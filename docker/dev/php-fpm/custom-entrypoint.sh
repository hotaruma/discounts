#!/bin/sh

set -e

# Check if incoming command contains flags.
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

# Replace environment variables if `ENV_SUBSTITUTION_ENABLE=true`
# shellcheck disable=SC2039
if [[ -n "$ENV_SUBSTITUTION_ENABLE" ]] && [[ "$ENV_SUBSTITUTION_ENABLE" = "true" ]]; then
    /envsubst.sh
fi

if [ "$(id -u)" = "0" ]; then
    echo "Fixing log directory permissions";
    # shellcheck disable=SC2154
    chown -R "$e_user":"$e_user" /var/log/php-fpm;
    chmod -R 755 /var/log/php-fpm;

    echo "Fixing sessions directory permissions";
    chown -R "$e_user":"$e_user" /usr/local/sessions;
    chmod -R 777 /usr/local/sessions;

    echo "Fix access rights to stdout and stderr";
    chown "$e_user" /proc/self/fd/1;
    chown "$e_user" /proc/self/fd/2;

    su-exec "$e_user" "$@";
fi

exec "$@";
