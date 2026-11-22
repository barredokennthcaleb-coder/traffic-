#!/bin/sh
set -e

# Render mounts secret files at /etc/secrets/<filename>. CodeIgniter reads
# its configuration from .env in the project root, so copy the secret file
# into place before Apache starts. The .env file is never baked into the
# image (it is excluded by .dockerignore).
if [ -f /etc/secrets/.env ]; then
    cp /etc/secrets/.env /var/www/html/.env
    chown root:www-data /var/www/html/.env
    chmod 640 /var/www/html/.env
fi

# Hand off to the official PHP image entrypoint (keeps its php/apache
# command handling intact).
exec docker-php-entrypoint "$@"
