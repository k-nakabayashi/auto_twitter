#!/bin/sh

php artisan make:job FollowerJob;
php artisan make:provider MyJobProvider;