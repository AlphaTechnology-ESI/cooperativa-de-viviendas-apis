#!/bin/bash

echo "=== PRUEBA DIRECTA DE LA URL ==="

URL="http://192.168.1.48:8080/cooperativa-de-viviendas-apis/endpoint/diagnostic.php"
echo "Probando URL: $URL"

echo -e "\n1. Desde localhost dentro de la VM:"
curl -v "http://localhost:8080/cooperativa-de-viviendas-apis/endpoint/diagnostic.php"

echo -e "\n\n2. Desde IP interna dentro de la VM:"
curl -v "$URL"

echo -e "\n\n3. Verificar estructura exacta en contenedor:"
CONTAINER_ID=$(docker ps -q | head -1)
docker exec $CONTAINER_ID ls -la /var/www/cooperativa-de-viviendas-apis/endpoint/

echo -e "\n4. Ejecutar PHP directamente:"
docker exec $CONTAINER_ID php /var/www/cooperativa-de-viviendas-apis/endpoint/diagnostic.php

echo -e "\n5. Verificar configuración Apache (DocumentRoot):"
docker exec $CONTAINER_ID cat /etc/apache2/sites-available/000-default.conf | grep DocumentRoot

echo -e "\n=== FIN PRUEBA ==="
