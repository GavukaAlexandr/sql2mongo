# sql2mongo

<p>
    Execute SQL queries [SELECT] on MongoDB 
</p>

## Code status
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

##Using
You can run app by following steps:
+ Go to project **root** folder
+ Run php script **php src/sql2mongo.php**
<img src="https://dl.dropboxusercontent.com/1/view/jw59ou94h2xufwe/Apps/Shutter/Selection_001.png />
+ Enter command **help**
+ You will see list of available commands
+ How you can use these commands you can see on the screenshot
<img src="https://dl.dropboxusercontent.com/1/view/skj8gpilr8y1ohw/Apps/Shutter/Selection_002.png />
+ How execute SQL query  you can see on the screenshots
<img src="https://dl.dropboxusercontent.com/1/view/uurglg2hrjdld9g/Apps/Shutter/Selection_003.png />
<img src="https://dl.dropboxusercontent.com/1/view/d3cxpeewquzqpvj/Apps/Shutter/Selection_004.png />

## Dependencies
+ [league/climate](https://github.com/thephpleague/climate)
+ [greenlion/php-sql-parser](https://github.com/greenlion/php-sql-parser)
+ [phpmyadmin/sql-parser](https://github.com/phpmyadmin/sql-parser)
+ [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit)


`PS. Sorry for my English`