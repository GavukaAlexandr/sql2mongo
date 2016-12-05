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

## Using
You can run app by following steps:
+ Go to project **root** folder
+ Run php script **php src/sql2mongo.php**
+ Enter command **help**
+ You will see list of available commands
![sql2mongo user interface](http://dl1.joxi.net/drive/2016/12/05/0014/1773/980717/17/8a280949d9.png)
+ How you can use these commands you can see on the screenshots
![sql2mongo executing commands](http://dl1.joxi.net/drive/2016/12/05/0014/1773/980717/17/83f687ce9c.png)
+ How execute SQL query  you can see on the screenshots
![sql2mongo executing SQL query](http://dl2.joxi.net/drive/2016/12/05/0014/1773/980717/17/c2129133e7.png)
![sql2mongo executing SQL query](http://dl2.joxi.net/drive/2016/12/05/0014/1773/980717/17/f2efc66dea.png)


## Dependencies
+ [league/climate](https://github.com/thephpleague/climate)
+ [greenlion/php-sql-parser](https://github.com/greenlion/php-sql-parser)
+ [phpmyadmin/sql-parser](https://github.com/phpmyadmin/sql-parser)
+ [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit)


**PS.** _Sorry for my English_
