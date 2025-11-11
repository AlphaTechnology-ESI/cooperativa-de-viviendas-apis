#!/bin/bash

# Cargar variables desde .env
if [ -f .env ]; then
    while IFS='=' read -r key value; do
        key=$(echo "$key" | xargs)
        value=$(echo "$value" | sed 's/^ *"//;s/" *$//;s/^ *//;s/ *$//')
        if [[ ! -z "$key" && ! "$key" =~ ^# ]]; then
            export "$key"="$value"
        fi
    done < .env
else
    echo ".env no encontrado. Abortando."
    exit 1
fi

# Validar variables requeridas
if [ -z "$db" ] || [ -z "$user" ] || [ -z "$pass" ] || [ -z "$host" ] || [ -z "$port" ]; then
    echo "Faltan variables requeridas en .env (db, user, pass, host, port). Abortando."
    exit 1
fi

# Configuración
fecha=$(date +%Y-%m-%d_%H-%M)
destino="./backups"

# Comprobar permisos de directorio destino
echo "Verificando o creando el directorio de backups: $destino"
if [ ! -d "$destino" ]; then
    mkdir -p "$destino" 2>/dev/null
    if [ $? -ne 0 ]; then
        echo "Intentando crear el directorio $destino con sudo..."
        sudo mkdir -p "$destino"
        sudo chown "$USER":"$USER" "$destino"
        if [ $? -ne 0 ]; then
            echo "No se pudo crear el directorio $destino con sudo. Abortando."
            exit 1
        fi
    fi
fi

# Crear backup
mysqldump --single-transaction --set-gtid-purged=OFF -u "$user" -p"$pass" "$db" -h "$host" -P "$port" > "$destino/backup_$fecha.sql"

# Mantener solo las 2 últimas copias
archivos=($(ls -1tr "$destino"/backup_*.sql 2>/dev/null))
num_archivos=${#archivos[@]}
if [ $num_archivos -gt 2 ]; then
    eliminar=$((num_archivos - 2))
    for ((i=0; i<eliminar; i++)); do
        rm -f "${archivos[i]}"
    done
fi
