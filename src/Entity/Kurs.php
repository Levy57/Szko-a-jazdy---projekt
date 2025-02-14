<?php

namespace App\Entity;

use App\Enum\Kategoria;
use App\Enum\Status;
use App\Repository\KursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KursRepository::class)]
class Kurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'kurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $praktykant = null;

    #[ORM\Column(enumType: Kategoria::class)]
    private ?Kategoria $kategoria = null;

    #[ORM\Column]
    private ?bool $teoria = null;

    #[ORM\Column]
    private ?int $teoria_godziny = null;

    #[ORM\Column]
    private ?int $praktyka_godziny = null;

    #[ORM\ManyToOne(inversedBy: 'kursInstruktor')]
    private ?User $instruktor = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $start_kurs = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end_kurs = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = Status::Nierozpoczety;

    /**
     * @var Collection<int, KursHarmonogram>
     */
    #[ORM\OneToMany(targetEntity: KursHarmonogram::class, mappedBy: 'kurs')]
    private Collection $harmonogram;

    /**
     * @var Collection<int, TeoriaListaObecnosci>
     */
    #[ORM\ManyToMany(targetEntity: TeoriaListaObecnosci::class, mappedBy: 'kurs', fetch: "EAGER")]
    private Collection $teoriaListaObecnoscis;

    public function __construct()
    {
        $this->harmonogram = new ArrayCollection();
        $this->teoriaListaObecnoscis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getKategoria(): ?Kategoria
    {
        return $this->kategoria;
    }

    public function setKategoria(Kategoria $kategoria): static
    {
        $this->kategoria = $kategoria;

        return $this;
    }

    public function isTeoria(): ?bool
    {
        return $this->teoria;
    }

    public function setTeoria(bool $teoria): static
    {
        $this->teoria = $teoria;

        return $this;
    }

    public function getTeoriaGodziny(): ?int
    {
        return $this->teoria_godziny;
    }

    public function setTeoriaGodziny(int $teoria_godziny): static
    {
        $this->teoria_godziny = $teoria_godziny;

        return $this;
    }

    public function getPraktykaGodziny(): ?int
    {
        return $this->praktyka_godziny;
    }

    public function setPraktykaGodziny(int $praktyka_godziny): static
    {
        $this->praktyka_godziny = $praktyka_godziny;

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

    public function getStartKurs(): ?\DateTimeInterface
    {
        return $this->start_kurs;
    }

    public function setStartKurs(?\DateTimeInterface $start_kurs): static
    {
        $this->start_kurs = $start_kurs;

        return $this;
    }

    public function getEndKurs(): ?\DateTimeInterface
    {
        return $this->end_kurs;
    }

    public function setEndKurs(?\DateTimeInterface $end_kurs): static
    {
        $this->end_kurs = $end_kurs;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, KursHarmonogram>
     */
    public function getHarmonogram(): Collection
    {
        return $this->harmonogram;
    }

    public function addHarmonogram(KursHarmonogram $harmonogram): static
    {
        if (!$this->harmonogram->contains($harmonogram)) {
            $this->harmonogram->add($harmonogram);
            $harmonogram->setKurs($this);
        }

        return $this;
    }

    public function removeHarmonogram(KursHarmonogram $harmonogram): static
    {
        if ($this->harmonogram->removeElement($harmonogram)) {
            // set the owning side to null (unless already changed)
            if ($harmonogram->getKurs() === $this) {
                $harmonogram->setKurs(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TeoriaListaObecnosci>
     */
    public function getTeoriaListaObecnoscis(): Collection
    {
        return $this->teoriaListaObecnoscis;
    }

    public function addTeoriaListaObecnosci(TeoriaListaObecnosci $teoriaListaObecnosci): static
    {
        if (!$this->teoriaListaObecnoscis->contains($teoriaListaObecnosci)) {
            $this->teoriaListaObecnoscis->add($teoriaListaObecnosci);
            $teoriaListaObecnosci->addKur($this);
        }

        return $this;
    }

    public function removeTeoriaListaObecnosci(TeoriaListaObecnosci $teoriaListaObecnosci): static
    {
        if ($this->teoriaListaObecnoscis->removeElement($teoriaListaObecnosci)) {
            $teoriaListaObecnosci->removeKur($this);
        }

        return $this;
    }
}
