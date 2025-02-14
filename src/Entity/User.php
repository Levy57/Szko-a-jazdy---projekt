<?php

namespace App\Entity;

use App\Enum\Kategoria;
use App\Enum\Status;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;


    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = ['ROLE_PRAKTYKANT'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $imie = null;

    #[ORM\Column(length: 255)]
    private ?string $nazwisko = null;

    #[ORM\Column(length: 255)]
    private ?string $numer_telefonu = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, enumType: Kategoria::class)]
    private ?array $kategoria_uprawnien = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * @var Collection<int, Kurs>
     */
    #[ORM\OneToMany(targetEntity: Kurs::class, mappedBy: 'praktykant')]
    private Collection $kurs;

    /**
     * @var Collection<int, Kurs>
     */
    #[ORM\OneToMany(targetEntity: Kurs::class, mappedBy: 'instruktor')]
    private Collection $kursInstruktor;
    private Collection $kursInstruktorAktywne;

    /**
     * @var Collection<int, KursHarmonogram>
     */
    #[ORM\OneToMany(targetEntity: KursHarmonogram::class, mappedBy: 'instruktor', orphanRemoval: true)]
    private Collection $kursHarmonograms;

    /**
     * @var Collection<int, Teoria>
     */
    #[ORM\OneToMany(targetEntity: Teoria::class, mappedBy: 'instruktor')]
    private Collection $teoriaInstruktor;

    /**
     * @var Collection<int, TeoriaListaObecnosci>
     */
    #[ORM\OneToMany(targetEntity: TeoriaListaObecnosci::class, mappedBy: 'praktykant')]
    private Collection $teoriaListaObecnoscis;


    public function __construct()
    {
        $this->kurs = new ArrayCollection();
        $this->kursInstruktor = new ArrayCollection();
        $this->kursInstruktorAktywne = new ArrayCollection();
        $this->kursy = new ArrayCollection();
        $this->kursHarmonograms = new ArrayCollection();
        $this->teoriaInstruktor = new ArrayCollection();
        $this->teoriaListaObecnoscis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $role = array_map(function ($role) {
            if (isset($role->value))
                return $role->value;
            else
                return $role;
        }, $roles);


        return $role;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getImie(): ?string
    {
        return $this->imie;
    }

    public function setImie(string $imie): static
    {
        $this->imie = $imie;

        return $this;
    }

    public function getNazwisko(): ?string
    {
        return $this->nazwisko;
    }

    public function setNazwisko(string $nazwisko): static
    {
        $this->nazwisko = $nazwisko;

        return $this;
    }

    public function getNumerTelefonu(): ?string
    {
        return $this->numer_telefonu;
    }

    public function setNumerTelefonu(string $numer_telefonu): static
    {
        $this->numer_telefonu = $numer_telefonu;

        return $this;
    }

    /**
     * @return Kategoria[]|null
     */
    public function getKategoriaUprawnien(): ?array
    {
        return $this->kategoria_uprawnien;
    }

    public function setKategoriaUprawnien(?array $kategoria_uprawnien): static
    {
        $this->kategoria_uprawnien = $kategoria_uprawnien;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Kurs>
     */
    public function getKurs(): Collection
    {
        return $this->kurs;
    }

    /**
     * @return Collection<int, Kurs>
     */
    public function getKursInstruktor(): Collection
    {
        return $this->kursInstruktor;
    }
    /**
     * @return Collection<int, Kurs>
     */
    public function getKursInstruktorAktywne(): Collection
    {
        $kursy = new ArrayCollection();
        foreach ($this->kursInstruktor as $kurs)
            if ($kurs->getStatus() != (Status::Ukonczony)->value)
                $kursy[] = $kurs;
        return $kursy;
    }

    /**
     * @return Collection<int, KursHarmonogram>
     */
    public function getKursHarmonograms(): Collection
    {
        return $this->kursHarmonograms;
    }

    public function addKursHarmonogram(KursHarmonogram $kursHarmonogram): static
    {
        if (!$this->kursHarmonograms->contains($kursHarmonogram)) {
            $this->kursHarmonograms->add($kursHarmonogram);
            $kursHarmonogram->setInstruktor($this);
        }

        return $this;
    }

    public function removeKursHarmonogram(KursHarmonogram $kursHarmonogram): static
    {
        if ($this->kursHarmonograms->removeElement($kursHarmonogram)) {
            // set the owning side to null (unless already changed)
            if ($kursHarmonogram->getInstruktor() === $this) {
                $kursHarmonogram->setInstruktor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Teoria>
     */
    public function getTeoriaInstruktor(): Collection
    {
        return $this->teoriaInstruktor;
    }

    public function addTeoriaInstruktor(Teoria $teoriaInstruktor): static
    {
        if (!$this->teoriaInstruktor->contains($teoriaInstruktor)) {
            $this->teoriaInstruktor->add($teoriaInstruktor);
            $teoriaInstruktor->setInstruktor($this);
        }

        return $this;
    }

    public function removeTeoriaInstruktor(Teoria $teoriaInstruktor): static
    {
        if ($this->teoriaInstruktor->removeElement($teoriaInstruktor)) {
            // set the owning side to null (unless already changed)
            if ($teoriaInstruktor->getInstruktor() === $this) {
                $teoriaInstruktor->setInstruktor(null);
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
            $teoriaListaObecnosci->setPraktykant($this);
        }

        return $this;
    }

    public function removeTeoriaListaObecnosci(TeoriaListaObecnosci $teoriaListaObecnosci): static
    {
        if ($this->teoriaListaObecnoscis->removeElement($teoriaListaObecnosci)) {
            // set the owning side to null (unless already changed)
            if ($teoriaListaObecnosci->getPraktykant() === $this) {
                $teoriaListaObecnosci->setPraktykant(null);
            }
        }

        return $this;
    }
}
