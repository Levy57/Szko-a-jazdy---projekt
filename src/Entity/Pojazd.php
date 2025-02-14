<?php

namespace App\Entity;

use App\Enum\Kategoria;
use App\Enum\PojazdStan;
use App\Repository\PojazdRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PojazdRepository::class)]
class Pojazd
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nazwa = null;

    #[ORM\Column(length: 255)]
    private ?string $marka = null;

    #[ORM\Column(nullable: false)]
    private ?int $rok = null;

    #[ORM\Column(enumType: PojazdStan::class)]
    private ?PojazdStan $stan = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Kategoria::class)]
    private array $kategoria = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $komentarz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNazwa(): ?string
    {
        return $this->nazwa;
    }

    public function setNazwa(?string $nazwa): static
    {
        $this->nazwa = $nazwa;

        return $this;
    }

    public function getMarka(): ?string
    {
        return $this->marka;
    }

    public function setMarka(string $marka): static
    {
        $this->marka = $marka;

        return $this;
    }

    public function getRok(): ?int
    {
        return $this->rok;
    }

    public function setRok(int $rok): static
    {
        $this->rok = $rok;

        return $this;
    }

    public function getStan(): ?PojazdStan
    {
        return $this->stan;
    }

    public function setStan(PojazdStan $stan): static
    {
        $this->stan = $stan;

        return $this;
    }

    /**
     * @return Kategoria[]
     */
    public function getKategoria(): array
    {
        return $this->kategoria;
    }

    public function setKategoria(array $kategoria): static
    {
        $this->kategoria = $kategoria;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): static
    {
        $this->vin = $vin;

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
