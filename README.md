# doctrine-practice

## usage

### preparate
```shell
$ composer install
```

```shell
$ cp .env .env.local
```
```diff
// .env.local
+ DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
```
```shell
$ bin/console doctrine:database:create
$ bin/console d:m:m
```
### test
```shell
$ bin/console app:test
```
