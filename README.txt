# Train Ticket Booking System

## Overview
A Windows Forms desktop application built with C# (.NET Framework 4.8).
It serves as the **ticket counter console** that allows staff to book,
confirm, and cancel train tickets. It connects to the Railway Management
System through a PHP REST API to sync train data in real time.

## Features
- Sync train schedules from PHP Railway Management System
- Book train tickets with passenger details
- Select number of seats and travel date
- Set booking status (Pending / Confirmed / Cancelled)
- Set payment status (Unpaid / Paid)
- Confirm and mark tickets as paid
- Cancel existing bookings
- View all train routes in route board
- View all passenger tickets in manifest monitor

## Technologies Used
- C# (.NET Framework 4.8)
- Windows Forms
- REST API (JSON via HttpWebRequest)
- SQLite (managed via PHP API)
- Visual Studio

## How to Run
### Step 1 — Run PHP System First
```bash
cd php-api
php -S localhost:8000
```

### Step 2 — Open C# Project
1. Open Visual Studio
2. Open `csharp\TrainTicketBookingApp.sln`
3. Click **Build > Rebuild Solution**
4. Press **F5** to run



