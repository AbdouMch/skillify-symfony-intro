#!/usr/bin/env bash
# inspired from https://carstenwindler.de/php/enable-xdebug-on-demand-in-your-local-docker-environment/

if [ "$#" -ne 1 ]; then
    SCRIPT_PATH=`basename "$0"`
    echo "Usage: $SCRIPT_PATH enable|disable"
    exit 1;
fi

if [ "$1" == "enable" ] ; then
    if [ -f /usr/local/etc/php/disabled/docker-php-ext-xdebug.ini ] ; then
      mv /usr/local/etc/php/disabled/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/
    else
      echo "Xdebug already enabled"
    fi
else
    if [ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ] ; then
      mkdir -p /usr/local/etc/php/disabled
      mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/disabled/
    else
      echo "Xdebug already disabled"
    fi
fi