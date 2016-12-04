# sql2mongo

[![Build Status](https://travis-ci.org/SerhiiTsybulskyi/sql2mongo.svg?branch=master)](https://travis-ci.org/SerhiiTsybulskyi/sql2mongo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SerhiiTsybulskyi/sql2mongo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SerhiiTsybulskyi/sql2mongo/?branch=master)
## Requirements

+ PHP 5.6
+ MongoDB 3+
+ Unix OS

## Installation

```bash
$ pecl install mongodb
$ echo "extension=mongodb.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
$ composer self-update
$ composer install

```

For import test collections to Mongodb you can go to the root project folder and run following command

```bash
$ mongoimport --db test --collection restaurants --drop --file dump/dataset.json
```

You can run app by following steps:
+ Go to project **root** folder
+ Run php script **php src/sql2mongo.php**


## Dependencies
+ [league/climate](https://github.com/thephpleague/climate)
+ [greenlion/php-sql-parser](https://github.com/greenlion/php-sql-parser)
+ [phpmyadmin/sql-parser](https://github.com/phpmyadmin/sql-parser)
+ [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit)
