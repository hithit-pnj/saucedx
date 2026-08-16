<?php
/**
 * Formulaire de contact — La Sauce d'Exister
 *
 * Reçoit le formulaire de /contact/ et transmet le message par mail.
 * Aucune donnée n'est enregistrée sur le serveur.
 *
 * Répond en JSON aux requêtes fetch, et par une redirection sinon
 * (le formulaire fonctionne donc sans JavaScript).
 *
 * L'acheminement lui-même — SMTP authentifié, ou mail() en dépannage — est dans
 * courrier.php, partagé avec la prise de rendez-vous.
 */

declare(strict_types=1);

require_once __DIR__ . '/courrier.php';

// ── Réglages — LES DEUX PREMIÈRES LIGNES SONT À ADAPTER ─────────────────────
$DESTINATAIRE   = 'contact@saucedexister.fr';
// L'expéditeur doit être une adresse réelle du domaine : SPF et DKIM ne signent
// que celles-là. Une seule boîte suffit — le Reply-To pointe vers le visiteur,
// donc répondre au mail répond bien à la personne, pas à soi-même.
$EXPEDITEUR     = 'contact@saucedexister.fr';
$NOM_EXPEDITEUR = "Formulaire du site";
$PAGE_MERCI     = '/contact/merci/';
$PAGE_ERREUR    = '/contact/erreur/';
$DELAI_MINIMUM  = 3;    // secondes entre l'ouverture du formulaire et l'envoi
$ENVOIS_MAX     = 8;    // par adresse IP
$FENETRE        = 3600; // secondes
// ────────────────────────────────────────────────────────────────────────────

// Les hébergeurs mutualisés tournent en UTC : sans cette ligne, l'heure de
// réception écrite dans le mail aurait une ou deux heures de retard sur la vraie.
date_default_timezone_set('Europe/Paris');

$veutDuJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function repondre(bool $ok, int $code, string $message, string $redirection): never
{
    global $veutDuJson;
    http_response_code($code);

    // Le .htaccess rend tout le site cacheable une heure. Appliqué à cette réponse,
    // un intermédiaire pourrait resservir un « message envoyé » à quelqu'un dont le
    // message n'est jamais parti.
    header('Cache-Control: no-store');

    if ($veutDuJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . $redirection, true, 303);
    }
    exit;
}

function echouer(string $message, int $code = 400): never
{
    global $PAGE_ERREUR;
    repondre(false, $code, $message, $PAGE_ERREUR);
}

function champ(string $nom, int $longueurMax): string
{
    $valeur = $_POST[$nom] ?? '';
    if (!is_string($valeur)) {
        return '';
    }
    return mb_substr(trim($valeur), 0, $longueurMax);
}

// ── 1. Méthode ──────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echouer('Méthode non autorisée.', 405);
}

// ── 2. Anti-spam : le pot de miel doit rester vide ──────────────────────────
if (champ('site', 200) !== '') {
    // On répond « ok » au robot pour ne rien lui apprendre.
    repondre(true, 200, 'Message envoyé.', $PAGE_MERCI);
}

// ── 3. Anti-spam : un humain met plus de trois secondes ─────────────────────
$ouvertA = (int) ($_POST['ouvert_a'] ?? 0);
if ($ouvertA > 0 && (time() - $ouvertA) < $DELAI_MINIMUM) {
    repondre(true, 200, 'Message envoyé.', $PAGE_MERCI);
}

// ── 4. Validation ───────────────────────────────────────────────────────────
$nom     = champ('nom', 120);
$email   = champ('email', 200);
$message = champ('message', 6000);
$sujet   = champ('sujet', 120);
$consent = ($_POST['consentement'] ?? '') === 'oui';

if ($nom === '' || mb_strlen($nom) < 2) {
    echouer('Merci d’indiquer votre nom.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echouer('Cette adresse mail ne semble pas valide.');
}
if (mb_strlen($message) < 10) {
    echouer('Merci d’écrire quelques mots de plus.');
}
if (!$consent) {
    echouer('Le consentement est nécessaire pour vous répondre.');
}

// ── 5. Limitation par adresse IP ────────────────────────────────────────────
/**
 * L'hébergeur place un CDN devant le serveur : REMOTE_ADDR est alors l'adresse de
 * ce CDN, la même pour tout le monde. S'en contenter plafonnerait le formulaire à
 * quelques messages par heure pour l'ensemble des visiteurs, et non par personne —
 * autrement dit, un envoi légitime pourrait être refusé à cause d'un autre.
 *
 * Ces en-têtes sont falsifiables par qui joint le serveur sans passer par le CDN.
 * Le seul gain pour un attaquant serait de contourner sa propre limite, ce qui est
 * bien moins grave que de bloquer les visiteurs de bonne foi.
 */
function adresseClient(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'] as $entete) {
        $brut = $_SERVER[$entete] ?? '';
        if (!is_string($brut) || $brut === '') {
            continue;
        }
        $premiere = trim(explode(',', $brut)[0]);
        if (filter_var($premiere, FILTER_VALIDATE_IP)) {
            return $premiere;
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
}

$empreinte = hash('sha256', adresseClient() . date('YmdH'));
$journal   = sys_get_temp_dir() . '/lsde-' . $empreinte . '.txt';
$envois    = is_readable($journal) ? (int) file_get_contents($journal) : 0;

if ($envois >= $ENVOIS_MAX) {
    echouer('Trop de messages envoyés depuis cette connexion. Réessayez plus tard.', 429);
}
@file_put_contents($journal, (string) ($envois + 1), LOCK_EX);
@touch($journal, time(), time() + $FENETRE);

// ── 6. Composition du mail ──────────────────────────────────────────────────
$nomSur   = assainirEntete($nom);
$emailSur = assainirEntete($email);
$sujetSur = assainirEntete($sujet);

$objet = $sujetSur !== ''
    ? sprintf('[Site] %s — %s', $sujetSur, $nomSur)
    : sprintf('[Site] Message de %s', $nomSur);

// Déduit de l'adresse de destination, et non de HTTP_HOST : cet en-tête est
// fourni par le client, donc inutilisable dans un en-tête de mail. Rien à
// modifier ici quand le domaine change.
$DOMAINE = substr(strrchr($DESTINATAIRE, '@') ?: '@', 1);

$corps = implode("\r\n", [
    'Message envoyé depuis ' . $DOMAINE,
    '',
    'Nom      : ' . $nomSur,
    'Mail     : ' . $emailSur,
    'Sujet    : ' . ($sujetSur !== '' ? $sujetSur : '—'),
    'Reçu le  : ' . date('d/m/Y à H:i'),
    '',
    str_repeat('—', 40),
    '',
    $message,
    '',
    str_repeat('—', 40),
    'Consentement RGPD donné à l’envoi. Répondre à ce mail écrit directement à ' . $emailSur . '.',
]);

$envoye = envoyerCourrier([
    'a'              => $DESTINATAIRE,
    'de'             => $EXPEDITEUR,
    'nom_de'         => $NOM_EXPEDITEUR,
    'repondre_a'     => $emailSur,
    'nom_repondre_a' => $nomSur,
    'objet'          => $objet,
    'corps'          => $corps,
]);

if (!$envoye) {
    echouer('L’envoi a échoué côté serveur.', 502);
}

repondre(true, 200, 'Message envoyé.', $PAGE_MERCI);
