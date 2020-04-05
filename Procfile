web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:restart && php artisan queue:work --queue=default,follow,tweet,favorite,unfollow --tries=1 --sleep=10 --timeout=1800