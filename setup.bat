@echo off
setlocal EnableDelayedExpansion

echo Creating directory structure for Penyewaan Lapangan...

REM --- Root Directories ---
md "admin" 2>nul
md "assets" 2>nul
md "config" 2>nul
md "etc" 2>nul
md "lib" 2>nul
md "uploads" 2>nul
md "views" 2>nul
md "booking" 2>nul
md "lapangan" 2>nul

REM --- Assets Subdirectories ---
md "assets\default" 2>nul
md "assets\default\css" 2>nul
md "assets\default\js" 2>nul
md "assets\default\images" 2>nul

REM --- Views Subdirectories ---
md "views\default" 2>nul

REM --- Etc Subdirectories ---
md "etc" 2>nul

echo Created directories.

echo Creating empty files...

REM --- Root Level Files ---
type nul > ".env"
type nul > "composer.json"
type nul > "index.php"
type nul > "login.php"
type nul > "logout.php"
type nul > ".htaccess"

REM --- Admin Files ---
type nul > "admin\index.php"

REM --- Config Files ---
type nul > "config\database.php"
type nul > "config\menu.json"

REM --- Lib Files ---
type nul > "lib\auth.php"
type nul > "lib\functions.php"

REM --- Views Files ---
type nul > "views\default\admin_content.php"
type nul > "views\default\breadcrumb.php"
type nul > "views\default\footer.php"
type nul > "views\default\header.php"
type nul > "views\default\sidebar.php"
type nul > "views\default\topnav.php"
type nul > "views\default\upper_block.php"
type nul > "views\default\lower_block.php"

REM --- Etc Files ---
type nul > "etc\.env"

REM --- Booking Files ---
type nul > "booking\index.php"
type nul > "booking\add.php"

REM --- Lapangan Files ---
type nul > "lapangan\index.php"
type nul > "lapangan\add.php"

echo.
echo Setup complete! Directory structure and empty files created.
echo You can now populate the files with your code.
pause