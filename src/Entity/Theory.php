<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TheoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TheoryRepository::class)]
class Theory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'theoryEmployee')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employee = null;

    /**
     * @var Collection<int, TheoryAttendanceList>
     */
    #[ORM\OneToMany(targetEntity: TheoryAttendanceList::class, mappedBy: 'theory', cascade: ["remove"])]
    private Collection $attendanceList;

    public function __construct()
    {
        $this->attendanceList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmployee(): ?User
    {
        return $this->employee;
    }

    public function setEmployee(?User $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * @return Collection<int, TheoryAttendanceList>
     */
    public function getAttendanceList(): Collection
    {
        return $this->attendanceList;
    }

    public function addAttendanceList(TheoryAttendanceList $attendanceList): static
    {
        if (!$this->attendanceList->contains($attendanceList)) {
            $this->attendanceList->add($attendanceList);
            $attendanceList->setTheory($this);
        }

        return $this;
    }

    public function removeAttendanceList(TheoryAttendanceList $attendanceList): static
    {
        if ($this->attendanceList->removeElement($attendanceList)) {
            if ($attendanceList->getTheory() === $this) {
                $attendanceList->setTheory(null);
            }
        }

        return $this;
    }
}
