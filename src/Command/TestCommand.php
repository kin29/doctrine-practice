<?php

namespace App\Command;

use App\Entity\DeliciousPizza;
use App\Entity\Tomato;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

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

        $pizza->getTomatoes()->clear();
        $this->em->persist($pizza);
        $this->em->flush();

        return Command::SUCCESS;
    }
}


