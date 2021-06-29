<?php

namespace App\Entity;

use App\Repository\TomatoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TomatoRepository::class)
 */
class Tomato
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DeliciousPizza::class, inversedBy="tomatoes")
     */
    private $pizza;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPizza(): ?DeliciousPizza
    {
        return $this->pizza;
    }

    public function setPizza(?DeliciousPizza $pizza): self
    {
        $this->pizza = $pizza;

        return $this;
    }
}
