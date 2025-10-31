#!/bin/bash

# Cargar variables
if [ -f .env ]; then
    source .env
else
    echo ".env no encontrado. Abortando."
    exit 1
fi

# Configuración
fecha=$(date +%Y-%m-%d_%H-%M)
destino="./backups"
mkdir -p "$destino"

# Crear backup
mysqldump -u "$user" -p"$pass" "$db" -h "$host" -P "$port" > "$destino/backup_$fecha.sql"

# Mantener solo las 2 últimas copias
archivos=($(ls -1tr "$destino"/backup_*.sql))
num_archivos=${#archivos[@]}
if [ $num_archivos -gt 2 ]; then
    eliminar=$((num_archivos - 2))
    for ((i=0; i<eliminar; i++)); do
        rm -f "${archivos[i]}"
    done
fi
