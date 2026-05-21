<?php
session_start();

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

$pdo = db();
$message = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['admin_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'login') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=? AND role='admin' LIMIT 1");
    $stmt->execute([$email, $password]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php');
        exit;
    } else {
        $message = 'Invalid admin login.';
    }
}

if (!isset($_SESSION['admin_id'])): ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Railway Admin Login</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:Segoe UI,Arial,sans-serif;background:linear-gradient(135deg,#0f172a,#064e3b)}
.card{width:390px;background:white;border-radius:28px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,.28)}
.brand{width:66px;height:66px;border-radius:20px;background:#facc15;display:grid;place-items:center;color:#0f172a;font-weight:1000;margin-bottom:18px}
h1{margin:0;color:#064e3b}
p{color:#64748b;font-weight:700}
label{display:block;margin:12px 0 6px;font-weight:900;color:#334155}
input{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:14px}
button{width:100%;margin-top:18px;padding:13px;border:0;border-radius:14px;background:#047857;color:white;font-weight:900}
.msg{background:#fee2e2;color:#991b1b;padding:12px;border-radius:12px;margin-bottom:12px;font-weight:800}
</style>
</head>
<body>
<div class="card">
    <div class="brand">RAIL</div>
    <h1>Admin Portal</h1>
    <p>Railway Management System</p>
    <?php if($message): ?><div class="msg"><?=htmlspecialchars($message)?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="form_type" value="login">
        <label>Email</label>
        <input name="email" value="admin@railway.test">
        <label>Password</label>
        <input type="password" name="password" value="admin123">
        <button>Sign In</button>
    </form>
</div>
</body>
</html>
<?php
exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['form_type'] ?? '';

    // Add Train
    if ($type === 'add_train') {
        $train_code          = trim($_POST['train_code']          ?? '');
        $train_name          = trim($_POST['train_name']          ?? '');
        $origin_station      = trim($_POST['origin_station']      ?? '');
        $destination_station = trim($_POST['destination_station'] ?? '');
        $departure_time      = trim($_POST['departure_time']      ?? '');
        $arrival_time        = trim($_POST['arrival_time']        ?? '');
        $fare                = (float)($_POST['fare']             ?? 0);
        $available_seats     = (int)($_POST['available_seats']    ?? 0);
        $status              = trim($_POST['status']              ?? 'Active');

        if ($train_code === '' || $train_name === '' || $origin_station === '' || $destination_station === '') {
            $message = 'All train fields are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO trains(train_code,train_name,origin_station,destination_station,departure_time,arrival_time,fare,available_seats,status,created_at)
                                   VALUES(?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$train_code,$train_name,$origin_station,$destination_station,$departure_time,$arrival_time,$fare,$available_seats,$status,date('Y-m-d H:i:s')]);
            $message = 'Train saved successfully.';
        }
    }

    // Update Train
    if ($type === 'update_train') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE trains SET train_code=?,train_name=?,origin_station=?,destination_station=?,departure_time=?,arrival_time=?,fare=?,available_seats=?,status=? WHERE id=?");
            $stmt->execute([
                trim($_POST['train_code']          ?? ''),
                trim($_POST['train_name']          ?? ''),
                trim($_POST['origin_station']      ?? ''),
                trim($_POST['destination_station'] ?? ''),
                trim($_POST['departure_time']      ?? ''),
                trim($_POST['arrival_time']        ?? ''),
                (float)($_POST['fare']             ?? 0),
                (int)($_POST['available_seats']    ?? 0),
                trim($_POST['status']              ?? 'Active'),
                $id
            ]);
            $message = 'Train updated successfully.';
        }
    }

    // Delete Train
    if ($type === 'delete_train') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM trains WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Train deleted.';
        }
    }

    // FIX 4: Replaced ticket_status stub with real DB update
    if ($type === 'ticket_status') {
        $id             = (int)($_POST['id']             ?? 0);
        $bookingStatus  = trim($_POST['booking_status']  ?? '');
        $paymentStatus  = trim($_POST['payment_status']  ?? '');

        $allowedBooking = ['Pending', 'Confirmed', 'Cancelled'];
        $allowedPayment = ['Unpaid', 'Paid'];

        if ($id > 0
            && in_array($bookingStatus, $allowedBooking, true)
            && in_array($paymentStatus, $allowedPayment, true)
        ) {
            $stmt = $pdo->prepare("UPDATE tickets SET booking_status=?, payment_status=? WHERE id=?");
            $stmt->execute([$bookingStatus, $paymentStatus, $id]);
            $message = 'Ticket #' . $id . ' status updated.';
        } else {
            $message = 'Invalid ticket status values.';
        }
    }
}

