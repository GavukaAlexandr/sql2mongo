# sql2mongo

[![Build Status](https://travis-ci.org/SerhiiTsybulskyi/sql2mongo.svg?branch=master)](https://travis-ci.org/SerhiiTsybulskyi/sql2mongo)


## Requirements

+ PHP 5.6
+ MongoDB 3+
+ Unix OS (Linux, OSX)

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
+ Go to project root folder
+ Run php script **php src/sql2mongo.php**
