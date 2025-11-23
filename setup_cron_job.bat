@echo off
REM ch System Auto-Cleanup Cron Job Setup
REM This script sets up a scheduled task to run the cleanup script every hour

echo Setting up ch system auto-cleanup cron job...

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges
) else (
    echo Please run this script as administrator to set up the scheduled task
    pause
    exit /b 1
)

REM Get the current directory
set "SCRIPT_DIR=%~dp0"
set "CLEANUP_SCRIPT=%SCRIPT_DIR%cleanup_expired_files.php"
set "PHP_EXE=C:\xampp\php\php.exe"

REM Check if PHP executable exists
if not exist "%PHP_EXE%" (
    echo PHP executable not found at %PHP_EXE%
    echo Please update the PHP_EXE path in this script
    pause
    exit /b 1
)

REM Check if cleanup script exists
if not exist "%CLEANUP_SCRIPT%" (
    echo Cleanup script not found at %CLEANUP_SCRIPT%
    pause
    exit /b 1
)

REM Create the scheduled task
schtasks /create /tn "chSystemCleanup" /tr "\"%PHP_EXE%\" \"%CLEANUP_SCRIPT%\"" /sc hourly /mo 1 /f

if %errorLevel% == 0 (
    echo Scheduled task created successfully!
    echo The cleanup script will run every hour.
    echo You can view/modify this task in Task Scheduler (taskschd.msc)
) else (
    echo Failed to create scheduled task.
    echo Error code: %errorLevel%
)

echo.
echo To test the cleanup script manually, run:
echo "%PHP_EXE%" "%CLEANUP_SCRIPT%"
echo.

pause

