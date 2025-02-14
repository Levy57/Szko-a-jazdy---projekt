<?php

namespace App\Entity;

use App\Repository\KursHarmonogramRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KursHarmonogramRepository::class)]
class KursHarmonogram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\ManyToOne(inversedBy: 'kursHarmonograms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $instruktor = null;

    #[ORM\ManyToOne(inversedBy: 'harmonogram')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kurs $kurs = null;

    #[ORM\Column]
    private ?int $czas_trwania = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $komentarz = null;

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

    public function getInstruktor(): ?User
    {
        return $this->instruktor;
    }

    public function setInstruktor(?User $instruktor): static
    {
        $this->instruktor = $instruktor;

        return $this;
    }

    public function getKurs(): ?Kurs
    {
        return $this->kurs;
    }

    public function setKurs(?Kurs $kurs): static
    {
        $this->kurs = $kurs;

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

    public function getKomentarz(): ?string
    {
        return $this->komentarz;
    }

    public function setKomentarz(?string $komentarz): static
    {
        $this->komentarz = $komentarz;

        return $this;
    }
}
