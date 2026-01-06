#!/bin/bash

# Start script for Railway deployment
# This script starts the PHP built-in server for the CBT_LPK application

set -e

echo "Starting CBT_LPK Application..."

# Get the port from environment variable (Railway provides PORT)
PORT=${PORT:-8000}
HOST=${HOST:-0.0.0.0}

# Install PHP dependencies if composer.json exists
if [ -f "composer.json" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Run PHP built-in server
echo "Starting PHP server on $HOST:$PORT"
php -S $HOST:$PORT
