<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('GET');
json_out(200, ['patient' => current_patient()]);