$trains     = $pdo->query("SELECT * FROM trains ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$passengers = $pdo->query("SELECT id,name,email,contact,created_at FROM users WHERE role='passenger' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// FIX 4 + FIX 5: Load real tickets with joins; count is now dynamic
$tickets = $pdo->query(
    "SELECT tickets.*, users.name AS passenger_name, users.email AS passenger_email,
            trains.train_code, trains.train_name, trains.origin_station, trains.destination_station
     FROM tickets
     JOIN users  ON users.id  = tickets.passenger_id
     JOIN trains ON trains.id = tickets.train_id
     ORDER BY tickets.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Railway Admin</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:#eef7f2;color:#0f172a}
.layout{display:grid;grid-template-columns:280px 1fr;min-height:100vh}
.sidebar{background:linear-gradient(180deg,#0f172a,#064e3b);color:white;padding:28px 22px}
.logo{width:72px;height:72px;border-radius:22px;background:#facc15;color:#0f172a;display:grid;place-items:center;font-weight:1000;font-size:22px;margin-bottom:22px}
.sidebar h1{font-size:26px;line-height:1.1;margin:0 0 12px}
.sidebar p{color:#d1fae5;line-height:1.55}
.side-card{background:rgba(255,255,255,.09);border-radius:20px;padding:16px;margin-top:18px}
.side-card b{display:block;color:#facc15;margin-bottom:6px}
.logout{display:block;background:#991b1b;color:white;text-decoration:none;text-align:center;border-radius:15px;padding:13px;margin-top:18px;font-weight:900}
.main{padding:28px}
.hero{background:white;border-radius:30px;padding:26px;box-shadow:0 18px 45px rgba(15,23,42,.10);margin-bottom:20px}
.hero h2{margin:0;color:#047857;font-size:34px}
.hero p{color:#64748b;font-weight:700}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
.stat{background:#064e3b;color:white;border-radius:24px;padding:20px}
.stat span{color:#facc15;font-weight:900;text-transform:uppercase;font-size:12px}
.stat strong{display:block;font-size:34px;margin-top:7px}
.grid{display:grid;grid-template-columns:380px 1fr;gap:22px;align-items:start}
.panel{background:white;border-radius:28px;padding:22px;box-shadow:0 16px 40px rgba(15,23,42,.10);margin-bottom:22px}
.panel h3{margin:0 0 17px;color:#047857;font-size:22px}
.notice{background:#d1fae5;color:#065f46;padding:13px 15px;border-radius:15px;margin-bottom:18px;font-weight:900}
.warn{background:#fef3c7;color:#92400e;padding:13px 15px;border-radius:15px;margin-bottom:18px;font-weight:900}
label{display:block;color:#334155;font-weight:900;font-size:12px;margin:10px 0 6px}
input,select{width:100%;min-height:40px;padding:10px;border:1px solid #cbd5e1;border-radius:14px;background:#f8fafc}
button{width:100%;border:0;border-radius:14px;padding:12px;background:#047857;color:white;font-weight:900;margin-top:12px;cursor:pointer}
button.danger{background:#991b1b}
button.secondary{background:#1e40af}
.table-wrap{overflow:auto}
table{width:100%;border-collapse:separate;border-spacing:0 8px;min-width:720px}
th{text-align:left;color:#64748b;text-transform:uppercase;font-size:12px;padding:0 10px}
td{background:#f8fafc;padding:10px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;font-size:13px}
td:first-child{border-left:1px solid #e2e8f0;border-radius:14px 0 0 14px;font-weight:900}
td:last-child{border-right:1px solid #e2e8f0;border-radius:0 14px 14px 0}
.badge{display:inline-block;background:#d1fae5;color:#065f46;border-radius:999px;padding:4px 9px;font-weight:900;font-size:11px}
.badge.inactive{background:#fee2e2;color:#991b1b}
.badge.confirmed{background:#dbeafe;color:#1e40af}
.badge.cancelled{background:#fef3c7;color:#92400e}
.badge.paid{background:#d1fae5;color:#065f46}
.train-card{border:1px solid #e2e8f0;border-radius:18px;padding:16px;margin-bottom:14px;background:#f8fafc}
.train-card h4{margin:0 0 8px;color:#047857}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:8px}
details summary{cursor:pointer;font-weight:900;color:#047857;margin-bottom:8px;list-style:none}
details summary::before{content:'▶ '}
details[open] summary::before{content:'▼ '}
@media(max-width:1000px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="logo">RAIL</div>
    <h1>Railway Admin</h1>
    <p>Manage trains, passengers, and bookings.</p>
    <div class="side-card"><b>Signed in</b><?=htmlspecialchars($_SESSION['admin_name'])?></div>
    <a class="logout" href="?logout=1">Logout Admin</a>
</aside>

<main class="main">
    <div class="hero">
        <h2>Operations Dashboard</h2>
        <p>Ticket management is now fully connected.</p>
    </div>

    <?php if($message): ?>
    <div class="<?=strpos($message,'required')!==false||strpos($message,'Invalid')!==false?'warn':'notice'?>">
        <?=htmlspecialchars($message)?>
    </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat"><span>Trains</span><strong><?=count($trains)?></strong></div>
        <div class="stat"><span>Passengers</span><strong><?=count($passengers)?></strong></div>
        <!-- FIX 5: Tickets count now reads real count from loaded $tickets array -->
        <div class="stat"><span>Tickets</span><strong><?=count($tickets)?></strong></div>
    </div>

    <div class="grid">

        <section>
            <div class="panel">
                <h3>Add Train</h3>
                <form method="post">
                    <input type="hidden" name="form_type" value="add_train">
                    <label>Code</label><input name="train_code" placeholder="RLY-400" required>
                    <label>Train Name</label><input name="train_name" placeholder="Capital Express" required>
                    <label>Origin</label><input name="origin_station" placeholder="Manila" required>
                    <label>Destination</label><input name="destination_station" placeholder="Baguio" required>
                    <label>Departure</label><input name="departure_time" placeholder="07:00 AM" required>
                    <label>Arrival</label><input name="arrival_time" placeholder="12:00 PM" required>
                    <label>Fare (₱)</label><input name="fare" type="number" step="0.01" placeholder="850" required>
                    <label>Seats</label><input name="available_seats" type="number" placeholder="80" required>
                    <label>Status</label>
                    <select name="status">
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                    <button type="submit">Save Train</button>
                </form>
            </div>
        </section>

        <section>

            <div class="panel">
                <h3>Train Records</h3>
                <?php foreach($trains as $t): ?>
                <div class="train-card">
                    <h4>
                        <?=htmlspecialchars($t['train_code'].' — '.$t['train_name'])?>
                        <span class="badge <?=strtolower($t['status'])==='inactive'?'inactive':''?>">
                            <?=htmlspecialchars($t['status'])?>
                        </span>
                    </h4>
                    <small style="color:#64748b">
                        <?=htmlspecialchars($t['origin_station'])?> → <?=htmlspecialchars($t['destination_station'])?>
                        &nbsp;|&nbsp; <?=htmlspecialchars($t['departure_time'])?> – <?=htmlspecialchars($t['arrival_time'])?>
                        &nbsp;|&nbsp; ₱<?=number_format((float)$t['fare'],2)?>
                        &nbsp;|&nbsp; <?=htmlspecialchars($t['available_seats'])?> seats
                    </small>
                    <details style="margin-top:10px">
                        <summary>Edit / Delete</summary>
                        <form method="post" style="margin-bottom:10px">
                            <input type="hidden" name="form_type" value="update_train">
                            <input type="hidden" name="id" value="<?=$t['id']?>">
                            <div class="two-col">
                                <div><label>Code</label><input name="train_code" value="<?=htmlspecialchars($t['train_code'])?>"></div>
                                <div><label>Train Name</label><input name="train_name" value="<?=htmlspecialchars($t['train_name'])?>"></div>
                                <div><label>Origin</label><input name="origin_station" value="<?=htmlspecialchars($t['origin_station'])?>"></div>
                                <div><label>Destination</label><input name="destination_station" value="<?=htmlspecialchars($t['destination_station'])?>"></div>
                                <div><label>Departure</label><input name="departure_time" value="<?=htmlspecialchars($t['departure_time'])?>"></div>
                                <div><label>Arrival</label><input name="arrival_time" value="<?=htmlspecialchars($t['arrival_time'])?>"></div>
                                <div><label>Fare (₱)</label><input name="fare" type="number" step="0.01" value="<?=htmlspecialchars($t['fare'])?>"></div>
                                <div><label>Seats</label><input name="available_seats" type="number" value="<?=htmlspecialchars($t['available_seats'])?>"></div>
                            </div>
                            <label>Status</label>
                            <select name="status">
                                <option <?=$t['status']==='Active'?'selected':''?>>Active</option>
                                <option <?=$t['status']==='Inactive'?'selected':''?>>Inactive</option>
                            </select>
                            <button class="secondary" type="submit">Update Train</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Delete this train permanently?')">
                            <input type="hidden" name="form_type" value="delete_train">
                            <input type="hidden" name="id" value="<?=$t['id']?>">
                            <button class="danger" type="submit">Delete Train</button>
                        </form>
                    </details>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <h3>Passenger Accounts</h3>
                <div class="table-wrap">
                    <table>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th></tr>
                        <?php foreach($passengers as $p): ?>
                        <tr>
                            <td>#<?=$p['id']?></td>
                            <td><?=htmlspecialchars($p['name'])?></td>
                            <td><?=htmlspecialchars($p['email'])?></td>
                            <td><?=htmlspecialchars($p['contact'])?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- FIX 4 + FIX 5: Real ticket table with live status update forms -->
            <div class="panel">
                <h3>Ticket Management</h3>
                <?php if(empty($tickets)): ?>
                <p style="color:#64748b;font-weight:700">No tickets yet. Passengers can book via the C# app.</p>
                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Passenger</th>
                            <th>Train</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Seats</th>
                            <th>Total</th>
                            <th>Booking</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach($tickets as $tk): ?>
                        <tr>
                            <td>#<?=$tk['id']?></td>
                            <td><?=htmlspecialchars($tk['passenger_name'])?></td>
                            <td><?=htmlspecialchars($tk['train_code'].' '.$tk['train_name'])?></td>
                            <td><?=htmlspecialchars($tk['origin_station'].' → '.$tk['destination_station'])?></td>
                            <td><?=htmlspecialchars($tk['travel_date'])?></td>
                            <td><?=htmlspecialchars($tk['seat_count'])?></td>
                            <td>₱<?=number_format((float)$tk['total_amount'],2)?></td>
                            <td>
                                <?php
                                $bc = '';
                                if($tk['booking_status']==='Confirmed') $bc='confirmed';
                                elseif($tk['booking_status']==='Cancelled') $bc='cancelled';
                                ?>
                                <span class="badge <?=$bc?>"><?=htmlspecialchars($tk['booking_status'])?></span>
                            </td>
                            <td>
                                <span class="badge <?=$tk['payment_status']==='Paid'?'paid':''?>">
                                    <?=htmlspecialchars($tk['payment_status'])?>
                                </span>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="form_type" value="ticket_status">
                                    <input type="hidden" name="id" value="<?=$tk['id']?>">
                                    <select name="booking_status" style="margin-bottom:4px">
                                        <option <?=$tk['booking_status']==='Pending'   ?'selected':''?>>Pending</option>
                                        <option <?=$tk['booking_status']==='Confirmed' ?'selected':''?>>Confirmed</option>
                                        <option <?=$tk['booking_status']==='Cancelled' ?'selected':''?>>Cancelled</option>
                                    </select>
                                    <select name="payment_status" style="margin-bottom:4px">
                                        <option <?=$tk['payment_status']==='Unpaid'?'selected':''?>>Unpaid</option>
                                        <option <?=$tk['payment_status']==='Paid'  ?'selected':''?>>Paid</option>
                                    </select>
                                    <button type="submit" style="padding:7px;font-size:12px">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </section>
    </div>
</main>
</div>
</body>
</html>