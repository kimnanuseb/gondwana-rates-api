#!/usr/bin/env bash
# start.sh - start single PHP server on 8080
cd "$(dirname "$0")/frontend" || exit 1
php -S 0.0.0.0:8080
