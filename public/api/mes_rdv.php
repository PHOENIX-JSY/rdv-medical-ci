<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
$patient = require_auth();
$ref = isset($_GET['reference']) ? (string)$_GET['reference'] : null;
json_out(200, ['rendez_vous' => fetch_rdv((int)$patient['id'], $ref)]);
