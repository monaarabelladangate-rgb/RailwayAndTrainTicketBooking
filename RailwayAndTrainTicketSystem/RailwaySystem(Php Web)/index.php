<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Railway Admin Management</title>
<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Segoe UI,Arial,sans-serif;
    background:#eef7f7;
    color:#102033;
}
.layout{
    display:grid;
    grid-template-columns:280px 1fr;
    min-height:100vh;
}
.side{
    background:#020817;
    color:white;
    padding:26px 18px;
}
.logo{
    width:70px;
    height:70px;
    border-radius:18px;
    background:#ffd21f;
    color:#020817;
    display:grid;
    place-items:center;
    font-size:26px;
    font-weight:1000;
    margin-bottom:22px;
}
.side h1{
    margin:0 0 12px;
    font-size:28px;
    line-height:1.1;
}
.side p{
    color:#dbeafe;
    line-height:1.55;
    font-size:14px;
}
.card-side{
    background:#111827;
    border:1px solid #263246;
    border-radius:18px;
    padding:16px;
    margin-top:18px;
}
.card-side b{
    color:#ffd21f;
}
.logout{
    width:100%;
    border:0;
    border-radius:14px;
    padding:13px;
    background:#991b1b;
    color:white;
    font-weight:900;
    margin-top:18px;
    cursor:pointer;
}
.main{
    padding:26px;
}
.hero{
    background:white;
    border-radius:26px;
    padding:26px;
    box-shadow:0 18px 45px rgba(15,23,42,.10);
    margin-bottom:22px;
}
.hero h2{
    margin:0;
    color:#065f46;
    font-size:32px;
}
.hero p{
    color:#64748b;
    font-weight:700;
}
.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:22px;
}
.stat{
    background:white;
    border-radius:22px;
    padding:18px;
    box-shadow:0 12px 32px rgba(15,23,42,.08);
}
.stat span{
    display:block;
    color:#64748b;
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
}
.stat strong{
    display:block;
    font-size:32px;
    color:#065f46;
    margin-top:4px;
}
.panel{
    background:white;
    border-radius:26px;
    padding:22px;
    margin-bottom:22px;
    box-shadow:0 18px 45px rgba(15,23,42,.10);
}
.panel h3{
    margin:0 0 18px;
    font-size:24px;
    color:#065f46;
}
.form-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:12px;
}
label{
    display:block;
    color:#334155;
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.4px;
}
input,select,textarea{
    width:100%;
    min-height:42px;
    border:1px solid #cbd5e1;
    border-radius:14px;
    padding:10px 12px;
    outline:none;
    background:#f8fafc;
}
input:focus,select:focus,textarea:focus{
    background:white;
    border-color:#16a34a;
    box-shadow:0 0 0 4px rgba(34,197,94,.14);
}
.btn{
    border:0;
    border-radius:14px;
    padding:12px 15px;
    font-weight:900;
    cursor:pointer;
}
.btn-green{background:#15803d;color:white}
.btn-blue{background:#2563eb;color:white}
.btn-red{background:#dc2626;color:white}
.btn-dark{background:#334155;color:white}
.notice{
    background:#dcfce7;
    color:#166534;
    border-left:6px solid #22c55e;
    padding:13px 16px;
    border-radius:14px;
    margin-bottom:18px;
    font-weight:900;
}
.error{
    background:#fee2e2;
    color:#991b1b;
    border-left:6px solid #ef4444;
    padding:13px 16px;
    border-radius:14px;
    margin-bottom:18px;
    font-weight:900;
}
.table-wrap{
    overflow:auto;
}
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 10px;
    min-width:900px;
}
th{
    color:#64748b;
    text-align:left;
    text-transform:uppercase;
    font-size:12px;
    padding:0 10px;
}
td{
    background:#f8fafc;
    padding:12px 10px;
    border-top:1px solid #e2e8f0;
    border-bottom:1px solid #e2e8f0;
    vertical-align:top;
}
td:first-child{
    border-left:1px solid #e2e8f0;
    border-radius:14px 0 0 14px;
    font-weight:900;
}
td:last-child{
    border-right:1px solid #e2e8f0;
    border-radius:0 14px 14px 0;
}
.train-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:16px;
}
.train-card{
    border:1px solid #dbeafe;
    border-radius:20px;
    padding:16px;
    background:#f8fafc;
}
.train-card h4{
    margin:0 0 12px;
    color:#065f46;
}
.train-edit{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
}
.status-badge{
    display:inline-block;
    border-radius:999px;
    padding:6px 10px;
    background:#dcfce7;
    color:#166534;
    font-size:12px;
    font-weight:900;
}
.login-wrap{
    min-height:100vh;
    display:grid;
    place-items:center;
    background:linear-gradient(135deg,#020817,#064e3b);
}
.login-card{
    width:420px;
    background:white;
    border-radius:28px;
    padding:34px;
    box-shadow:0 28px 70px rgba(0,0,0,.30);
}
.login-card h1{
    margin:0 0 8px;
    color:#065f46;
}
.login-card p{
    color:#64748b;
    font-weight:700;
}
@media(max-width:1200px){
    .layout{grid-template-columns:1fr}
    .stats,.form-grid,.train-grid,.train-edit{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div id="app"></div>

<script>
const API = '../Api/api.php';

let currentAdmin = JSON.parse(localStorage.getItem('railway_admin') || 'null');

function esc(value){
    return String(value ?? '').replace(/[&<>"']/g, function(char){
        return {
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            "'":'&#039;'
        }[char];
    });
}

function formData(obj){
    const fd = new FormData();

    Object.keys(obj).forEach(key => {
        fd.append(key, obj[key]);
    });

    return fd;
}

async function apiGet(action){
    const response = await fetch(`${API}?action=${encodeURIComponent(action)}`);

    return await response.json();
}

async function apiGetUrl(url){
    const response = await fetch(url);

    return await response.json();
}

async function apiPost(data){
    const response = await fetch(API, {
        method:'POST',
        body:formData(data)
    });

    return await response.json();
}

function showMessage(text, type='notice'){
    const box = document.getElementById('messageBox');

    if(!box) return;

    box.className = type;
    box.textContent = text;
    box.style.display = 'block';

    setTimeout(() => {
        box.style.display = 'none';
    }, 3000);
}

function renderLogin(){
    document.getElementById('app').innerHTML = `
        <div class="login-wrap">
            <form class="login-card" id="loginForm">
                <h1>Railway Admin</h1>
                <p>Web app calls the separated API folder using GET and POST requests.</p>

                <div id="loginMsg"></div>

                <label>Email</label>
                <input name="email" value="admin@railway.test">

                <label>Password</label>
                <input name="password" type="password" value="admin123">

                <button class="btn btn-green" style="width:100%;margin-top:18px">Login</button>
            </form>
        </div>
    `;

    document.getElementById('loginForm').addEventListener('submit', async function(e){
        e.preventDefault();

        const data = new FormData(this);

        try{
            const result = await apiPost({
                action:'login',
                role:'admin',
                email:data.get('email'),
                password:data.get('password')
            });

            if(!result.success){
                document.getElementById('loginMsg').innerHTML = `<div class="error">${esc(result.message)}</div>`;
                return;
            }

            currentAdmin = result.user;
            localStorage.setItem('railway_admin', JSON.stringify(currentAdmin));
            renderDashboard();
        }catch(error){
            document.getElementById('loginMsg').innerHTML = `<div class="error">Cannot connect to API. Run PHP from the main folder.</div>`;
        }
    });
}

function renderDashboardShell(){
    document.getElementById('app').innerHTML = `
        <div class="layout">
            <aside class="side">
                <div class="logo">RAIL</div>
                <h1>Railway Admin Management</h1>
                <p>PHP Web App folder only sends GET/POST requests to the separated API folder.</p>

                <div class="card-side">
                    <b>Signed In</b><br>
                    ${esc(currentAdmin?.name || 'Railway Administrator')}
                </div>

                <div class="card-side">
                    <b>API Structure</b><br><br>
                    C# → Api/api.php<br>
                    PHP → Api/api.php<br>
                    API → Database/railway.sqlite
                </div>

                <button class="logout" onclick="logout()">Logout Admin</button>
            </aside>

            <main class="main">
                <div class="hero">
                    <h2>Railway Operations Dashboard</h2>
                    <p>Manage trains, passengers, and ticket booking statuses through the separated API.</p>
                </div>

                <div id="messageBox" style="display:none"></div>

                <div class="stats">
                    <div class="stat"><span>Trains</span><strong id="statTrains">0</strong></div>
                    <div class="stat"><span>Passengers</span><strong id="statPassengers">0</strong></div>
                    <div class="stat"><span>Tickets</span><strong id="statTickets">0</strong></div>
                    <div class="stat"><span>API</span><strong>OK</strong></div>
                </div>

                <section class="panel">
                    <h3>Add Train</h3>
                    <form id="addTrainForm" class="form-grid">
                        <div>
                            <label>Code</label>
                            <input name="train_code" required>
                        </div>
                        <div>
                            <label>Train Name</label>
                            <input name="train_name" required>
                        </div>
                        <div>
                            <label>Origin</label>
                            <input name="origin_station" required>
                        </div>
                        <div>
                            <label>Destination</label>
                            <input name="destination_station" required>
                        </div>
                        <div>
                            <label>Fare</label>
                            <input name="fare" type="number" step="0.01" required>
                        </div>
                        <div>
                            <label>Departure</label>
                            <input name="departure_time" required placeholder="08:00 AM">
                        </div>
                        <div>
                            <label>Arrival</label>
                            <input name="arrival_time" required placeholder="11:00 AM">
                        </div>
                        <div>
                            <label>Seats</label>
                            <input name="available_seats" type="number" required>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status"><option>Active</option><option>Inactive</option></select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button class="btn btn-green" style="width:100%">Save Train</button>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <h3>Train Records</h3>
                    <div id="trainsList" class="train-grid"></div>
                </section>

                <section class="panel">
                    <h3>Passenger Accounts</h3>
                    <div class="table-wrap">
                        <table id="passengerTable"></table>
                    </div>
                </section>

                <section class="panel">
                    <h3>Ticket Bookings</h3>
                    <div class="table-wrap">
                        <table id="ticketTable"></table>
                    </div>
                </section>
            </main>
        </div>
    `;

    document.getElementById('addTrainForm').addEventListener('submit', async function(e){
        e.preventDefault();

        const data = Object.fromEntries(new FormData(this).entries());
        data.action = 'add_train';

        const result = await apiPost(data);

        showMessage(result.message, result.success ? 'notice' : 'error');

        if(result.success){
            this.reset();
            loadAll();
        }
    });
}

async function renderDashboard(){
    renderDashboardShell();
    await loadAll();
}

async function loadAll(){
    try{
        const trains = await apiGet('list_trains');
        const passengers = await apiGet('list_passengers');
        const tickets = await apiGet('list_tickets');

        renderTrains(trains.trains || []);
        renderPassengers(passengers.passengers || []);
        renderTickets(tickets.tickets || []);

        document.getElementById('statTrains').textContent = (trains.trains || []).length;
        document.getElementById('statPassengers').textContent = (passengers.passengers || []).length;
        document.getElementById('statTickets').textContent = (tickets.tickets || []).length;
    }catch(error){
        showMessage('Cannot connect to separated API folder. Run PHP from the main folder.', 'error');
    }
}

function renderTrains(trains){
    const box = document.getElementById('trainsList');

    if(trains.length === 0){
        box.innerHTML = '<p>No train records found.</p>';
        return;
    }

    box.innerHTML = trains.map(t => `
        <div class="train-card">
            <h4>${esc(t.train_code)} - ${esc(t.train_name)}</h4>

            <form class="train-edit" onsubmit="updateTrain(event, ${esc(t.id)})">
                <div>
                    <label>Code</label>
                    <input name="train_code" value="${esc(t.train_code)}">
                </div>
                <div>
                    <label>Name</label>
                    <input name="train_name" value="${esc(t.train_name)}">
                </div>
                <div>
                    <label>Fare</label>
                    <input name="fare" type="number" step="0.01" value="${esc(t.fare)}">
                </div>
                <div>
                    <label>Origin</label>
                    <input name="origin_station" value="${esc(t.origin_station)}">
                </div>
                <div>
                    <label>Destination</label>
                    <input name="destination_station" value="${esc(t.destination_station)}">
                </div>
                <div>
                    <label>Seats</label>
                    <input name="available_seats" type="number" value="${esc(t.available_seats)}">
                </div>
                <div>
                    <label>Departure</label>
                    <input name="departure_time" value="${esc(t.departure_time)}">
                </div>
                <div>
                    <label>Arrival</label>
                    <input name="arrival_time" value="${esc(t.arrival_time)}">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option ${t.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option ${t.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
                <button class="btn btn-blue">Update</button>
                <button type="button" class="btn btn-red" onclick="deleteTrain(${esc(t.id)})">Delete</button>
            </form>
        </div>
    `).join('');
}

async function updateTrain(event, id){
    event.preventDefault();

    const data = Object.fromEntries(new FormData(event.target).entries());
    data.action = 'update_train';
    data.id = id;

    const result = await apiPost(data);

    showMessage(result.message, result.success ? 'notice' : 'error');

    if(result.success){
        loadAll();
    }
}

async function deleteTrain(id){
    if(!confirm('Delete this train record?')) return;

    const result = await apiPost({
        action:'delete_train',
        id:id
    });

    showMessage(result.message, result.success ? 'notice' : 'error');

    if(result.success){
        loadAll();
    }
}

function renderPassengers(rows){
    const table = document.getElementById('passengerTable');

    table.innerHTML = `
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Created</th></tr>
        ${rows.map(p => `
            <tr>
                <td>#${esc(p.id)}</td>
                <td>${esc(p.name)}</td>
                <td>${esc(p.email)}</td>
                <td>${esc(p.contact)}</td>
                <td>${esc(p.created_at)}</td>
            </tr>
        `).join('')}
    `;
}

function renderTickets(rows){
    const table = document.getElementById('ticketTable');

    table.innerHTML = `
        <tr>
            <th>ID</th>
            <th>Passenger</th>
            <th>Train</th>
            <th>Travel</th>
            <th>Seats</th>
            <th>Total</th>
            <th>Booking</th>
            <th>Payment</th>
            <th>Action</th>
        </tr>
        ${rows.map(t => `
            <tr>
                <td>#${esc(t.id)}</td>
                <td>${esc(t.passenger_name)}<br><small>${esc(t.passenger_email)}</small></td>
                <td>${esc(t.train_code)}<br><small>${esc(t.origin_station)} → ${esc(t.destination_station)}</small></td>
                <td>${esc(t.travel_date)}</td>
                <td>${esc(t.seat_count)}</td>
                <td>₱${esc(t.total_amount)}</td>
                <td><span class="status-badge">${esc(t.booking_status)}</span></td>
                <td><span class="status-badge">${esc(t.payment_status)}</span></td>
                <td>
                    <form onsubmit="updateTicket(event, ${esc(t.id)})">
                        <select name="booking_status">
                            <option ${t.booking_status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option ${t.booking_status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option ${t.booking_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                        <br><br>
                        <select name="payment_status">
                            <option ${t.payment_status === 'Unpaid' ? 'selected' : ''}>Unpaid</option>
                            <option ${t.payment_status === 'Paid' ? 'selected' : ''}>Paid</option>
                        </select>
                        <br><br>
                        <button class="btn btn-green">Update</button>
                    </form>
                </td>
            </tr>
        `).join('')}
    `;
}

async function updateTicket(event, id){
    event.preventDefault();

    const data = Object.fromEntries(new FormData(event.target).entries());
    data.action = 'update_ticket_status';
    data.id = id;

    const result = await apiPost(data);

    showMessage(result.message, result.success ? 'notice' : 'error');

    if(result.success){
        loadAll();
    }
}

function logout(){
    localStorage.removeItem('railway_admin');
    currentAdmin = null;
    renderLogin();
}

if(currentAdmin){
    renderDashboard();
}else{
    renderLogin();
}
</script>
</body>
</html>
