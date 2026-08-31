@echo off
setlocal

cd /d "%~dp0\..\.."
if errorlevel 1 exit /b 1

if not exist "release\cpanel" mkdir "release\cpanel"

if exist "release\cpanel\direpair-api.zip" del /q "release\cpanel\direpair-api.zip"
if exist "release\cpanel\direpair-content.zip" del /q "release\cpanel\direpair-content.zip"
if exist "release\cpanel\direpair-frontend.zip" del /q "release\cpanel\direpair-frontend.zip"

echo [1/2] Packaging Laravel API...
tar.exe -a -c -f "release\cpanel\direpair-api.zip" --exclude=database/database.sqlite --exclude=bootstrap/cache/*.php -C "apps\api" .env.cpanel.example .htaccess app artisan bootstrap composer.json composer.lock config database deploy public resources routes
if errorlevel 1 exit /b 1

echo [2/2] Packaging WordPress content plugin...
tar.exe -a -c -f "release\cpanel\direpair-content.zip" -C "apps\wordpress-plugin" direpair-content
if errorlevel 1 exit /b 1

echo.
echo cPanel packages are ready:
echo   release\cpanel\direpair-api.zip
echo   release\cpanel\direpair-content.zip
echo.
echo Astro is deployed by Vercel from Git and is intentionally not packaged here.

endlocal
