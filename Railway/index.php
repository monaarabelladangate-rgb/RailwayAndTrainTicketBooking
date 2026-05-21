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
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=? AND role='admin' LIMIT 1");
    $stmt->execute([$email, $password]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php');
        exit;
    } else {
        $message = 'Invalid admin login.';
    }
}

if (!isset($_SESSION['admin_id'])):
?>
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
    <p>Starter management access</p>
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
    $message = 'Feature not yet connected. Follow the commit guide to implement this action.';
}

$trains = $pdo->query("SELECT * FROM trains ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$passengers = $pdo->query("SELECT id,name,email,contact,created_at FROM users WHERE role='passenger' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$tickets = [];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Railway Admin Starter</title>
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
.notice{background:#fef3c7;color:#92400e;padding:13px 15px;border-radius:15px;margin-bottom:18px;font-weight:900}
label{display:block;color:#334155;font-weight:900;font-size:12px;margin:10px 0 6px}
input,select{width:100%;min-height:40px;padding:10px;border:1px solid #cbd5e1;border-radius:14px;background:#f8fafc}
button{width:100%;border:0;border-radius:14px;padding:12px;background:#047857;color:white;font-weight:900;margin-top:12px}
.table-wrap{overflow:auto}
table{width:100%;border-collapse:separate;border-spacing:0 10px;min-width:720px}
th{text-align:left;color:#64748b;text-transform:uppercase;font-size:12px;padding:0 10px}
td{background:#f8fafc;padding:12px 10px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}
td:first-child{border-left:1px solid #e2e8f0;border-radius:14px 0 0 14px;font-weight:900}
td:last-child{border-right:1px solid #e2e8f0;border-radius:0 14px 14px 0}
.badge{display:inline-block;background:#d1fae5;color:#065f46;border-radius:999px;padding:5px 9px;font-weight:900;font-size:12px}
@media(max-width:1000px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="logo">RAIL</div>
    <h1>Railway Admin Starter</h1>
    <p>Basic dashboard prepared for step-by-step development.</p>
    <div class="side-card"><b>Signed in</b><?=htmlspecialchars($_SESSION['admin_name'])?></div>
    <a class="logout" href="?logout=1">Logout Admin</a>
</aside>

<main class="main">
    <div class="hero">
        <h2>Operations Dashboard</h2>
        <p>Starter version for train scheduling and ticket management.</p>
    </div>

    <?php if($message): ?><div class="notice"><?=htmlspecialchars($message)?></div><?php endif; ?>

    <div class="stats">
        <div class="stat"><span>Trains</span><strong><?=count($trains)?></strong></div>
        <div class="stat"><span>Passengers</span><strong><?=count($passengers)?></strong></div>
        <div class="stat"><span>Tickets</span><strong>0</strong></div>
    </div>

    <div class="grid">
        <section class="panel">
            <h3>Add Train</h3>
            <form method="post">
                <input type="hidden" name="form_type" value="add_train">
                <label>Code</label><input name="train_code" placeholder="RLY-400">
                <label>Train Name</label><input name="train_name" placeholder="Capital Express">
                <label>Origin</label><input name="origin_station" placeholder="Manila">
                <label>Destination</label><input name="destination_station" placeholder="Baguio">
                <label>Departure</label><input name="departure_time" placeholder="07:00 AM">
                <label>Arrival</label><input name="arrival_time" placeholder="12:00 PM">
                <label>Fare</label><input name="fare" type="number" step="0.01" placeholder="850">
                <label>Seats</label><input name="available_seats" type="number" placeholder="80">
                <label>Status</label><select name="status"><option>Active</option><option>Inactive</option></select>
                <button>Save Train</button>
            </form>
        </section>

        <section>
            <div class="panel">
                <h3>Train Records</h3>
                <div class="table-wrap">
                    <table>
                        <tr><th>ID</th><th>Train</th><th>Route</th><th>Time</th><th>Fare</th><th>Status</th></tr>
                        <?php foreach($trains as $t): ?>
                        <tr>
                            <td>#<?=$t['id']?></td>
                            <td><?=htmlspecialchars($t['train_code'].' - '.$t['train_name'])?></td>
                            <td><?=htmlspecialchars($t['origin_station'].' to '.$t['destination_station'])?></td>
                            <td><?=htmlspecialchars($t['departure_time'].' - '.$t['arrival_time'])?></td>
                            <td>₱<?=number_format((float)$t['fare'],2)?></td>
                            <td><span class="badge"><?=htmlspecialchars($t['status'])?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
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

            <div class="panel">
                <h3>Ticket Management</h3>
                <div class="notice">Ticket records and status updates will be connected in later commits.</div>
            </div>
        </section>
    </div>
</main>
</div>
</body>
</html>
