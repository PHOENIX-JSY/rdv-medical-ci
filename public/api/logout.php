<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('POST');
start_session();
$_SESSION = [];
session_destroy();
json_out(200, ['ok' => true]);
