#!/bin/bash

echo "=== Configurando MySQL para conexiones remotas ==="

# Backup del archivo de configuración
echo "Creando backup de mysqld.cnf..."
sudo cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup

# Cambiar bind-address
echo "Modificando bind-address..."
sudo sed -i 's/bind-address.*=.*127\.0\.0\.1/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf

# Configurar permisos de usuario
echo "Configurando permisos de MySQL..."
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '';"
sudo mysql -u root -e "FLUSH PRIVILEGES;"

# Reiniciar MySQL
echo "Reiniciando MySQL..."
sudo systemctl restart mysql

# Verificar configuración
echo "Verificando configuración..."
echo "Puerto 3306 está escuchando en:"
sudo netstat -tulnp | grep :3306