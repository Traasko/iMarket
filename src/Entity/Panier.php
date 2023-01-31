<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $achat = null;

    #[ORM\Column]
    private ?bool $etat = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: ContenuPanier::class, orphanRemoval: true)]
    private Collection $contenuPaniers;

    public function __construct()
    {
        $this->etat = 0;
        $this->achat = new \DateTime();
        $this->contenuPaniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAchat(): ?\DateTimeInterface
    {
        return $this->achat;
    }

    public function setAchat(\DateTimeInterface $achat): self
    {
        $this->achat = $achat;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, ContenuPanier>
     */
    public function getContenuPaniers(): Collection
    {
        return $this->contenuPaniers;
    }

    public function addContenuPanier(ContenuPanier $contenuPanier): self
    {
        if (!$this->contenuPaniers->contains($contenuPanier)) {
            $this->contenuPaniers->add($contenuPanier);
            $contenuPanier->setPanier($this);
        }

        return $this;
    }

    public function removeContenuPanier(ContenuPanier $contenuPanier): self
    {
        if ($this->contenuPaniers->removeElement($contenuPanier)) {
            // set the owning side to null (unless already changed)
            if ($contenuPanier->getPanier() === $this) {
                $contenuPanier->setPanier(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->user;
    }
}
