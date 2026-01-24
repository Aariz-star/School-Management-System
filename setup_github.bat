@echo off
echo ==========================================
echo   GitHub Upload Helper (Smart Version)
echo ==========================================
echo.

:: 1. Check if Git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Git is NOT installed on this computer!
    echo.
    echo You need to install Git to upload to GitHub.
    echo 1. Go to: https://git-scm.com/download/win
    echo 2. Download and install "64-bit Git for Windows Setup"
    echo 3. During install, just keep clicking "Next"
    echo 4. IMPORTANT: Close this window and restart your computer (or just the terminal)
    echo.
    pause
    exit /b
)

:: 2. Configure User (Prevents "who are you" errors)
echo We need to configure your identity for GitHub.
if not exist ".git" (
    set /p git_name="Enter your Name (e.g. Ali Khan): "
    set /p git_email="Enter your Email (e.g. ali@example.com): "
    git config --global user.name "%git_name%"
    git config --global user.email "%git_email%"
)

echo 1. Initializing Git repository...
if not exist ".git" git init

echo.
echo 2. Adding all files...
git add .

echo.
echo 3. Committing files...
git commit -m "Project Update"

echo.
echo 4. Renaming branch to main...
git branch -M main

echo.
echo 5. Linking to GitHub...
git remote get-url origin >nul 2>&1
if %errorlevel% equ 0 (
    git remote set-url origin https://github.com/Aariz-star/School-Management-System
) else (
    git remote add origin https://github.com/Aariz-star/School-Management-System
)

echo.
echo 6. Pushing code...
git push -u origin main

echo.
echo Done! If there were no errors above, your code is now on GitHub.
pause