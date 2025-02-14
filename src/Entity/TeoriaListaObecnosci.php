<?php

namespace App\Entity;

use App\Repository\TeoriaListaObecnosciRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeoriaListaObecnosciRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER', fields: ['teoria', 'praktykant'])]
class TeoriaListaObecnosci
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listaObecnosci')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Teoria $teoria = null;

    #[ORM\ManyToOne(inversedBy: 'teoriaListaObecnoscis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $praktykant = null;

    /**
     * @var Collection<int, Kurs>
     */
    #[ORM\ManyToMany(targetEntity: Kurs::class, inversedBy: 'teoriaListaObecnoscis', fetch: "EAGER")]
    private Collection $kurs;

    public function __construct()
    {
        $this->kurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeoria(): ?Teoria
    {
        return $this->teoria;
    }

    public function setTeoria(?Teoria $teoria): static
    {
        $this->teoria = $teoria;

        return $this;
    }

    public function getPraktykant(): ?User
    {
        return $this->praktykant;
    }

    public function setPraktykant(?User $praktykant): static
    {
        $this->praktykant = $praktykant;

        return $this;
    }

    /**
     * @return Collection<int, Kurs>
     */
    public function getKurs(): Collection
    {
        return $this->kurs;
    }

    public function addKur(Kurs $kur): static
    {
        if (!$this->kurs->contains($kur)) {
            $this->kurs->add($kur);
        }

        return $this;
    }

    public function removeKur(Kurs $kur): static
    {
        $this->kurs->removeElement($kur);

        return $this;
    }
}
