<?php
/**
 * Les créneaux encore libres — La Sauce d'Exister
 *
 * Interrogé par la page contact au moment où quelqu'un la consulte. Ne renvoie
 * rien d'autre que des dates et des heures : aucune donnée personnelle ne sort
 * d'ici, même si un créneau est pris — il est alors simplement absent.
 */

declare(strict_types=1);

require_once __DIR__ . '/agenda.php';

header('Content-Type: application/json; charset=utf-8');
// Le .htaccess rend tout le site cacheable une heure. Appliqué ici, un
// intermédiaire proposerait des créneaux déjà pris.
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    echo json_encode(['ouvert' => false, 'jours' => []]);
    exit;
}

$reglages = agendaReglages();

if ($reglages === null) {
    echo json_encode(['ouvert' => false, 'jours' => []]);
    exit;
}

agendaMenage($reglages);

echo json_encode(
    [
        'ouvert' => true,
        'duree'  => $reglages['duree'],
        'jours'  => agendaCreneaux($reglages),
    ],
    JSON_UNESCAPED_UNICODE,
);
