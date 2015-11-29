@echo off
php %~dp0..\build.php %1 %2
if %errorlevel% neq 0 exit /b %errorlevel%