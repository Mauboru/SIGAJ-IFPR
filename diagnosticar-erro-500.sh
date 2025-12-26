#!/bin/bash

echo "=========================================="
echo "Diagnostico de Erro 500"
echo "=========================================="
echo ""

cd /home/tecnomaub-sigaj-ifpr/htdocs/sigaj-ifpr.tecnomaub.site

echo "1. Verificando logs do Laravel..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "✓ Log encontrado. Ultimas 50 linhas:"
    echo ""
    tail -n 50 storage/logs/laravel.log
else
    echo "✗ Log nao encontrado em storage/logs/laravel.log"
fi

echo ""
echo "2. Verificando permissoes de storage..."
ls -ld storage
ls -ld storage/logs
ls -ld storage/framework
ls -ld bootstrap/cache

echo ""
echo "3. Verificando se .env existe..."
if [ -f ".env" ]; then
    echo "✓ .env existe"
    echo "   APP_ENV: $(grep APP_ENV .env | head -1)"
    echo "   APP_DEBUG: $(grep APP_DEBUG .env | head -1)"
else
    echo "✗ ERRO: .env nao existe!"
fi

echo ""
echo "4. Verificando se APP_KEY esta configurado..."
if grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "✓ APP_KEY configurado"
else
    echo "✗ ERRO: APP_KEY nao configurado!"
    echo "   Execute: php artisan key:generate"
fi

echo ""
echo "5. Verificando permissoes de arquivos..."
php artisan --version 2>&1

echo ""
echo "6. Testando sintaxe PHP..."
php -l app/Http/Kernel.php 2>&1

echo ""
echo "=========================================="
echo "Diagnostico concluido!"
echo "=========================================="

