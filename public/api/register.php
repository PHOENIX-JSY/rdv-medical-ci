<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_method('POST');
$in = input();
$nom = trim((string)($in['nom'] ?? ''));
$tel = normalize_phone((string)($in['telephone'] ?? ''));
$mdp = (string)($in['mot_de_passe'] ?? '');

if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
    fail(422, 'Nom et prénom requis (2 à 100 caractères).');
}
if (!preg_match('/^\+?[0-9]{8,15}$/', $tel)) {
    fail(422, 'Numéro de téléphone invalide.');
}
if (strlen($mdp) < 8) {
    fail(422, 'Le mot de passe doit contenir au moins 8 caractères.');
}

try {
    $stmt = db()->prepare('INSERT INTO patients (nom, telephone, mot_de_passe) VALUES (?, ?, ?)');
    $stmt->execute([$nom, $tel, password_hash($mdp, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    fail(409, 'Un compte existe déjà avec ce numéro.');
}
$id = (int)db()->lastInsertId();
login_patient($id);
json_out(201, ['patient' => ['id' => $id, 'nom' => $nom, 'telephone' => $tel]]);
