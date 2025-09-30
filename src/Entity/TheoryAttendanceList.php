<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\TheoryAttendanceListRepository;

#[ORM\Entity(repositoryClass: TheoryAttendanceListRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER', fields: ['theory', 'customer'])]
class TheoryAttendanceList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'attendanceList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theory $theory = null;

    #[ORM\ManyToOne(inversedBy: 'theoryAttendanceLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    /**
     * @var Collection<int, Course>
     */
    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'theoryAttendanceLists', fetch: 'EAGER')]
    private Collection $course;

    public function __construct()
    {
        $this->course = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheory(): ?Theory
    {
        return $this->theory;
    }

    public function setTheory(?Theory $theory): static
    {
        $this->theory = $theory;

        return $this;
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

    /**
     * @return Collection<int, Course>
     */
    public function getCourse(): Collection
    {
        return $this->course;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->course->contains($course)) {
            $this->course->add($course);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        $this->course->removeElement($course);

        return $this;
    }
}
