<?php

namespace App\Entity;

use App\Repository\DeliciousPizzaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliciousPizzaRepository::class)
 */
class DeliciousPizza
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\OneToMany(targetEntity=Tomato::class, mappedBy="pizza", orphanRemoval=true)
     */
    private Collection $tomatoes;

    public function __construct()
    {
        $this->tomatoes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getTomatoes(): Collection
    {
        return $this->tomatoes;
    }

    public function setTomatoes(Collection $tomatoes): self
    {
        $this->tomatoes = $tomatoes;

        return $this;
    }

    public function addTomato(Tomato $tomato): self
    {
        if (!$this->tomatoes->contains($tomato)) {
            $this->tomatoes[] = $tomato;
            $tomato->setPizza($this);
        }

        return $this;
    }

    public function removeTomato(Tomato $tomato): self
    {
        if ($this->tomatoes->removeElement($tomato)) {
            // set the owning side to null (unless already changed)
            if ($tomato->getPizza() === $this) {
                $tomato->setPizza(null);
            }
        }

        return $this;
    }
}
