#!/bin/bash

echo "=========================================="
echo "Corrigindo Erro 500 - Laravel"
echo "=========================================="
echo ""

cd /home/tecnomaub-sigaj-ifpr/htdocs/sigaj-ifpr.tecnomaub.site

echo "1. Verificando e corrigindo permissoes..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "2. Criando diretorios necessarios se nao existirem..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "3. Verificando APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "   Gerando APP_KEY..."
    php artisan key:generate
else
    echo "   APP_KEY ja configurado"
fi

echo "4. Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "5. Verificando autoload..."
composer dump-autoload --optimize

echo "6. Verificando se vendor existe..."
if [ ! -d "vendor" ]; then
    echo "   Instalando dependencias..."
    composer install --no-dev --optimize-autoloader
fi

echo "7. Verificando build..."
if [ ! -f "public/build/manifest.json" ]; then
    echo "   Build nao encontrado! Execute: npm run build"
else
    echo "   Build encontrado"
fi

echo "8. Removendo arquivo hot se existir..."
rm -f public/hot

echo ""
echo "=========================================="
echo "Verificando logs para erros..."
echo "=========================================="
if [ -f "storage/logs/laravel.log" ]; then
    echo "Ultimas 20 linhas do log:"
    tail -n 20 storage/logs/laravel.log
else
    echo "Log ainda nao existe (sera criado no primeiro acesso)"
fi

echo ""
echo "=========================================="
echo "Correcoes aplicadas!"
echo "=========================================="
echo ""
echo "Se ainda houver erro, verifique:"
echo "1. tail -f storage/logs/laravel.log"
echo "2. Verifique se o PHP esta na versao correta: php -v"
echo "3. Verifique se todas as extensoes PHP estao instaladas"

