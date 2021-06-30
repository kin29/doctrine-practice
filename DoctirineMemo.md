# cascade={"remove"}、 onDelete="CASCADE"、 orphanRemoval=trueの違いメモ

- どれも使わない時...(1)
- `cascade={"remove"}`...(2)
- `onDelete="CASCADE`...(3)
- `orphanRemoval=true`...(4)

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

## どれも使わない時...(1)
外部キー制約のために、DELETE FROM delicious_pizza...ができない
```shell
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
