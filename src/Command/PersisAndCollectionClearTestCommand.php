<?php

namespace App\Command;

use App\Entity\DeliciousPizza;
use App\Entity\Tomato;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PersisAndCollectionClearTestCommand extends Command
{
    protected static $defaultName = 'app:remove:test';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface$input, OutputInterface $output): int
    {
        $tomato = new Tomato();
        $tomato->setName('フルーツ');

        $pizza = new DeliciousPizza();
        $pizza->setTomatoes(new ArrayCollection([$tomato]));

        $tomato->setPizza($pizza);

        $this->em->persist($pizza);
        $this->em->flush();
        //INSERT INTO delicious_pizza (id) VALUES (null) (parameters: array[], types: array[]) {"sql":"INSERT INTO delicious_pizza (id) VALUES (null)","params":[],"types":[]} []
        //INSERT INTO tomato (name, pizza_id) VALUES (?, ?) (parameters: array{"1":"プチトマト","2":8}, types: array{"1":2,"2":1}) {"sql":"INSERT INTO tomato (name, pizza_id) VALUES (?, ?)","params":{"1":"プチトマト","2":8},"types":{"1":2,"2":1}} []


        //ここまではpersistTestと同じ

        $pizza->getTomatoes()->clear();
        $this->em->persist($pizza); //フルーツトマトは削除される、ピザは残る
        $this->em->flush();
        //DELETE FROM tomato WHERE id = ? (parameters: array{"1":8}, types: array{"1":1}) {"sql":"DELETE FROM tomato WHERE id = ?","params":{"1":8},"types":{"1":1}} []

        return Command::SUCCESS;
    }
}


