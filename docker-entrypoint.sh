#!/bin/bash

set -e

# Set Apache port from environment variable
PORT=${PORT:-8080}
echo "Starting application on port $PORT..."

# Configure Apache to listen on PORT
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf

# Check if MySQL needs to be set up
if [ ! -z "$DB_HOST" ] && [ ! -z "$DB_USER" ]; then
    echo "Waiting for database to be ready..."
    until nc -z $DB_HOST ${DB_PORT:-3306} 2>/dev/null; do
        echo "Database is unavailable - sleeping"
        sleep 1
    done
    echo "Database is up - continuing"
fi

# Start Apache
echo "Starting Apache..."
apache2-foreground
