<?php
/**
 * L'agenda — La Sauce d'Exister
 *
 * Bibliothèque partagée par creneaux.php et rendez-vous.php. Elle ne répond à
 * aucune adresse : elle ne fait que définir des fonctions.
 *
 * Deux responsabilités.
 *
 * 1. Découper les habitudes d'Alice en créneaux. Elle décrit « mardi de 14 h à
 *    16 h » dans le CMS ; la mise en ligne dépose ces règles dans
 *    /rendez-vous/regles.json, et le découpage se fait ici, à la demande. Ce
 *    calcul ne peut pas se faire dans le navigateur : lui seul connaît l'heure
 *    qu'il est réellement à Paris et les créneaux déjà pris.
 *
 * 2. Ranger les demandes. Un fichier par créneau, hors de la racine web, nommé
 *    d'après le moment demandé. Ce nom n'est pas un détail : c'est lui qui rend
 *    la réservation atomique. Deux visiteurs qui cliquent sur le même créneau à
 *    la même seconde ne peuvent pas créer deux fois le même fichier, donc le
 *    second est refusé par le système de fichiers lui-même, sans verrou à tenir.
 */

declare(strict_types=1);

const AGENDA_FUSEAU = 'Europe/Paris';

/** Jours conservés dans les archives avant effacement — engagement RGPD. */
const AGENDA_RETENTION = 90;

const AGENDA_JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

const AGENDA_MOIS = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

// ── Les règles ──────────────────────────────────────────────────────────────

/**
 * Les disponibilités déclarées par Alice, déposées à la racine du site par la
 * mise en ligne. Renvoie null si le fichier manque ou si la prise de rendez-vous
 * est coupée : dans les deux cas, il n'y a simplement aucun créneau.
 */
function agendaReglages(): ?array
{
    $chemin = dirname(__DIR__) . '/rendez-vous/regles.json';
    if (!is_readable($chemin)) {
        return null;
    }

    $lu = json_decode((string) file_get_contents($chemin), true);
    if (!is_array($lu) || ($lu['actif'] ?? false) !== true) {
        return null;
    }

    return [
        'duree'        => max(5, (int) ($lu['duree'] ?? 15)),
        'battement'    => max(0, (int) ($lu['battement'] ?? 0)),
        'delaiMinimum' => max(0, (int) ($lu['delaiMinimum'] ?? 24)),
        'horizon'      => min(120, max(1, (int) ($lu['horizon'] ?? 21))),
        'expiration'   => max(1, (int) ($lu['expiration'] ?? 72)),
        'plages'       => is_array($lu['plages'] ?? null) ? $lu['plages'] : [],
        'fermetures'   => is_array($lu['fermetures'] ?? null) ? $lu['fermetures'] : [],
    ];
}

// ── Le découpage en créneaux ────────────────────────────────────────────────

function agendaCle(DateTimeImmutable $moment): string
{
    return $moment->format('Y-m-d-Hi');
}

/** Relit une clé de créneau. Renvoie null si elle est mal formée ou inventée. */
function agendaMoment(string $cle): ?DateTimeImmutable
{
    if (preg_match('/^\d{4}-\d{2}-\d{2}-\d{4}$/', $cle) !== 1) {
        return null;
    }

    // Le point d'exclamation remet à zéro tout ce que la clé ne dit pas — les
    // secondes — au lieu d'y verser l'heure qu'il est.
    $moment = DateTimeImmutable::createFromFormat(
        '!Y-m-d-Hi',
        $cle,
        new DateTimeZone(AGENDA_FUSEAU),
    );

    // createFromFormat accepte « 2026-02-31 » et le reporte au 3 mars : comparer
    // la clé rendue à celle reçue écarte ces dates qui n'existent pas.
    return $moment !== false && agendaCle($moment) === $cle ? $moment : null;
}

function agendaHeure(DateTimeImmutable $moment): string
{
    // Espaces insécables : « 14 h 00 » ne doit jamais se couper en fin de ligne.
    return $moment->format('H') . "\u{00A0}h\u{00A0}" . $moment->format('i');
}

