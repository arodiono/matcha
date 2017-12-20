### Installing

Сhange the database settings in the file `app/Database/config.php`

```
$ npm install
$ gulp build
$ composer install
$ vendor/bin/phinx migrate
$ php -S localhost:8080 -t public
```