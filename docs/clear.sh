#!/bin/sh

composer dump-autoload;
php artisan cache:clear;
php artisan config:clear;
php artisan view:clear;

heroku run composer dump-autoload;
heroku run heroku run php artisan cache:clear;
heroku run php artisan config:clear;
heroku run php artisan view:clear;

git rm -r --cached .
$ git rm -r --cached