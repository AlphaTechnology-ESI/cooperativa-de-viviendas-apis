#!/bin/bash

echo "=== DIAGNÓSTICO DOCKER Y CONECTIVIDAD ==="

echo "1. Estado de Docker:"
sudo docker ps -a

echo -e "\n2. Procesos escuchando en puerto 8080:"
sudo ss -tulnp | grep :8080

echo -e "\n3. Estado de MySQL:"
sudo systemctl status mysql --no-pager -l

echo -e "\n4. MySQL bind-address configurado:"
grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf

echo -e "\n5. Firewall status:"
sudo ufw status

echo -e "\n6. Conectividad local (desde la VM):"
curl -I http://localhost:8080/endpoint/diagnostic.php

echo -e "\n7. Conectividad externa (desde la VM a sí misma):"
curl -I http://192.168.1.48:8080/endpoint/diagnostic.php

echo -e "\n8. Verificar si el contenedor puede conectarse a MySQL:"
if [ "$(docker ps -q)" ]; then
    CONTAINER_ID=$(docker ps -q | head -1)
    echo "Testing MySQL connection from container $CONTAINER_ID:"
    docker exec $CONTAINER_ID php -r "
    \$conn = new mysqli('192.168.1.48', 'root', '', '', 3306);
    if (\$conn->connect_error) {
        echo 'Connection failed: ' . \$conn->connect_error . PHP_EOL;
    } else {
        echo 'MySQL connection successful!' . PHP_EOL;
        \$conn->close();
    }
    "
else
    echo "No containers running"
fi

echo -e "\n=== FIN DIAGNÓSTICO ==="
