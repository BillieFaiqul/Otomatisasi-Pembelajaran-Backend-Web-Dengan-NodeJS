#!/bin/sh
service redis-server start
exec php artisan queue:work redis