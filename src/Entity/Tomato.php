<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TomatoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TomatoRepository::class)]
#[ORM\Table]
class Tomato
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: DeliciousPizza::class, inversedBy: 'tomatoes')]
    #[ORM\JoinColumn(name: 'pizza_id', referencedColumnName: 'id')]
    private ?DeliciousPizza $pizza;

    #[ORM\Column(type: 'string', length: 225)]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPizza(): ?DeliciousPizza
    {
        return $this->pizza;
    }

    public function setPizza(?DeliciousPizza $pizza): Tomato
    {
        $this->pizza = $pizza;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Tomato
    {
        $this->name = $name;

        return $this;
    }
}
