@echo off

echo ========================================
echo Deploy SIGAJ-IFPR
echo ========================================
echo.

ssh root@212.85.19.3 "cd /home/tecnomaub-sigaj-ifpr/htdocs/sigaj-ifpr.tecnomaub.site && git pull && npm i --force && npm run build && rm -f public/hot && php artisan config:clear && php artisan view:clear && php artisan route:clear"

echo.
echo Deploy concluido!
pause