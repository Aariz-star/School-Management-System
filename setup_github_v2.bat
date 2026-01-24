@echo off
echo ==========================================
echo   GitHub Upload Helper v2 (Debug Mode)
echo ==========================================
echo.

:: 1. Check if Git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Git is NOT installed on this computer!
    echo Please download and install Git from: https://git-scm.com/download/win
    pause
    exit /b
)

:: 2. Configure User (Fixes "Please tell me who you are" error)
echo We need to configure your Git identity for the commit.
set /p git_name="Enter your Name (e.g. John Doe): "
set /p git_email="Enter your Email (e.g. john@example.com): "

git config --global user.name "%git_name%"
git config --global user.email "%git_email%"

echo.
echo 3. Preparing files...
git init
git add .
git commit -m "Initial commit"

echo.
echo 4. Renaming branch...
git branch -M main

echo.
echo ==========================================
echo IMPORTANT: Go to https://github.com/new and create a repository.
echo Copy the HTTPS URL (it looks like https://github.com/username/repo.git)
echo ==========================================
echo.

set /p repo_url="Paste your GitHub Repository URL here: "

echo.
echo 5. Linking remote...
git remote remove origin >nul 2>&1
git remote add origin %repo_url%

echo.
echo 6. Pushing to GitHub...
echo (A login window might pop up. Please sign in!)
echo.
git push -u origin main

echo.
echo ==========================================
echo CHECK THE OUTPUT ABOVE!
echo If you see "Success" or "Branch 'main' set up", it worked.
echo If you see "fatal" or "error", read the message.
echo ==========================================
pause