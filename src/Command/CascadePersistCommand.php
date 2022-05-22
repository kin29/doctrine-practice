<?php

namespace App\Command;

use App\Entity\Comment;
use App\Entity\DeliciousPizza;
use App\Entity\Tomato;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cascade-persist',
    description: 'cascade persist test command',
)]
class CascadePersistCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface$input, OutputInterface $output): int
    {
        $user = new User();
        $comment = new Comment();
        $comment->setContent('コメント');
        $user->addComment($comment);

        $this->em->persist($user);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
