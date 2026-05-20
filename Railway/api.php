<?php
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
        $stmt->execute(['RLY-303','Metro Shuttle','Pasay Station','Batangas Station','08:00 AM','11:00 AM',450,100,'Active',date('Y-m-d H:i:s')]);
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
    if ($action === 'ping') ok(['message' => 'Railway API is running']);

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

    if ($action === 'register_passenger') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $contact = trim($_POST['contact'] ?? '');

        if ($name === '' || $email === '' || $password === '') fail('Name, email, and password are required.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Valid email is required.');
        if (strlen($password) < 6) fail('Password must be at least 6 characters.');

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) fail('Email is already registered.');

        $stmt = $pdo->prepare("INSERT INTO users(name,email,password,role,contact,created_at) VALUES(?,?,?,?,?,?)");
        $stmt->execute([$name,$email,$password,'passenger',$contact,date('Y-m-d H:i:s')]);

        ok(['message' => 'Passenger account created.']);
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

    if ($action === 'add_train') {
        $stmt = $pdo->prepare("INSERT INTO trains(train_code,train_name,origin_station,destination_station,departure_time,arrival_time,fare,available_seats,status,created_at)
                               VALUES(?,?,?,?,?,?,?,?,?,?)");

        $stmt->execute([
            trim($_POST['train_code'] ?? ''),
            trim($_POST['train_name'] ?? ''),
            trim($_POST['origin_station'] ?? ''),
            trim($_POST['destination_station'] ?? ''),
            trim($_POST['departure_time'] ?? ''),
            trim($_POST['arrival_time'] ?? ''),
            (float)($_POST['fare'] ?? 0),
            (int)($_POST['available_seats'] ?? 0),
            trim($_POST['status'] ?? 'Active'),
            date('Y-m-d H:i:s')
        ]);

        ok(['message' => 'Train saved.']);
    }

    if ($action === 'update_train') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid train ID.');

        $stmt = $pdo->prepare("UPDATE trains SET train_code=?, train_name=?, origin_station=?, destination_station=?, departure_time=?, arrival_time=?, fare=?, available_seats=?, status=? WHERE id=?");
        $stmt->execute([
            trim($_POST['train_code'] ?? ''),
            trim($_POST['train_name'] ?? ''),
            trim($_POST['origin_station'] ?? ''),
            trim($_POST['destination_station'] ?? ''),
            trim($_POST['departure_time'] ?? ''),
            trim($_POST['arrival_time'] ?? ''),
            (float)($_POST['fare'] ?? 0),
            (int)($_POST['available_seats'] ?? 0),
            trim($_POST['status'] ?? 'Active'),
            $id
        ]);

        ok(['message' => 'Train updated.']);
    }

    if ($action === 'delete_train') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid train ID.');

        $stmt = $pdo->prepare("DELETE FROM trains WHERE id=?");
        $stmt->execute([$id]);

        ok(['message' => 'Train deleted.']);
    }

    if ($action === 'list_passengers') {
        $stmt = $pdo->prepare("SELECT id,name,email,contact,created_at FROM users WHERE role='passenger' ORDER BY id DESC");
        $stmt->execute();
        ok(['passengers' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'book_ticket') {
        $passengerId = (int)($_POST['passenger_id'] ?? 0);
        $trainId = (int)($_POST['train_id'] ?? 0);
        $travelDate = trim($_POST['travel_date'] ?? '');
        $seatCount = max(1, (int)($_POST['seat_count'] ?? 1));

        if ($passengerId <= 0 || $trainId <= 0 || $travelDate === '') fail('Passenger, train, and travel date are required.');

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT fare, available_seats, status FROM trains WHERE id=? LIMIT 1");
            $stmt->execute([$trainId]);
            $train = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$train) {
                $pdo->rollBack();
                fail('Train not found.');
            }
            if ($train['status'] !== 'Active') {
                $pdo->rollBack();
                fail('Train is not active.');
            }
            if ((int)$train['available_seats'] < $seatCount) {
                $pdo->rollBack();
                fail('Not enough available seats.');
            }

            $total = (float)$train['fare'] * $seatCount;

            $stmt = $pdo->prepare("UPDATE trains SET available_seats = available_seats - ? WHERE id=? AND available_seats >= ?");
            $stmt->execute([$seatCount, $trainId, $seatCount]);

            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                fail('Not enough available seats.');
            }

            $stmt = $pdo->prepare("INSERT INTO tickets(passenger_id,train_id,travel_date,seat_count,total_amount,booking_status,payment_status,created_at)
                                   VALUES(?,?,?,?,?,?,?,?)");
            $stmt->execute([$passengerId,$trainId,$travelDate,$seatCount,$total,'Pending','Unpaid',date('Y-m-d H:i:s')]);

            $ticketId = (int)$pdo->lastInsertId();
            $pdo->commit();

            ok(['message' => 'Ticket request submitted.', 'ticket_id' => $ticketId]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if ($action === 'list_tickets') {
        $passengerId = (int)($_GET['passenger_id'] ?? 0);

        $sql = "SELECT tickets.*, users.name AS passenger_name, users.email AS passenger_email, trains.train_code, trains.train_name, trains.origin_station, trains.destination_station
                FROM tickets
                JOIN users ON users.id=tickets.passenger_id
                JOIN trains ON trains.id=tickets.train_id";

        if ($passengerId > 0) {
            $stmt = $pdo->prepare($sql . " WHERE tickets.passenger_id=? ORDER BY tickets.id DESC");
            $stmt->execute([$passengerId]);
            ok(['tickets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }

        $rows = $pdo->query($sql . " ORDER BY tickets.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        ok(['tickets' => $rows]);
    }

    if ($action === 'update_ticket_status') {
        $id = (int)($_POST['id'] ?? 0);
        $bookingStatus = trim($_POST['booking_status'] ?? '');
        $paymentStatus = trim($_POST['payment_status'] ?? '');

        if ($id <= 0) fail('Invalid ticket ID.');
        if (!in_array($bookingStatus, ['Pending','Confirmed','Cancelled'], true)) fail('Invalid booking status.');
        if (!in_array($paymentStatus, ['Unpaid','Paid'], true)) fail('Invalid payment status.');

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT train_id, seat_count, booking_status FROM tickets WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                $pdo->rollBack();
                fail('Ticket not found.');
            }

            $oldStatus = $ticket['booking_status'];
            $trainId = (int)$ticket['train_id'];
            $seatCount = (int)$ticket['seat_count'];

            if ($oldStatus !== 'Cancelled' && $bookingStatus === 'Cancelled') {
                $stmt = $pdo->prepare("UPDATE trains SET available_seats = available_seats + ? WHERE id=?");
                $stmt->execute([$seatCount, $trainId]);
            }

            if ($oldStatus === 'Cancelled' && $bookingStatus !== 'Cancelled') {
                $stmt = $pdo->prepare("UPDATE trains SET available_seats = available_seats - ? WHERE id=? AND available_seats >= ?");
                $stmt->execute([$seatCount, $trainId, $seatCount]);

                if ($stmt->rowCount() !== 1) {
                    $pdo->rollBack();
                    fail('Not enough available seats to restore this ticket.');
                }
            }

            $stmt = $pdo->prepare("UPDATE tickets SET booking_status=?, payment_status=? WHERE id=?");
            $stmt->execute([$bookingStatus,$paymentStatus,$id]);

            $pdo->commit();
            ok(['message' => 'Ticket status updated.']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    fail('Invalid action.');
} catch (Exception $e) {
    fail($e->getMessage(), 500);
}
?>