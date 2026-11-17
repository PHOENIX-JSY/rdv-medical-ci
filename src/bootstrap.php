<?php
declare(strict_types=1);

date_default_timezone_set('Africa/Abidjan');

const ROOT = __DIR__ . '/..';

/** Lit une variable d'environnement (priorité : environnement, puis fichier .env). */
function env(string $key, string $default = ''): string
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $file = ROOT . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v);
            }
        }
    }
    $fromEnv = getenv($key);
    return $fromEnv !== false ? $fromEnv : ($vars[$key] ?? $default);
}

/** Connexion unique à la base SQLite (requêtes préparées uniquement). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $path = env('DB_PATH', 'database/rdv.sqlite');
        if ($path[0] !== '/') {
            $path = ROOT . '/' . $path;
        }
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

/** Envoie une réponse JSON et termine le script. */
function json_out(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(int $status, string $message): never
{
    json_out($status, ['error' => $message]);
}

/** Vérifie la méthode HTTP ; les POST doivent être en JSON (protection CSRF de base). */
function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        fail(405, 'Méthode non autorisée');
    }
    if ($method === 'POST' && !str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        fail(415, 'Content-Type application/json requis');
    }
}

function input(): array
{
    $data = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($data) ? $data : [];
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(env('SESSION_NAME', 'rdvmedical'));
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
        session_start();
    }
}

function current_patient(): ?array
{
    start_session();
    $id = $_SESSION['patient_id'] ?? null;
    if ($id === null) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, nom, telephone FROM patients WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function require_auth(): array
{
    $patient = current_patient();
    if ($patient === null) {
        fail(401, 'Connexion requise');
    }
    return $patient;
}

function login_patient(int $id): void
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['patient_id'] = $id;
}

function normalize_phone(string $phone): string
{
    return preg_replace('/[\s.\-]/', '', $phone) ?? '';
}

/** Rendez-vous d'un patient, avec établissement et service. */
function fetch_rdv(int $patientId, ?string $reference = null): array
{
    $sql = 'SELECT r.id, r.reference, r.motif, r.statut, c.debut,
                   s.nom AS service, e.nom AS etablissement, e.adresse, e.ville, e.telephone
            FROM rendez_vous r
            JOIN creneaux c ON c.id = r.creneau_id
            JOIN services s ON s.id = c.service_id
            JOIN etablissements e ON e.id = s.etablissement_id
            WHERE r.patient_id = :pid';
    $params = [':pid' => $patientId];
    if ($reference !== null) {
        $sql .= ' AND r.reference = :ref';
        $params[':ref'] = $reference;
    }
    $stmt = db()->prepare($sql . ' ORDER BY c.debut');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    // Le serveur est la seule référence d'heure (fuseau Africa/Abidjan)
    $now = date('Y-m-d H:i');
    foreach ($rows as &$row) {
        $row['a_venir'] = $row['statut'] === 'confirme' && $row['debut'] > $now;
    }
    return $rows;
}
