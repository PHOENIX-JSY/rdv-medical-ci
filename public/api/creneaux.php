<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
$serviceId = (int)($_GET['service_id'] ?? 0);
$date = (string)($_GET['date'] ?? '');
$now = date('Y-m-d H:i');

// Créneaux libres = futurs et sans rendez-vous confirmé
$free = 'FROM creneaux c WHERE c.service_id = :sid AND c.debut > :now
         AND NOT EXISTS (SELECT 1 FROM rendez_vous r WHERE r.creneau_id = c.id AND r.statut = \'confirme\')';

$stmt = db()->prepare("SELECT DISTINCT substr(c.debut, 1, 10) AS jour $free ORDER BY jour");
$stmt->execute([':sid' => $serviceId, ':now' => $now]);
$out = ['dates' => $stmt->fetchAll(PDO::FETCH_COLUMN), 'creneaux' => []];

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $stmt = db()->prepare("SELECT c.id, c.debut $free AND substr(c.debut, 1, 10) = :d ORDER BY c.debut");
    $stmt->execute([':sid' => $serviceId, ':now' => $now, ':d' => $date]);
    $out['creneaux'] = $stmt->fetchAll();
}
json_out(200, $out);
