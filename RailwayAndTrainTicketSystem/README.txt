RAILWAY AND TRAIN TICKET SYSTEM
================================

HOW TO RUN (UPDATED - No manual CMD needed)
--------------------------------------------

OPTION A: Web Admin Panel
  Double-click:  START WEB.bat
  - Auto-detects PHP (XAMPP or global)
  - Starts server automatically
  - Opens browser to admin panel
  - Keep the window open while using the web app
  - Close the window to stop the server

  Admin login:
    Email   : admin@railway.test
    Password: admin123

OPTION B: C# Passenger App
  Open:  CSharp\TrainTicketBookingApp.sln
  - Press F5 (or Build > Rebuild, then F5)
  - App AUTO-STARTS the PHP server on launch
  - App AUTO-STOPS the server on close
  - No CMD window needed!

  Passenger login:
    Email   : passenger@railway.test
    Password: passenger123

NOTE: If you run BOTH at the same time, open the web FIRST
(via START WEB.bat), then open C# — they share the same server.

--------------------------------------------

FOLDER STRUCTURE

RailwayAndTrainTicketSystem
 |___ Api/          api.php         (shared REST API)
 |___ Php/          index.php       (admin web panel)
 |___ CSharp/       TrainTicketBookingApp.sln
 |___ Database/     railway.sqlite  (auto-created)
 |___ START WEB.bat                 (double-click launcher)

--------------------------------------------

REQUIREMENTS
  - PHP installed (XAMPP at C:\xampp OR php.exe in PATH)
  - .NET Framework 4.8 (for C# app)
  - Windows (WinForms app)

