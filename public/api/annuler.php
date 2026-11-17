<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('POST');
$patient = require_auth();
$id = (int)(input()['rdv_id'] ?? 0);

$stmt = db()->prepare(
    "SELECT r.id, r.statut, c.debut FROM rendez_vous r JOIN creneaux c ON c.id = r.creneau_id
     WHERE r.id = ? AND r.patient_id = ?"
);
$stmt->execute([$id, $patient['id']]);
$rdv = $stmt->fetch();
if (!$rdv) {
    fail(404, 'Rendez-vous introuvable.');
}
if ($rdv['statut'] !== 'confirme' || $rdv['debut'] <= date('Y-m-d H:i')) {
    fail(409, 'Ce rendez-vous ne peut plus être annulé.');
}
db()->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?")->execute([$id]);
json_out(200, ['ok' => true]);
