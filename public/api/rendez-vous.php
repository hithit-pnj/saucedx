<?php
/**
 * Prise de rendez-vous téléphonique — La Sauce d'Exister
 *
 * Trois gestes passent par ici.
 *
 *   POST sans geste           un visiteur demande un créneau
 *   GET  ?geste=voir          Alice ouvre le lien reçu par mail et lit la demande
 *   POST geste=accepter|refuser  Alice tranche
 *
 * Pourquoi la décision d'Alice n'est pas un simple lien cliquable : certains
 * services de messagerie ouvrent les liens d'un message pour les analyser. Un
 * lien « accepter » se déclencherait alors tout seul, avant même qu'Alice ait vu
 * la demande. Le mail conduit donc à une page qui lit la demande — geste sans
 * conséquence — et n'agit que sur un bouton, c'est-à-dire par POST.
 *
 * Toutes les réponses sont en JSON : la page /rendez-vous/decider/ et le
 * sélecteur de créneaux s'en chargent à l'écran.
 */

declare(strict_types=1);

require_once __DIR__ . '/agenda.php';
require_once __DIR__ . '/courrier.php';

// ── Réglages — LA PREMIÈRE LIGNE EST À ADAPTER ──────────────────────────────
$DESTINATAIRE   = 'contact@saucedexister.fr';
$EXPEDITEUR     = 'contact@saucedexister.fr';
$NOM_EXPEDITEUR = "La Sauce d'Exister";
$PERSONNE       = 'Alice Berthoz';
$DELAI_MINIMUM  = 3;    // secondes entre l'ouverture du formulaire et l'envoi
$DEMANDES_MAX   = 4;    // par adresse IP
$FENETRE        = 3600; // secondes
// ────────────────────────────────────────────────────────────────────────────

// Déduit de l'adresse de destination, et non de HTTP_HOST : cet en-tête est
// fourni par le client, et un lien de décision falsifié conduirait Alice ailleurs.
$DOMAINE = substr(strrchr($DESTINATAIRE, '@') ?: '@', 1);
$SITE    = 'https://' . $DOMAINE;

