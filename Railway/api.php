<?php
/*
    RAILWAY STARTER API

    This file is intentionally incomplete.
    It already creates SQLite tables and supports basic train listing.

    Students should complete the missing API actions step by step:
    - register_passenger
    - book_ticket
    - list_tickets
    - update_ticket_status
    - add_train
    - update_train
    - delete_train
*/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$dbFile = __DIR__ . '/railway.sqlite';

function db() {
    global $dbFile;

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        contact TEXT,
        created_at TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS trains (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        train_code TEXT NOT NULL,
        train_name TEXT NOT NULL,
        origin_station TEXT NOT NULL,
        destination_station TEXT NOT NULL,
        departure_time TEXT NOT NULL,
        arrival_time TEXT NOT NULL,
        fare REAL NOT NULL DEFAULT 0,
        available_seats INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL DEFAULT 'Active',
        created_at TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        passenger_id INTEGER NOT NULL,
        train_id INTEGER NOT NULL,
        travel_date TEXT NOT NULL,
        seat_count INTEGER NOT NULL DEFAULT 1,
        total_amount REAL NOT NULL DEFAULT 0,
        booking_status TEXT NOT NULL DEFAULT 'Pending',
        payment_status TEXT NOT NULL DEFAULT 'Unpaid',
        created_at TEXT NOT NULL
    )");

    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    if ((int)$count === 0) {
        $stmt = $pdo->prepare("INSERT INTO users(name,email,password,role,contact,created_at) VALUES(?,?,?,?,?,?)");
        $stmt->execute(['Railway Administrator','admin@railway.test','admin123','admin','',date('Y-m-d H:i:s')]);
        $stmt->execute(['Juan Passenger','passenger@railway.test','passenger123','passenger','09123456789',date('Y-m-d H:i:s')]);
    }

    $trainCount = $pdo->query("SELECT COUNT(*) FROM trains")->fetchColumn();

    if ((int)$trainCount === 0) {
        $stmt = $pdo->prepare("INSERT INTO trains(train_code,train_name,origin_station,destination_station,departure_time,arrival_time,fare,available_seats,status,created_at)
                               VALUES(?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute(['RLY-101','North Express','Manila Central','Baguio Terminal','06:00 AM','12:30 PM',850,80,'Active',date('Y-m-d H:i:s')]);
        $stmt->execute(['RLY-202','Coastal Line','Cebu Station','Davao Station','09:00 AM','05:00 PM',1250,90,'Active',date('Y-m-d H:i:s')]);
    }

    return $pdo;
}

function ok($data = []) {
    echo json_encode(['success' => true] + $data);
    exit;
}

function fail($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$pdo = db();
$action = $_GET['action'] ?? $_POST['action'] ?? 'ping';

try {
    if ($action === 'ping') {
        ok(['message' => 'Railway starter API is running']);
    }

    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'passenger');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=? AND role=? LIMIT 1");
        $stmt->execute([$email, $password, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) fail('Invalid login details.');

        ok(['message' => 'Login successful.', 'user' => $user]);
    }

    if ($action === 'list_trains') {
        $rows = $pdo->query("SELECT * FROM trains ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        ok(['trains' => $rows]);
    }

    if ($action === 'list_active_trains') {
        $stmt = $pdo->prepare("SELECT * FROM trains WHERE status='Active' ORDER BY id DESC");
        $stmt->execute();
        ok(['trains' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'register_passenger') {
        fail('Registration is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'add_train') {
        fail('Add train is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'update_train') {
        fail('Update train is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'delete_train') {
        fail('Delete train is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'book_ticket') {
        fail('Ticket booking is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'list_tickets') {
        fail('Ticket listing is not implemented yet. Follow the commit guide.');
    }

    if ($action === 'update_ticket_status') {
        fail('Ticket status update is not implemented yet. Follow the commit guide.');
    }

    fail('Invalid action.');
} catch (Exception $e) {
    fail($e->getMessage(), 500);
}
?>