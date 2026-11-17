<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
$ville = trim((string)($_GET['ville'] ?? ''));
$service = trim((string)($_GET['service'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));

$stmt = db()->prepare(
    "SELECT e.id, e.nom, e.ville, e.commune, e.adresse
     FROM etablissements e
     WHERE (:ville = '' OR e.ville = :ville)
       AND (:q = '' OR e.nom LIKE '%' || :q || '%')
       AND (:service = '' OR EXISTS (
            SELECT 1 FROM services s WHERE s.etablissement_id = e.id AND s.nom = :service))
     ORDER BY e.nom"
);
$stmt->execute([':ville' => $ville, ':q' => $q, ':service' => $service]);
$etabs = $stmt->fetchAll();

$srv = db()->prepare('SELECT nom FROM services WHERE etablissement_id = ? ORDER BY nom');
foreach ($etabs as &$e) {
    $srv->execute([$e['id']]);
    $e['services'] = $srv->fetchAll(PDO::FETCH_COLUMN);
}
json_out(200, ['etablissements' => $etabs]);
