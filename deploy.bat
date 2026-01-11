@echo off
ssh root@212.85.19.3 "cd /home/tecnomaub-sigaj-ifpr/htdocs/sigaj-ifpr.tecnomaub.site && git pull && npm install --force --ignore-scripts && npm run build"