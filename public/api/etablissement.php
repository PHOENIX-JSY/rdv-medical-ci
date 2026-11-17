<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT id, nom, ville, commune, adresse, telephone, infos FROM etablissements WHERE id = ?');
$stmt->execute([$id]);
$e = $stmt->fetch();
if (!$e) {
    fail(404, 'Établissement introuvable.');
}
$srv = db()->prepare('SELECT id, nom FROM services WHERE etablissement_id = ? ORDER BY nom');
$srv->execute([$id]);
$e['services'] = $srv->fetchAll();
json_out(200, ['etablissement' => $e]);
