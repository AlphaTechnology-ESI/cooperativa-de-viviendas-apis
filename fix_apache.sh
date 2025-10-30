#!/bin/bash

echo "=== SOLUCIONANDO PROBLEMA DE RUTA DE APACHE ==="

CONTAINER_ID=$(docker ps -q | head -1)
echo "Container ID: $CONTAINER_ID"

echo -e "\n1. Creando enlace simbólico en /var/www/html:"
docker exec $CONTAINER_ID ln -sf /var/www/cooperativa-de-viviendas-apis /var/www/html/cooperativa-de-viviendas-apis

echo -e "\n2. Verificando enlace:"
docker exec $CONTAINER_ID ls -la /var/www/html/

echo -e "\n3. Probando URL después del enlace:"
curl -I "http://localhost:8080/cooperativa-de-viviendas-apis/endpoint/diagnostic.php"

echo -e "\n4. Si aún no funciona, verificar DocumentRoot:"
docker exec $CONTAINER_ID find /etc -name "*.conf" -exec grep -l "DocumentRoot" {} \; 2>/dev/null | head -3

echo -e "\n=== FIN SOLUCIÓN ==="
