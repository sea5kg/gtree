#!/bin/bash

MAX_RETRIES=10
DELAY_SECONDS=2
ATTEMPT=0

sed -i 's/upload_max_filesize.*/upload_max_filesize = 100M/g' /usr/local/etc/php/php.ini-development
sed -i 's/post_max_size.*/post_max_size = 100M/g' /usr/local/etc/php/php.ini-development

sed -i 's/upload_max_filesize.*/upload_max_filesize = 100M/g' /usr/local/etc/php/php.ini-production
sed -i 's/post_max_size.*/post_max_size = 100M/g' /usr/local/etc/php/php.ini-production

cp -rf /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

while true; do
    # Your command or code block to be retried
    cd /var/www/html/
    php update_db.php

    if [ $? -eq 0 ]; then
        echo "Command succeeded!"
        break # Exit the loop on success
    else
        ATTEMPT=$((ATTEMPT + 1))
        if [ $ATTEMPT -ge $MAX_RETRIES ]; then
            echo "Command failed after $MAX_RETRIES attempts."
            exit 1 # Exit with error after max retries
        fi
        echo "Command failed. Retrying in $DELAY_SECONDS seconds (Attempt $ATTEMPT/$MAX_RETRIES)..."
        sleep "$DELAY_SECONDS"
    fi
done

apache2ctl -D FOREGROUND