@echo off
setlocal
echo ==========================================
echo   GitHub Push Helper (Safe Mode)
echo ==========================================
echo.

:: Initialize GIT_CMD variable
set "GIT_CMD=git"

:: 1. Check if 'git' command worksi
%GIT_CMD% --version >nul 2>&1
if %errorlevel% equ 0 goto :FoundGit

:: 2. Check standard install paths
if exist "C:\Program Files\Git\cmd\git.exe" (
    set "GIT_CMD=C:\Program Files\Git\cmd\git.exe"
    goto :FoundGit
)

if exist "%LOCALAPPDATA%\Programs\Git\cmd\git.exe" (
    set "GIT_CMD=%LOCALAPPDATA%\Programs\Git\cmd\git.exe"
    goto :FoundGit
)

if exist "C:\Program Files (x86)\Git\cmd\git.exe" (
    set "GIT_CMD=C:\Program Files (x86)\Git\cmd\git.exe"
    goto :FoundGit
)

:: If we get here, Git is not found
echo [ERROR] Git is not detected on this computer.
echo.
echo Please install Git from: https://git-scm.com/download/win
echo IMPORTANT: After installing, restart your computer.
echo.
pause
exit /b

:FoundGit
echo Git found! Using: "%GIT_CMD%"
echo.

:: 3. Configure User (Linear logic to avoid block crashes)
"%GIT_CMD%" config --global user.name >nul 2>&1
if %errorlevel% equ 0 goto :ConfigDone

echo First time setup: We need your name for GitHub.
set /p "git_name=Enter your Name: "
set /p "git_email=Enter your Email: "
"%GIT_CMD%" config --global user.name "%git_name%"
"%GIT_CMD%" config --global user.email "%git_email%"

:ConfigDone

:: 4. Prepare Files
echo Preparing files...
if not exist ".git" "%GIT_CMD%" init
"%GIT_CMD%" add .
"%GIT_CMD%" commit -m "Project Upload"
"%GIT_CMD%" branch -M main

:: 5. Link Repository
"%GIT_CMD%" remote get-url origin >nul 2>&1
if %errorlevel% equ 0 (
    "%GIT_CMD%" remote set-url origin https://github.com/Aariz-star/School-Management-System
    goto :PushNow
)

echo Linking to repository...
"%GIT_CMD%" remote add origin https://github.com/Aariz-star/School-Management-System

:PushNow
echo.
echo Pushing to GitHub...
echo (Please sign in if a window pops up)
"%GIT_CMD%" push -u origin main

echo.
pause