RAILWAY STARTER SYSTEM WITH COMMIT ROADMAP

This is an early-stage incomplete version of the working Railway system.

Purpose:
Students should build the project step by step and commit each meaningful change.

System Roles:
PHP/Web: Railway Admin Management
C# Windows Forms: Passenger Ticket Booking
Database: SQLite
Connection: PHP API

Already working:
- PHP server runs using php -S localhost:8000
- SQLite database is created automatically
- Admin login/logout works
- API ping works
- Passenger login works
- Active train listing works in C#
- PHP admin can view existing trains and passengers

Still incomplete:
- Passenger registration
- Admin train CRUD
- Passenger ticket booking
- Passenger ticket status listing
- Admin ticket management
- Final validation and testing

Run PHP:
cd php-api
php -S localhost:8000

Open PHP Admin:
http://localhost:8000/index.php

Open C#:
csharp\TrainTicketBookingApp.sln

Default Admin:
admin@railway.test
admin123

Default Passenger:
passenger@railway.test
passenger123

Read the notes folder before making commits.