function agendaJour(DateTimeImmutable $moment): string
{
    $jour   = AGENDA_JOURS[(int) $moment->format('N') - 1];
    $mois   = AGENDA_MOIS[(int) $moment->format('n') - 1];
    $numero = (int) $moment->format('j');

    // Le premier du mois est un ordinal, les autres non : « 1er mars », « 2 mars ».
    $libelle = $jour . ' ' . ($numero === 1 ? '1er' : (string) $numero) . ' ' . $mois;

    // L'année n'est utile qu'au passage de décembre à janvier.
    $ici = new DateTimeImmutable('now', new DateTimeZone(AGENDA_FUSEAU));

    return $moment->format('Y') === $ici->format('Y') ? $libelle : $libelle . ' ' . $moment->format('Y');
}

function agendaLibelle(DateTimeImmutable $moment): string
{
    return agendaJour($moment) . ' à ' . agendaHeure($moment);
}

function agendaFerme(DateTimeImmutable $jour, array $fermetures): bool
{
    $date = $jour->format('Y-m-d');

    foreach ($fermetures as $fermeture) {
        $du = (string) ($fermeture['du'] ?? '');
        $au = (string) ($fermeture['au'] ?? $du);
        if ($du !== '' && $date >= $du && $date <= max($du, $au)) {
            return true;
        }
    }

    return false;
}

/**
 * Les créneaux encore libres, groupés par jour.
 *
 * Trois filtres se superposent : le délai de prévenance, les fermetures, et les
 * créneaux déjà demandés par quelqu'un d'autre.
 */
function agendaCreneaux(array $reglages): array
{
    $fuseau     = new DateTimeZone(AGENDA_FUSEAU);
    $maintenant = new DateTimeImmutable('now', $fuseau);
    $plancher   = $maintenant->modify('+' . $reglages['delaiMinimum'] . ' hours');
    $occupes    = agendaOccupes();
    $pas        = $reglages['duree'] + $reglages['battement'];

    $jours = [];

    for ($rang = 0; $rang <= $reglages['horizon']; $rang++) {
        $jour = $maintenant->setTime(0, 0)->modify('+' . $rang . ' days');

        if (agendaFerme($jour, $reglages['fermetures'])) {
            continue;
        }

        $nomDuJour = AGENDA_JOURS[(int) $jour->format('N') - 1];
        $creneaux  = [];

        foreach ($reglages['plages'] as $plage) {
            if (($plage['jour'] ?? '') !== $nomDuJour) {
                continue;
            }
            if (preg_match('/^\d{2}:\d{2}$/', (string) ($plage['debut'] ?? '')) !== 1) {
                continue;
            }
            if (preg_match('/^\d{2}:\d{2}$/', (string) ($plage['fin'] ?? '')) !== 1) {
                continue;
            }

            [$heureDebut, $minuteDebut] = array_map('intval', explode(':', $plage['debut']));
            [$heureFin, $minuteFin]     = array_map('intval', explode(':', $plage['fin']));

            $debut = $jour->setTime($heureDebut, $minuteDebut);
            $fin   = $jour->setTime($heureFin, $minuteFin);

            for (
                $moment = $debut;
                $moment->modify('+' . $reglages['duree'] . ' minutes') <= $fin;
                $moment = $moment->modify('+' . $pas . ' minutes')
            ) {
                if ($moment < $plancher) {
                    continue;
                }

                $cle = agendaCle($moment);
                if (isset($occupes[$cle])) {
                    continue;
                }

                $creneaux[$cle] = agendaHeure($moment);
            }
        }

        if ($creneaux === []) {
            continue;
        }

        ksort($creneaux);

        $jours[] = [
            'date'     => $jour->format('Y-m-d'),
            'libelle'  => agendaJour($jour),
            'creneaux' => array_map(
                static fn (string $cle, string $heure): array => ['cle' => $cle, 'heure' => $heure],
                array_keys($creneaux),
                $creneaux,
            ),
        ];
    }

    return $jours;
}

