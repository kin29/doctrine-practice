# cascade={"remove"}、 onDelete="CASCADE"、 orphanRemoval=trueの違いメモ

## reference
[https://tech.quartetcom.co.jp/2016/12/22/doctrine-cascade-remove](https://tech.quartetcom.co.jp/2016/12/22/doctrine-cascade-remove)

## 目次
- [どれも使わない時...(1)](#どれも使わない時1)
- [cascade={"remove"}...(2)](#cascaderemove2)
- [onDelete="CASCADE...(3)](#ondeletecascade3)
- [orphanRemoval=true...(4)](#orphanremovaltrue4)
- [cascade={"remove"}とorphanRemoval=trueの違い](#wipcascaderemoveとorphanremovaltrueの違い)

の4パターンで、以下Commandを実行
```shell
// src/Command/TestCommand.php
...
    protected function execute(InputInterface$input, OutputInterface $output): int
    {
        $pizza = new DeliciousPizza();
        $tomato = new Tomato();
        $pizza->addTomato($tomato);
        $tomato->setPizza($pizza);

        $this->em->persist($tomato);
        $this->em->persist($pizza);
        $this->em->flush();

        // データ削除
        $this->em->remove($pizza);
        $this->em->flush();

        return Command::SUCCESS;
    }
 ...
```

### どれも使わない時...(1)
外部キー制約のために、DELETE FROM delicious_pizza...ができない
```shell
// bin/console app:test実行後
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":2} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM delicious_pizza WHERE id = ? [2] []
doctrine.DEBUG: "ROLLBACK" [] []
console.CRITICAL: Error thrown while running command "app:test". Message: "An exception occurred while executing 'DELETE FROM delicious_pizza WHERE id = ?' with params [2]:  SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`doctrine_practice`.`tomato`, CONSTRAINT `FK_2C14401AD41D1D42` FOREIGN KEY (`pizza_id`) REFERENCES `delicious_pizza` (`id`))" {"exception":"[object] (Doctrine\\DBAL\\Exception\\ForeignKeyConstraintViolationException(code: 0): An exception occurred while executing 'DELETE FROM delicious_pizza WHERE id = ?' with params [2]:\n\nSQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`doctrine_practice`.`tomato`, CONSTRAINT `FK_2C14401AD41D1D42` FOREIGN KEY (`pizza_id`) REFERENCES `delicious_pizza` (`id`)) at /Users/shigaayano/doctrine-practice/vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/AbstractMySQLDriver.php:68)\n[previous exception] [object] (Doctrine\\DBAL\\Driver\\PDO\\Exception(code: 23000): SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`doctrine_practice`.`tomato`, CONSTRAINT `FK_2C14401AD41D1D42` FOREIGN KEY (`pizza_id`) REFERENCES `delicious_pizza` (`id`)) at /Users/shigaayano/doctrine-practice/vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDO/Exception.php:18)\n[previous exception] [object] (PDOException(code: 23000): SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`doctrine_practice`.`tomato`, CONSTRAINT `FK_2C14401AD41D1D42` FOREIGN KEY (`pizza_id`) REFERENCES `delicious_pizza` (`id`)) at /Users/shigaayano/doctrine-practice/vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOStatement.php:112)","command":"app:test","message":"An exception occurred while executing 'DELETE FROM delicious_pizza WHERE id = ?' with params [2]:\n\nSQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`doctrine_practice`.`tomato`, CONSTRAINT `FK_2C14401AD41D1D42` FOREIGN KEY (`pizza_id`) REFERENCES `delicious_pizza` (`id`))"} []
console.DEBUG: Command "app:test" exited with code "1" {"command":"app:test","code":1} []
```

### cascade={"remove"}...(2)
親Entityの子Entityにあたるプロパティに`cascade={"remove"}`を付与
```php
class DeliciousPizza
{
    ...
    /**
     * @ORM\OneToMany(targetEntity=Tomato::class, mappedBy="pizza", cascade={"remove"})
     */
    private Collection $tomatoes;
```


親Entity(delicious_pizza)をremove(`$em->remove($pizza)`)したとき、子Entity(tomato)と親Entityの両テーブルをDELETEしてくれる。
```shell
// bin/console app:test実行後
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":3} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM tomato WHERE id = ? [3] []
doctrine.DEBUG: DELETE FROM delicious_pizza WHERE id = ? [3] []
doctrine.DEBUG: "COMMIT" [] []
```

子Entity(tomato)をremove(`$em->remove($tomato)`)したときは、子Entity(tomato)のみDELETEしてくれる。
```shell
// bin/console app:test実行後
// dev.log
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":4} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM tomato WHERE id = ? [4] []
doctrine.DEBUG: "COMMIT" [] []
```

### onDelete="CASCADE...(3)
**migrationの変更があるので`bin/console d:m:diff & bin/console d:m:m`する必要がある**

JOINCOLUMする子Entityの親Entityのプロパティに`onDelete="CASCADE"`を付与
```php
/**
 * @ORM\Entity(repositoryClass=TomatoRepository::class)
 */
class Tomato
{
    ...
    /**
     * @ORM\ManyToOne(targetEntity=DeliciousPizza::class, inversedBy="tomatoes")
     * @ORM\JoinColumn(name="pizza_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?DeliciousPizza $pizza;
```

SQL(作成されたmigrations)をみると外部キー制約に`ON DELETE CASCADE`が追加されたことがわかる
```diff
- ALTER TABLE tomato ADD CONSTRAINT FK_2C14401AD41D1D42 FOREIGN KEY (pizza_id) REFERENCES delicious_pizza (id)
+ ALTER TABLE tomato ADD CONSTRAINT FK_2C14401AD41D1D42 FOREIGN KEY (pizza_id) REFERENCES delicious_pizza (id) ON DELETE CASCADE
```

SQL的にはDELETE FROM delicious_pizza...しかしてないが、tomatoテーブルからもpizza_id=6は削除されていた。
```shell
// bin/console d:m:diff & bin/console d:m:m実行後に
// bin/console app:test実行
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":6} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM delicious_pizza WHERE id = ? [6] []
doctrine.DEBUG: "COMMIT" [] []
```

### orphanRemoval=true...(4)
親Entityの子Entityにあたるプロパティに`orphanRemoval=true`を付与
```php
class DeliciousPizza
{
    ...
    /**
     * @ORM\OneToMany(targetEntity=Tomato::class, mappedBy="pizza", orphanRemoval=true)
     */
    private Collection $tomatoes;
```

親Entity(delicious_pizza)をremove(`$em->remove($pizza)`)したとき、子Entity(tomato)と親Entityの両テーブルをDELETEしてくれる。  
→ cascade={"remove"}...(2)と同じ結果
```shell
// bin/console app:test実行
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":8} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM tomato WHERE id = ? [8] []
doctrine.DEBUG: DELETE FROM delicious_pizza WHERE id = ? [8] []
doctrine.DEBUG: "COMMIT" [] []
```

子Entity(tomato)をremove(`$em->remove($tomato)`)したときは、子Entity(tomato)のみDELETEしてくれる。  
→ cascade={"remove"}...(2)と同じ結果
```shell
// bin/console app:test実行
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (pizza_id) VALUES (?) {"1":9} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM tomato WHERE id = ? [9] []
doctrine.DEBUG: "COMMIT" [] []
```

### cascade={"remove"}とorphanRemoval=trueの違い
以下Command内容に変更実行すると違いがわかる
```php
class TestCommand extends Command
{
   ...
    protected function execute(InputInterface$input, OutputInterface $output): int
    {
        $pizza = new DeliciousPizza();
        $tomato = new Tomato();
        $tomato->setName('プチトマト');
        $tomatoCollection = new ArrayCollection([$tomato]);
        $pizza->setTomatoes($tomatoCollection);
        $tomato->setPizza($pizza);

        $this->em->persist($tomato);
        $this->em->persist($pizza);
        $this->em->flush();

        $tomato2 = new Tomato();
        $tomato2->setName('フルーツトマト');
        $tomatoCollection2 = new ArrayCollection([$tomato]);
        $pizza->setTomatoes($tomatoCollection2);
        $tomato2->setPizza($pizza);

        $this->em->persist($tomato2);
        $this->em->persist($pizza);

        // データ削除
        //$this->em->remove($pizza);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
```

#### cascade={"remove"}
setし直す前のtomato(プチトマト)はDELETEされない
```shell
// bin/console app:test実行後
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (name, pizza_id) VALUES (?, ?) {"1":"プチトマト","2":30} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO tomato (name, pizza_id) VALUES (?, ?) {"1":"フルーツトマト","2":30} []
doctrine.DEBUG: "COMMIT" [] []
```
```shell
mysql> select * from tomato;
+----+----------+-----------------------+
| id | pizza_id | name                  |
+----+----------+-----------------------+
...
| 44 |       30 | プチトマト              |
| 45 |       30 | フルーツトマト           |
+----+----------+-----------------------+
```

#### orphanRemoval=true
setし直す前のtomato(プチトマト)はDELETEされる
```shell
// bin/console app:test実行後
// dev.log
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: INSERT INTO delicious_pizza (id) VALUES (null) [] []
doctrine.DEBUG: INSERT INTO tomato (name, pizza_id) VALUES (?, ?) {"1":"プチトマト","2":29} []
doctrine.DEBUG: "COMMIT" [] []
doctrine.DEBUG: "START TRANSACTION" [] []
doctrine.DEBUG: DELETE FROM tomato WHERE pizza_id = ? [29] []
doctrine.DEBUG: INSERT INTO tomato (name, pizza_id) VALUES (?, ?) {"1":"フルーツトマト","2":29} []
doctrine.DEBUG: "COMMIT" [] []
```
```shell
mysql> select * from tomato;
+----+----------+-----------------------+
| id | pizza_id | name                  |
+----+----------+-----------------------+
...
| 43 |       29 | フルーツトマト           |
+----+----------+-----------------------+
```
