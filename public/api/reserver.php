<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('POST');
$patient = require_auth();
$in = input();
$creneauId = (int)($in['creneau_id'] ?? 0);
$motif = trim((string)($in['motif'] ?? ''));

if ($motif === '' || mb_strlen($motif) > 200) {
    fail(422, 'Le motif de consultation est requis (200 caractères maximum).');
}
$stmt = db()->prepare('SELECT id, debut FROM creneaux WHERE id = ?');
$stmt->execute([$creneauId]);
$creneau = $stmt->fetch();
if (!$creneau || $creneau['debut'] <= date('Y-m-d H:i')) {
    fail(404, 'Créneau indisponible.');
}

for ($try = 0; $try < 5; $try++) {
    $reference = 'RDV-' . strtoupper(bin2hex(random_bytes(3)));
    try {
        $ins = db()->prepare(
            "INSERT INTO rendez_vous (reference, patient_id, creneau_id, motif, statut)
             VALUES (?, ?, ?, ?, 'confirme')"
        );
        $ins->execute([$reference, $patient['id'], $creneauId, $motif]);
        json_out(201, ['rendez_vous' => fetch_rdv((int)$patient['id'], $reference)[0]]);
    } catch (PDOException $e) {
        // L'index unique empêche la double réservation d'un même créneau
        if (str_contains($e->getMessage(), 'creneau_id')) {
            fail(409, 'Ce créneau vient d\'être réservé. Choisissez-en un autre.');
        }
        // sinon collision de référence : on retente
    }
}
fail(500, 'Impossible d\'enregistrer le rendez-vous.');