/** Ce créneau existe-t-il vraiment dans les règles, et est-il encore à prendre ? */
function agendaCreneauLibre(array $reglages, string $cle): bool
{
    foreach (agendaCreneaux($reglages) as $jour) {
        foreach ($jour['creneaux'] as $creneau) {
            if ($creneau['cle'] === $cle) {
                return true;
            }
        }
    }

    return false;
}

// ── Le rangement des demandes ───────────────────────────────────────────────

/**
 * Le dossier des demandes, créé au premier besoin.
 *
 * Il se place un cran au-dessus de public_html, à côté de smtp.php : les noms,
 * numéros et adresses qu'il contient ne doivent être lisibles par personne
 * depuis le web. Si l'hébergement refuse d'y écrire, on se rabat sur un dossier
 * caché dans la racine publiée — le .htaccess interdit déjà tout ce qui commence
 * par un point, et on y ajoute par sécurité une interdiction propre.
 */
function agendaDossier(): ?string
{
    static $trouve = false;
    static $dossier = null;

    if ($trouve) {
        return $dossier;
    }
    $trouve = true;

    $candidats = [
        dirname(__DIR__, 2) . '/rendez-vous',
        dirname(__DIR__) . '/.rendez-vous',
    ];

    foreach ($candidats as $base) {
        if (!is_dir($base) && !@mkdir($base, 0700, true)) {
            continue;
        }

        $complet = true;
        foreach (['demandes', 'archives'] as $sous) {
            if (!is_dir($base . '/' . $sous) && !@mkdir($base . '/' . $sous, 0700, true)) {
                $complet = false;
                break;
            }
        }

        if (!$complet || !is_writable($base . '/demandes')) {
            continue;
        }

        if (!is_file($base . '/.htaccess')) {
            @file_put_contents($base . '/.htaccess', "Require all denied\nDeny from all\n");
        }

        $dossier = $base;

        return $dossier;
    }

    error_log('Rendez-vous — aucun dossier de stockage accessible en écriture.');

    return null;
}

function agendaLire(string $cle): ?array
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return null;
    }

    $chemin = $dossier . '/demandes/' . $cle . '.json';
    if (!is_readable($chemin)) {
        return null;
    }

    $lu = json_decode((string) file_get_contents($chemin), true);

    return is_array($lu) ? $lu : null;
}

/**
 * Dépose une demande, à condition que le créneau soit encore vierge.
 *
 * Le mode « x » est le cœur de l'affaire : il crée le fichier ou échoue si
 * quelqu'un l'a créé entre-temps. C'est le système de fichiers qui arbitre, donc
 * l'arbitrage est indivisible.
 */
function agendaDeposer(string $cle, array $demande): bool
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return false;
    }

    $poignee = @fopen($dossier . '/demandes/' . $cle . '.json', 'xb');
    if ($poignee === false) {
        return false;
    }

    $ecrit = fwrite($poignee, json_encode($demande, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fclose($poignee);

    return $ecrit !== false;
}

function agendaEnregistrer(string $cle, array $demande): bool
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return false;
    }

    return @file_put_contents(
        $dossier . '/demandes/' . $cle . '.json',
        json_encode($demande, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        LOCK_EX,
    ) !== false;
}

/** Sort la demande du dossier actif : le créneau redevient disponible. */
function agendaArchiver(string $cle, array $demande, string $statut): bool
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return false;
    }

    $demande['statut']   = $statut;
    $demande['decide_a'] = time();

    $vers = sprintf('%s/archives/%s-%s.json', $dossier, $cle, $demande['id'] ?? 'sans-id');

    @file_put_contents($vers, json_encode($demande, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

    return @unlink($dossier . '/demandes/' . $cle . '.json');
}

/** Les créneaux qu'une demande en attente ou confirmée occupe. */
function agendaOccupes(): array
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return [];
    }

    $occupes = [];
    foreach (glob($dossier . '/demandes/*.json') ?: [] as $fichier) {
        $occupes[basename($fichier, '.json')] = true;
    }

    return $occupes;
}

