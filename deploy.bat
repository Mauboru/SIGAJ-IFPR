@echo off
echo ========================================
echo Deploy SIGAJ-IFPR
echo ========================================
echo.

ssh root@212.85.19.3 "cd /home/tecnomaub-sigaj-ifpr/htdocs/sigaj-ifpr.tecnomaub.site && git pull && composer install --no-dev --optimize-autoloader && npm i --force && npm run build && chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache && rm -f public/hot && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php artisan optimize:clear"

echo.
echo Deploy concluido!
echo.
echo Se houver erro 500, execute no servidor:
echo bash fix-500.sh
pause