<?php

namespace App\Entity;

use App\Repository\TeoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeoriaRepository::class)]
class Teoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column]
    private ?int $czas_trwania = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $temat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $opis = null;

    #[ORM\ManyToOne(inversedBy: 'teoriaInstruktor')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $instruktor = null;

    /**
     * @var Collection<int, TeoriaListaObecnosci>
     */
    #[ORM\OneToMany(targetEntity: TeoriaListaObecnosci::class, mappedBy: 'teoria')]
    private Collection $listaObecnosci;

    public function __construct()
    {
        $this->listaObecnosci = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getCzasTrwania(): ?int
    {
        return $this->czas_trwania;
    }

    public function setCzasTrwania(int $czas_trwania): static
    {
        $this->czas_trwania = $czas_trwania;

        return $this;
    }

    public function getTemat(): ?string
    {
        return $this->temat;
    }

    public function setTemat(?string $temat): static
    {
        $this->temat = $temat;

        return $this;
    }

    public function getOpis(): ?string
    {
        return $this->opis;
    }

    public function setOpis(?string $opis): static
    {
        $this->opis = $opis;

        return $this;
    }

    public function getInstruktor(): ?User
    {
        return $this->instruktor;
    }

    public function setInstruktor(?User $instruktor): static
    {
        $this->instruktor = $instruktor;

        return $this;
    }

    /**
     * @return Collection<int, TeoriaListaObecnosci>
     */
    public function getListaObecnosci(): Collection
    {
        return $this->listaObecnosci;
    }

    public function addListaObecnosci(TeoriaListaObecnosci $listaObecnosci): static
    {
        if (!$this->listaObecnosci->contains($listaObecnosci)) {
            $this->listaObecnosci->add($listaObecnosci);
            $listaObecnosci->setTeoria($this);
        }

        return $this;
    }

    public function removeListaObecnosci(TeoriaListaObecnosci $listaObecnosci): static
    {
        if ($this->listaObecnosci->removeElement($listaObecnosci)) {
            // set the owning side to null (unless already changed)
            if ($listaObecnosci->getTeoria() === $this) {
                $listaObecnosci->setTeoria(null);
            }
        }

        return $this;
    }
}
