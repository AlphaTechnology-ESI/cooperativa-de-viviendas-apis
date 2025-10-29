#!/bin/bash

echo "=== VERIFICAR ESTRUCTURA DEL CONTENEDOR ==="

CONTAINER_ID=$(docker ps -q | head -1)
echo "Container ID: $CONTAINER_ID"

echo -e "\n1. Estructura en /var/www/html:"
docker exec $CONTAINER_ID find /var/www/html -type f -name "*.php" | head -20

echo -e "\n2. Buscar diagnostic.php:"
docker exec $CONTAINER_ID find /var/www -name "diagnostic.php" 2>/dev/null

echo -e "\n3. Contenido del directorio raíz del proyecto:"
docker exec $CONTAINER_ID ls -la /var/www/html/

echo -e "\n4. Verificar si existe endpoint:"
docker exec $CONTAINER_ID ls -la /var/www/html/endpoint/ 2>/dev/null || echo "Directorio endpoint no existe"

echo -e "\n5. Test directo de diagnostic.php:"
docker exec $CONTAINER_ID php /var/www/html/endpoint/diagnostic.php 2>/dev/null || echo "Archivo no encontrado o error"

echo -e "\n=== FIN VERIFICACIÓN ==="
