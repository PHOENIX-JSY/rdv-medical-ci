<?php
declare(strict_types=1);
/**
 * Initialise la base SQLite : php database/init.php [--reset]
 * - crée les tables et charge les établissements de démonstration (première fois)
 * - (re)génère les créneaux des 14 prochains jours (peut être relancé à tout moment)
 */
require __DIR__ . '/../src/bootstrap.php';

$path = env('DB_PATH', 'database/rdv.sqlite');
if ($path[0] !== '/') {
    $path = ROOT . '/' . $path;
}
if (in_array('--reset', $argv, true) && is_file($path)) {
    unlink($path);
}
if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0775, true);
}

$pdo = db();
$pdo->exec((string)file_get_contents(__DIR__ . '/schema.sql'));

if ((int)$pdo->query('SELECT COUNT(*) FROM etablissements')->fetchColumn() === 0) {
    $pdo->exec((string)file_get_contents(__DIR__ . '/seed.sql'));
    echo "Établissements et services de démonstration chargés.\n";
}

$heures = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
$services = $pdo->query('SELECT id FROM services')->fetchAll(PDO::FETCH_COLUMN);
$ins = $pdo->prepare('INSERT OR IGNORE INTO creneaux (service_id, debut) VALUES (?, ?)');
$count = 0;
$pdo->beginTransaction();
for ($d = 0; $d < 14; $d++) {
    $jour = new DateTimeImmutable("+$d day");
    if ((int)$jour->format('N') >= 6) {
        continue; // pas de créneaux le week-end
    }
    foreach ($services as $sid) {
        foreach ($heures as $h) {
            $ins->execute([$sid, $jour->format('Y-m-d') . ' ' . $h]);
            $count += $ins->rowCount();
        }
    }
}
$pdo->commit();
echo "$count nouveaux créneaux générés.\nBase prête : $path\n";