// Les hébergeurs mutualisés tournent en UTC : sans cette ligne, l'heure écrite
// dans les mails aurait une ou deux heures de retard sur la vraie.
date_default_timezone_set('Europe/Paris');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function repondre(bool $ok, int $code, string $message, array $extra = []): never
{
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

function echouer(string $message, int $code = 400): never
{
    repondre(false, $code, $message);
}

function champ(string $nom, int $longueurMax): string
{
    $valeur = $_POST[$nom] ?? $_GET[$nom] ?? '';
    if (!is_string($valeur)) {
        return '';
    }

    return mb_substr(trim($valeur), 0, $longueurMax);
}

$reglages = agendaReglages();
if ($reglages === null) {
    echouer('La prise de rendez-vous est fermée pour le moment.', 503);
}

$methode = $_SERVER['REQUEST_METHOD'] ?? '';
$geste   = champ('geste', 20);

// ════════════════════════════════════════════════════════════════════════════
// A. Alice lit une demande
// ════════════════════════════════════════════════════════════════════════════

if ($methode === 'GET') {
    if ($geste !== 'voir') {
        echouer('Requête inconnue.', 405);
    }

    [$cle, $demande] = demandeAuthentifiee();
    $moment          = agendaMoment($cle);

    repondre(true, 200, 'Demande trouvée.', [
        'demande' => [
            'statut'    => $demande['statut'] ?? 'attente',
            'nom'       => $demande['nom'] ?? '',
            'telephone' => $demande['telephone'] ?? '',
            'email'     => $demande['email'] ?? '',
            'motif'     => $demande['motif'] ?? '',
            'moment'    => $moment !== null ? agendaLibelle($moment) : $cle,
            'duree'     => $reglages['duree'],
        ],
    ]);
}

if ($methode !== 'POST') {
    echouer('Méthode non autorisée.', 405);
}

// ════════════════════════════════════════════════════════════════════════════
// B. Alice tranche
// ════════════════════════════════════════════════════════════════════════════

if ($geste === 'accepter' || $geste === 'refuser') {
    [$cle, $demande] = demandeAuthentifiee();

    $moment  = agendaMoment($cle);
    $libelle = $moment !== null ? agendaLibelle($moment) : $cle;
    $statut  = $demande['statut'] ?? 'attente';

    // Rien à refaire si le geste a déjà été posé : Alice a pu recharger la page.
    if ($geste === 'accepter' && $statut === 'confirme') {
        repondre(true, 200, 'Ce rendez-vous était déjà confirmé.', ['statut' => 'confirme']);
    }

    if ($geste === 'accepter') {
        $demande['statut']   = 'confirme';
        $demande['decide_a'] = time();

        if (!agendaEnregistrer($cle, $demande)) {
            echouer('La confirmation n’a pas pu être enregistrée.', 500);
        }

        $invitation = $moment === null ? null : [
            'nom'     => 'rendez-vous.ics',
            'type'    => 'text/calendar; charset=utf-8',
            'contenu' => agendaFichier(
                $moment,
                $reglages['duree'],
                'Appel — ' . $demande['nom'] . ' / ' . $NOM_EXPEDITEUR,
                sprintf(
                    "Appel téléphonique de %d minutes.\nTéléphone : %s\nMail : %s",
                    $reglages['duree'],
                    $demande['telephone'],
                    $demande['email'],
                ),
                $DESTINATAIRE,
            ),
        ];

        $prevenu = envoyerCourrier([
            'a'              => $demande['email'],
            'de'             => $EXPEDITEUR,
            'nom_de'         => $NOM_EXPEDITEUR,
            'repondre_a'     => $DESTINATAIRE,
            'nom_repondre_a' => $PERSONNE,
            'objet'          => 'C’est confirmé — ' . $libelle,
            'corps'          => implode("\r\n", [
                'Bonjour ' . $demande['nom'] . ',',
                '',
                'Nous nous parlons ' . $libelle . ' (heure française).',
                'Je vous appelle au ' . $demande['telephone'] . '. Comptez ' . $reglages['duree'] . ' minutes.',
                '',
                'Si un imprévu survient d’ici là, répondez simplement à ce message.',
                '',
                'À très bientôt,',
                $PERSONNE . ' — ' . $NOM_EXPEDITEUR,
                $SITE,
            ]),
            'piece'          => $invitation,
        ]);

        envoyerCourrier([
            'a'      => $DESTINATAIRE,
            'de'     => $EXPEDITEUR,
            'nom_de' => $NOM_EXPEDITEUR,
            'objet'  => '[RDV confirmé] ' . $demande['nom'] . ' — ' . $libelle,
            'corps'  => implode("\r\n", [
                'Rendez-vous confirmé. ' . $demande['nom'] . ' vient d’en être prévenu.',
                '',
                'Quand      : ' . $libelle,
                'Téléphone  : ' . $demande['telephone'],
                'Mail       : ' . $demande['email'],
                '',
                ($demande['motif'] ?? '') !== '' ? "Ce qui l’amène :\r\n" . $demande['motif'] : '',
            ]),
            'piece'  => $invitation,
        ]);

        // Le rendez-vous est bel et bien pris ; c'est la personne qui l'ignore.
        // Le taire laisserait Alice croire qu'elle est attendue.
        repondre(true, 200, $prevenu
            ? 'C’est noté : le rendez-vous est confirmé et la personne vient d’en être prévenue.'
            : 'Le rendez-vous est confirmé, mais le mail n’est pas parti : prévenez la personne '
                . 'vous-même à ' . $demande['email'] . '.', ['statut' => 'confirme']);
    }

    agendaArchiver($cle, $demande, 'refuse');

    $prevenu = envoyerCourrier([
        'a'              => $demande['email'],
        'de'             => $EXPEDITEUR,
        'nom_de'         => $NOM_EXPEDITEUR,
        'repondre_a'     => $DESTINATAIRE,
        'nom_repondre_a' => $PERSONNE,
        'objet'          => 'À propos de votre demande de rendez-vous',
        'corps'          => implode("\r\n", [
            'Bonjour ' . $demande['nom'] . ',',
            '',
            'Le moment que vous aviez choisi — ' . $libelle . ' — ne m’est finalement',
            'pas possible, et j’en suis désolée.',
            '',
            'D’autres créneaux sont ouverts ici :',
            $SITE . '/contact/',
            '',
            'Vous pouvez aussi répondre à ce message : nous trouverons autrement.',
            '',
            $PERSONNE . ' — ' . $NOM_EXPEDITEUR,
        ]),
    ]);

    repondre(true, 200, $prevenu
        ? 'C’est noté : la personne est prévenue et le créneau redevient libre.'
        : 'Le créneau redevient libre, mais le mail n’est pas parti : prévenez la personne '
            . 'vous-même à ' . $demande['email'] . '.', ['statut' => 'refuse']);
}

// ════════════════════════════════════════════════════════════════════════════
// C. Un visiteur demande un créneau
// ════════════════════════════════════════════════════════════════════════════

// Anti-spam : le pot de miel doit rester vide, et un humain met plus de trois
// secondes. Dans les deux cas on répond « ok » au robot pour ne rien lui apprendre.
if (champ('site', 200) !== '') {
    repondre(true, 200, 'Demande envoyée.');
}

$ouvertA = (int) ($_POST['ouvert_a'] ?? 0);
if ($ouvertA > 0 && (time() - $ouvertA) < $DELAI_MINIMUM) {
    repondre(true, 200, 'Demande envoyée.');
}

$nom       = assainirEntete(champ('nom', 120));
$email     = assainirEntete(champ('email', 200));
$telephone = assainirEntete(champ('telephone', 40));
$motif     = champ('motif', 1000);
$creneau   = champ('creneau', 20);
$consent   = ($_POST['consentement'] ?? '') === 'oui';

if (mb_strlen($nom) < 2) {
    echouer('Merci d’indiquer votre nom.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echouer('Cette adresse mail ne semble pas valide.');
}
if (strlen(preg_replace('/\D/', '', $telephone) ?? '') < 8) {
    echouer('Ce numéro ne semble pas valide — c’est par là que passera l’appel.');
}
if (!$consent) {
    echouer('Le consentement est nécessaire pour organiser l’appel.');
}

$moment = agendaMoment($creneau);
if ($moment === null) {
    echouer('Ce moment n’existe pas.');
}

// ── Limitation par adresse IP ───────────────────────────────────────────────
/**
 * L'hébergeur place un CDN devant le serveur : REMOTE_ADDR est alors l'adresse de
 * ce CDN, la même pour tout le monde. S'en contenter plafonnerait la prise de
 * rendez-vous pour l'ensemble des visiteurs, et non par personne.
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

$journal  = sys_get_temp_dir() . '/lsde-rdv-' . hash('sha256', adresseClient() . date('YmdH')) . '.txt';
$demandes = is_readable($journal) ? (int) file_get_contents($journal) : 0;

if ($demandes >= $DEMANDES_MAX) {
    echouer('Trop de demandes depuis cette connexion. Réessayez plus tard.', 429);
}

// Le ménage d'abord : un créneau libéré par une demande périmée doit pouvoir
// être repris à l'instant même.
agendaMenage($reglages);

if (!agendaCreneauLibre($reglages, $creneau)) {
    // Deux raisons très différentes de refuser, et deux choses à dire : ou bien
    // quelqu'un a été plus rapide, ou bien ce moment n'a jamais été proposé —
    // page restée ouverte pendant qu'Alice changeait ses disponibilités.
    if (agendaLire($creneau) !== null) {
        echouer('Ce moment vient d’être pris. Choisissez-en un autre.', 409);
    }

    echouer('Ce moment n’est plus proposé. Voici l’agenda à jour.', 410);
}

$jeton   = bin2hex(random_bytes(16));
$demande = [
    'id'        => bin2hex(random_bytes(6)),
    'creneau'   => $creneau,
    'statut'    => 'attente',
    'nom'       => $nom,
    'telephone' => $telephone,
    'email'     => $email,
    'motif'     => $motif,
    // Seule l'empreinte est conservée : un accès en lecture au dossier ne
    // permettrait pas de fabriquer un lien de décision valable.
    'jeton'     => hash('sha256', $jeton),
    'depose_a'  => time(),
    'decide_a'  => null,
];

// Le dépôt échoue si quelqu'un a pris ce créneau entre la vérification et ici.
if (!agendaDeposer($creneau, $demande)) {
    echouer('Ce moment vient d’être pris. Choisissez-en un autre.', 409);
}

@file_put_contents($journal, (string) ($demandes + 1), LOCK_EX);
@touch($journal, time(), time() + $FENETRE);

$libelle = agendaLibelle($moment);
$lien    = sprintf('%s/rendez-vous/decider/?creneau=%s&jeton=%s', $SITE, rawurlencode($creneau), $jeton);

$prevenue = envoyerCourrier([
    'a'              => $DESTINATAIRE,
    'de'             => $EXPEDITEUR,
    'nom_de'         => $NOM_EXPEDITEUR,
    'repondre_a'     => $email,
    'nom_repondre_a' => $nom,
    'objet'          => sprintf('[RDV] %s — %s', $nom, $libelle),
    'corps'          => implode("\r\n", [
        'Quelqu’un demande ' . $reglages['duree'] . ' minutes au téléphone.',
        '',
        'Qui        : ' . $nom,
        'Téléphone  : ' . $telephone,
        'Mail       : ' . $email,
        'Moment     : ' . $libelle . ' (heure française)',
        'Demandé le : ' . date('d/m/Y à H:i'),
        '',
        str_repeat('—', 40),
        '',
        $motif !== '' ? $motif : '(rien de précisé)',
        '',
        str_repeat('—', 40),
        '',
        'Accepter ou proposer un autre moment :',
        $lien,
        '',
        'Ce lien ne vaut que pour cette demande. Sans réponse sous '
            . $reglages['expiration'] . ' heures, le créneau se libère tout seul.',
    ]),
]);

// Sans ce mail, la demande dort dans un dossier que personne ne regarde : mieux
// vaut rendre le créneau et le dire au visiteur que de le laisser attendre.
if (!$prevenue) {
    agendaArchiver($creneau, $demande, 'echec');
    echouer('La demande n’a pas pu être transmise. Écrivez-moi plutôt directement.', 502);
}

envoyerCourrier([
    'a'              => $email,
    'de'             => $EXPEDITEUR,
    'nom_de'         => $NOM_EXPEDITEUR,
    'repondre_a'     => $DESTINATAIRE,
    'nom_repondre_a' => $PERSONNE,
    'objet'          => 'Votre demande de rendez-vous — ' . $NOM_EXPEDITEUR,
    'corps'          => implode("\r\n", [
        'Bonjour ' . $nom . ',',
        '',
        'Votre demande d’appel est bien arrivée :',
        $libelle . ' (heure française), ' . $reglages['duree'] . ' minutes.',
        '',
        'Je vous confirme ce moment par mail, en général sous vingt-quatre heures.',
        'Tant que cette confirmation n’est pas là, rien n’est arrêté.',
        '',
        'À bientôt,',
        $PERSONNE . ' — ' . $NOM_EXPEDITEUR,
        $SITE,
    ]),
]);

repondre(true, 200, 'Demande envoyée.');

// ── Le lien reçu par Alice ──────────────────────────────────────────────────

/**
 * Retrouve la demande désignée par le lien, et vérifie le jeton.
 *
 * Le jeton n'est jamais stocké en clair : on compare son empreinte, et avec
 * hash_equals plutôt que == pour que le temps de réponse ne trahisse pas
 * combien de caractères sont justes.
 */
function demandeAuthentifiee(): array
{
    $cle   = champ('creneau', 20);
    $jeton = champ('jeton', 64);

    if (agendaMoment($cle) === null || preg_match('/^[a-f0-9]{32}$/', $jeton) !== 1) {
        echouer('Ce lien n’est plus valable.', 404);
    }

    $demande = agendaLire($cle);
    if ($demande === null || !is_string($demande['jeton'] ?? null)) {
        echouer('Ce lien n’est plus valable.', 404);
    }

    if (!hash_equals($demande['jeton'], hash('sha256', $jeton))) {
        echouer('Ce lien n’est plus valable.', 404);
    }

    return [$cle, $demande];
}
