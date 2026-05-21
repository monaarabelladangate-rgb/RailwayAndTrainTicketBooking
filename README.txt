# Railway Management System

## Overview
A web-based Railway Management System built with PHP and SQLite.
It serves as the **station operations board** that manages train schedules,
routes, and passenger tickets. It is connected to the Train Ticket Booking
System through a PHP REST API.

## Features
- View all active train schedules
- Add, edit, and delete train records
- View passenger manifest and ticket records
- Manage train routes (origin, destination, departure, arrival)
- Manage seat availability and fare
- REST API endpoint for C# integration (`api.php`)
- SQLite database (no MySQL setup required)

## How to Run
### Option A — Using Git Bash
```bash
cd php-api
php -S localhost:8000
```

### Option B — Using XAMPP PHP
```bash
/c/xampp/php/php.exe -S localhost:8000


Default Admin:
admin@railway.test
admin123

