<?php

namespace App\Entity;

use App\Enum\Status;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CourseRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'course')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $theory = null;

    #[ORM\Column]
    private ?int $theoryHours = null;

    #[ORM\Column]
    private ?int $courseHours = null;

    #[ORM\ManyToOne(inversedBy: 'courseEmployee')]
    private ?User $employee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endedAt = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = Status::planed;

    /**
     * @var Collection<int, CourseSchedule>
     */
    #[ORM\OneToMany(targetEntity: CourseSchedule::class, mappedBy: 'course')]
    private Collection $schedule;

    /**
     * @var Collection<int, TheoryAttendanceList>
     */
    #[ORM\ManyToMany(targetEntity: TheoryAttendanceList::class, mappedBy: 'course', fetch: 'EAGER')]
    private Collection $theoryAttendanceLists;

    public function __construct()
    {
        $this->schedule = new ArrayCollection();
        $this->theoryAttendanceLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }
    public function setCustomer(?User $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isTheory(): ?bool
    {
        return $this->theory;
    }
    public function setTheory(bool $theory): static
    {
        $this->theory = $theory;

        return $this;
    }

    public function getTheoryhours(): ?int
    {
        return $this->theoryHours;
    }
    public function setTheoryhours(int $theoryHours): static
    {
        $this->theoryHours = $theoryHours;

        return $this;
    }

    public function getCourseHours(): ?int
    {
        return $this->courseHours;
    }
    public function setCourseHours(int $courseHours): static
    {
        $this->courseHours = $courseHours;

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

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }
    public function setStartAt(?\DateTimeInterface $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): static
    {
        $this->endedAt = $endedAt;

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
     * @return Collection<int, CourseSchedule>
     */
    public function getSchedule(): Collection
    {
        return $this->schedule;
    }

    public function setSchedule(CourseSchedule $schedule): static
    {
        if (!$this->schedule->contains($schedule)) {
            $this->schedule->add($schedule);
            $schedule->setCourse($this);
        }

        return $this;
    }

    public function removeSchedule(CourseSchedule $schedule): static
    {
        if ($this->schedule->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getCourse() === $this) {
                $schedule->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TheoryAttendanceList>
     */
    public function getTheoryAttendanceLists(): Collection
    {
        return $this->theoryAttendanceLists;
    }

    public function addTheoryAttendanceList(TheoryAttendanceList $theoryAttendanceList): static
    {
        if (!$this->theoryAttendanceLists->contains($theoryAttendanceList)) {
            $this->theoryAttendanceLists->add($theoryAttendanceList);
            $theoryAttendanceList->addCourse($this);
        }

        return $this;
    }

    public function removeTheoryAttendanceList(TheoryAttendanceList $theoryAttendanceList): static
    {
        if ($this->theoryAttendanceLists->removeElement($theoryAttendanceList)) {
            $theoryAttendanceList->removeCourse($this);
        }

        return $this;
    }
}
