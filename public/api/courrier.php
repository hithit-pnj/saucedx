<?php
/**
 * L'envoi de courrier — La Sauce d'Exister
 *
 * Bibliothèque partagée par le formulaire de contact et la prise de rendez-vous.
 * Elle ne répond à aucune adresse : elle ne fait que définir des fonctions.
 *
 * L'envoi passe par SMTP authentifié dès qu'un fichier de configuration existe
 * hors de la racine web — voir smtp.exemple.php à la racine du projet. À défaut,
 * il retombe sur mail(), ce qui dépanne en local mais ne suffit pas en ligne : les
 * hébergeurs mutualisés acheminent mal le courrier non authentifié, et mail()
 * renvoie « vrai » même quand le message est jeté en silence.
 */

declare(strict_types=1);

/**
 * Réglages SMTP, lus dans un fichier situé **hors de la racine web**, un cran
 * au-dessus de public_html. Deux raisons à cet emplacement : le mot de passe de la
 * boîte n'a rien à faire dans le dépôt Git, et le déploiement ne peut ni l'écraser
 * ni le supprimer puisqu'il ne le connaît pas.
 *
 * Le fichier renvoie un tableau — voir smtp.exemple.php à la racine du projet.
 */
function reglagesSmtp(): ?array
{
    $chemin = dirname(__DIR__, 2) . '/smtp.php';
    if (!is_readable($chemin)) {
        return null;
    }

    $lu = require $chemin;

    return is_array($lu) && ($lu['mot_de_passe'] ?? '') !== '' ? $lu : null;
}

/** Empêche l'injection d'en-têtes via un champ contenant un retour à la ligne. */
function assainirEntete(string $valeur): string
{
    return trim(str_replace(["\r", "\n", "\0", '%0a', '%0d'], '', $valeur));
}

/**
 * Envoie un message.
 *
 * $envoi accepte : a, objet, corps, de, nom_de, repondre_a, nom_repondre_a,
 * et piece — un tableau nom / type / contenu pour joindre un fichier.
 *
 * La pièce jointe n'est possible que par SMTP : la construire à la main pour
 * mail() demanderait un message multipart, alors que ce chemin-là n'est qu'un
 * dépannage de développement.
 */
function envoyerCourrier(array $envoi): bool
{
    $smtp = reglagesSmtp();

    return $smtp !== null ? envoyerParSmtp($smtp, $envoi) : envoyerParMail($envoi);
}

/**
 * Envoi authentifié. Contrairement à mail(), le serveur de courrier signe le
 * message : il passe alors les contrôles SPF et DKIM, sans quoi il finit en
 * indésirable ou disparaît sans trace.
 */
function envoyerParSmtp(array $config, array $envoi): bool
{
    // Un require sur un fichier absent est une erreur fatale que try/catch ne
    // rattrape pas : l'appelant répondrait alors par une page blanche. Mieux vaut
    // vérifier d'abord et échouer proprement.
    foreach (['Exception', 'PHPMailer', 'SMTP'] as $classe) {
        $fichier = __DIR__ . '/phpmailer/' . $classe . '.php';
        if (!is_readable($fichier)) {
            error_log('Courrier — bibliothèque d’envoi introuvable : ' . $fichier);

            return false;
        }
        require_once $fichier;
    }

    try {
        $courrier = new \PHPMailer\PHPMailer\PHPMailer(true);

        $port = (int) ($config['port'] ?? 465);

        $courrier->isSMTP();
        $courrier->Host       = (string) $config['hote'];
        $courrier->Port       = $port;
        $courrier->SMTPAuth   = true;
        $courrier->Username   = (string) $config['utilisateur'];
        $courrier->Password   = (string) $config['mot_de_passe'];
        $courrier->SMTPSecure = $port === 587
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $courrier->Timeout    = 15;
        $courrier->CharSet    = 'UTF-8';

        $courrier->setFrom($envoi['de'], $envoi['nom_de'] ?? '');
        $courrier->addAddress($envoi['a']);

        if (!empty($envoi['repondre_a'])) {
            $courrier->addReplyTo($envoi['repondre_a'], $envoi['nom_repondre_a'] ?? '');
        }

        if (!empty($envoi['piece'])) {
            $courrier->addStringAttachment(
                $envoi['piece']['contenu'],
                $envoi['piece']['nom'],
                \PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64,
                $envoi['piece']['type'],
            );
        }

        // Sujet et corps en clair : PHPMailer se charge lui-même de l'encodage.
        $courrier->isHTML(false);
        $courrier->Subject = $envoi['objet'];
        $courrier->Body    = $envoi['corps'];

        return $courrier->send();
    } catch (\Throwable $souci) {
        // Visible dans le journal d'erreurs du hPanel. Le mot de passe n'y figure pas.
        error_log('Courrier — envoi SMTP impossible : ' . $souci->getMessage());

        return false;
    }
}

/** Dépannage sans SMTP configuré. Ne joint aucun fichier. */
function envoyerParMail(array $envoi): bool
{
    $entetes = [
        'From: ' . sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($envoi['nom_de'] ?? ''), $envoi['de']),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];

    if (!empty($envoi['repondre_a'])) {
        $entetes[] = 'Reply-To: ' . sprintf(
            '=?UTF-8?B?%s?= <%s>',
            base64_encode($envoi['nom_repondre_a'] ?? ''),
            $envoi['repondre_a'],
        );
    }

    return @mail(
        $envoi['a'],
        '=?UTF-8?B?' . base64_encode($envoi['objet']) . '?=',
        $envoi['corps'],
        implode("\r\n", $entetes),
        '-f' . $envoi['de'],
    );
}