/**
 * Le ménage, passé à chaque consultation de l'agenda.
 *
 * Une demande restée sans réponse finit par libérer son créneau : sans cela, un
 * oubli d'Alice condamnerait ce moment pour toujours. Et rien ne traîne au-delà
 * de la durée de conservation annoncée dans la politique de confidentialité.
 */
function agendaMenage(array $reglages): void
{
    $dossier = agendaDossier();
    if ($dossier === null) {
        return;
    }

    $maintenant = time();
    $peremption = $reglages['expiration'] * 3600;

    foreach (glob($dossier . '/demandes/*.json') ?: [] as $fichier) {
        $cle     = basename($fichier, '.json');
        $demande = agendaLire($cle);

        // Un fichier illisible n'apprend plus rien à personne, mais son seul nom
        // suffit à retenir le créneau : le laisser en place le condamnerait pour
        // toujours. On le met de côté et le moment redevient libre.
        if ($demande === null) {
            @rename($fichier, sprintf('%s/archives/%s-illisible-%d.json', $dossier, $cle, $maintenant));
            continue;
        }

        $moment = agendaMoment($cle);

        if ($moment !== null && $moment->getTimestamp() < $maintenant - 7 * 86400) {
            agendaArchiver($cle, $demande, $demande['statut'] ?? 'passe');
            continue;
        }

        $enAttente = ($demande['statut'] ?? '') === 'attente';
        $depose    = (int) ($demande['depose_a'] ?? 0);

        if ($enAttente && $depose > 0 && $depose + $peremption < $maintenant) {
            agendaArchiver($cle, $demande, 'expire');
        }
    }

    foreach (glob($dossier . '/archives/*.json') ?: [] as $fichier) {
        if (filemtime($fichier) < $maintenant - AGENDA_RETENTION * 86400) {
            @unlink($fichier);
        }
    }
}

// ── Le fichier d'agenda joint aux confirmations ─────────────────────────────

function agendaEchapper(string $texte): string
{
    return str_replace(
        ["\\", "\n", ',', ';'],
        ['\\\\', '\\n', '\\,', '\\;'],
        $texte,
    );
}

/** Replie les lignes à 75 octets, comme l'exige le format iCalendar. */
function agendaReplier(string $ligne): string
{
    $morceaux = [];
    $reste    = $ligne;

    while (strlen($reste) > 75) {
        // Ne jamais couper au milieu d'un caractère accentué : on recule jusqu'au
        // début d'une séquence UTF-8.
        $coupe = 75;
        while ($coupe > 1 && (ord($reste[$coupe]) & 0xC0) === 0x80) {
            $coupe--;
        }
        $morceaux[] = substr($reste, 0, $coupe);
        $reste      = ' ' . substr($reste, $coupe);
    }
    $morceaux[] = $reste;

    return implode("\r\n", $morceaux);
}

function agendaFichier(DateTimeImmutable $moment, int $duree, string $titre, string $description, string $organisateur): string
{
    $utc = new DateTimeZone('UTC');
    $fin = $moment->modify('+' . $duree . ' minutes');

    $lignes = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//La Sauce d Exister//Rendez-vous//FR',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . agendaCle($moment) . '@' . substr(strrchr($organisateur, '@') ?: '@local', 1),
        'DTSTAMP:' . (new DateTimeImmutable('now', $utc))->format('Ymd\THis\Z'),
        'DTSTART:' . $moment->setTimezone($utc)->format('Ymd\THis\Z'),
        'DTEND:' . $fin->setTimezone($utc)->format('Ymd\THis\Z'),
        'SUMMARY:' . agendaEchapper($titre),
        'DESCRIPTION:' . agendaEchapper($description),
        'ORGANIZER:mailto:' . $organisateur,
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    return implode("\r\n", array_map('agendaReplier', $lignes)) . "\r\n";
}
