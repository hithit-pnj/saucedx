<?php
/**
 * Diagnostic TEMPORAIRE du formulaire de contact.
 *
 * À supprimer une fois l'envoi réparé : il n'a rien à faire sur un site en service.
 * Il ne révèle aucun mot de passe — tout au plus sa longueur, de quoi repérer un
 * champ resté vide ou une espace en trop.
 *
 * Usage : https://saucedexister.fr/api/diagnostic.php?jeton=LE-JETON
 */

declare(strict_types=1);

const JETON = 'd7f3a91c4e8b2065';

if (($_GET['jeton'] ?? '') !== JETON) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$cheminConfig = dirname(__DIR__, 2) . '/smtp.php';

$rapport = [
    'php'                => PHP_VERSION,
    'openssl'            => extension_loaded('openssl'),
    'dossier_du_script'  => __DIR__,
    'racine_publiee'     => dirname(__DIR__),
    'dossier_du_domaine' => dirname(__DIR__, 2),
    'config_attendue_a'  => $cheminConfig,
    'config_existe'      => file_exists($cheminConfig),
    'config_lisible'     => is_readable($cheminConfig),
];

// Ce que le gestionnaire de fichiers montre réellement à côté de public_html :
// de quoi repérer un smtp.php.txt, un fichier déposé un cran trop haut ou trop bas.
$voisins = @scandir(dirname(__DIR__, 2));
$rapport['contenu_du_dossier_du_domaine'] = is_array($voisins)
    ? array_values(array_diff($voisins, ['.', '..']))
    : 'illisible';

if ($rapport['config_lisible']) {
    $lu = require $cheminConfig;

    if (is_array($lu)) {
        $rapport['config_clefs']            = array_keys($lu);
        $rapport['hote']                    = $lu['hote'] ?? '(absent)';
        $rapport['port']                    = $lu['port'] ?? '(absent)';
        $rapport['utilisateur']             = $lu['utilisateur'] ?? '(absent)';
        $rapport['longueur_mot_de_passe']   = strlen((string) ($lu['mot_de_passe'] ?? ''));
    } else {
        $rapport['config_clefs'] = 'le fichier ne renvoie pas de tableau';
    }
}

$rapport['bibliotheque'] = [
    'Exception' => is_readable(__DIR__ . '/phpmailer/Exception.php'),
    'PHPMailer' => is_readable(__DIR__ . '/phpmailer/PHPMailer.php'),
    'SMTP'      => is_readable(__DIR__ . '/phpmailer/SMTP.php'),
];

// Essai d'envoi réel : c'est lui qui donne la raison exacte d'un refus.
if ($rapport['config_lisible'] && is_array($lu ?? null)) {
    require_once __DIR__ . '/phpmailer/Exception.php';
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';

    $courrier = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $port = (int) ($lu['port'] ?? 465);

        $courrier->isSMTP();
        $courrier->Host       = (string) ($lu['hote'] ?? '');
        $courrier->Port       = $port;
        $courrier->SMTPAuth   = true;
        $courrier->Username   = (string) ($lu['utilisateur'] ?? '');
        $courrier->Password   = (string) ($lu['mot_de_passe'] ?? '');
        $courrier->SMTPSecure = $port === 587
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $courrier->Timeout    = 15;
        $courrier->CharSet    = 'UTF-8';

        $courrier->setFrom((string) ($lu['utilisateur'] ?? ''), 'Diagnostic du site');
        $courrier->addAddress((string) ($lu['utilisateur'] ?? ''));
        $courrier->isHTML(false);
        $courrier->Subject = 'Diagnostic — envoi SMTP';
        $courrier->Body    = "Si ce message vous parvient, l'envoi authentifié fonctionne.";

        $rapport['envoi_reussi'] = $courrier->send();
        $rapport['erreur']       = $courrier->ErrorInfo;
    } catch (\Throwable $souci) {
        $rapport['envoi_reussi'] = false;
        $rapport['erreur']       = $souci->getMessage();
    }
}

echo json_encode($rapport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
