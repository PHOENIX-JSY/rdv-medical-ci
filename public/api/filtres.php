<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
$villes = db()->query('SELECT DISTINCT ville FROM etablissements ORDER BY ville')->fetchAll(PDO::FETCH_COLUMN);
$services = db()->query('SELECT DISTINCT nom FROM services ORDER BY nom')->fetchAll(PDO::FETCH_COLUMN);
json_out(200, ['villes' => $villes, 'services' => $services]);
