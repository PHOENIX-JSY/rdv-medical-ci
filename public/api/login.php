<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('POST');
$in = input();
$tel = normalize_phone((string)($in['telephone'] ?? ''));
$mdp = (string)($in['mot_de_passe'] ?? '');

$stmt = db()->prepare('SELECT id, nom, telephone, mot_de_passe FROM patients WHERE telephone = ?');
$stmt->execute([$tel]);
$p = $stmt->fetch();
if (!$p || !password_verify($mdp, $p['mot_de_passe'])) {
    fail(401, 'Téléphone ou mot de passe incorrect.');
}
login_patient((int)$p['id']);
json_out(200, ['patient' => ['id' => $p['id'], 'nom' => $p['nom'], 'telephone' => $p['telephone']]]);
