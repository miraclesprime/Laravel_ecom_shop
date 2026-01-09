@echo off
cd /d E:\PHP\trial
php artisan queue:work --tries=3

