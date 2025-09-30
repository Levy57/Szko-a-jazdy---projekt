<?php

namespace App\Entity;

use App\Enum\Status;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    private Collection $roles;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_category')]
    private Collection $categories;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 5, options: ['default' => 'pl'])]
    private string $locale = 'pl';

    /**
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'customer', cascade: ["remove"])]
    private Collection $course;

    /**
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'employee')]
    private Collection $courseEmployee;
    private Collection $courseEmployeeActive;

    /**
     * @var Collection<int, CourseSchedule>
     */
    #[ORM\OneToMany(targetEntity: CourseSchedule::class, mappedBy: 'employee', orphanRemoval: true)]
    private Collection $courseSchedules;

    /**
     * @var Collection<int, Theory>
     */
    #[ORM\OneToMany(targetEntity: Theory::class, mappedBy: 'employee')]
    private Collection $theoryEmployee;

    /**
     * @var Collection<int, TheoryAttendanceList>
     */
    #[ORM\OneToMany(targetEntity: TheoryAttendanceList::class, mappedBy: 'customer')]
    private Collection $theoryAttendanceLists;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->course = new ArrayCollection();
        $this->courseEmployee = new ArrayCollection();
        $this->courseEmployeeActive = new ArrayCollection();
        $this->courseSchedules = new ArrayCollection();
        $this->theoryEmployee = new ArrayCollection();
        $this->theoryAttendanceLists = new ArrayCollection();
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

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                $roles[] = $permission->value;
            }
        }

        return array_unique($roles);
    }

    /**
     * @return Collection<int, Role>
     */
    public function getUserRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourse(): Collection
    {
        return $this->course;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourseEmployee(): Collection
    {
        return $this->courseEmployee;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourseEmployeeActive(): Collection
    {
        $courses = new ArrayCollection();
        foreach ($this->courseEmployee as $course) {
            if ($course->getStatus() != Status::completed->value) {
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * @return Collection<int, CourseSchedule>
     */
    public function getCourseSchedules(): Collection
    {
        return $this->courseSchedules;
    }

    public function addCourseSchedule(CourseSchedule $courseSchedule): static
    {
        if (!$this->courseSchedules->contains($courseSchedule)) {
            $this->courseSchedules->add($courseSchedule);
            $courseSchedule->setEmployee($this);
        }

        return $this;
    }

    public function removeCourseHarmonogram(CourseSchedule $courseSchedule): static
    {
        if ($this->courseSchedules->removeElement($courseSchedule)) {
            if ($courseSchedule->getEmployee() === $this) {
                $courseSchedule->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Theory>
     */
    public function getTheoryEmployee(): Collection
    {
        return $this->theoryEmployee;
    }

    public function addTheoryEmployee(Theory $theoryEmployee): static
    {
        if (!$this->theoryEmployee->contains($theoryEmployee)) {
            $this->theoryEmployee->add($theoryEmployee);
            $theoryEmployee->setEmployee($this);
        }

        return $this;
    }

    public function removeTheoryEmployee(Theory $theoryEmployee): static
    {
        if ($this->theoryEmployee->removeElement($theoryEmployee)) {
            if ($theoryEmployee->getEmployee() === $this) {
                $theoryEmployee->setEmployee(null);
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
            $theoryAttendanceList->setCustomer($this);
        }

        return $this;
    }

    public function removeTheoryAttendanceList(TheoryAttendanceList $theoryAttendanceList): static
    {
        if ($this->theoryAttendanceLists->removeElement($theoryAttendanceList)) {
            if ($theoryAttendanceList->getCustomer() === $this) {
                $theoryAttendanceList->setCustomer(null);
            }
        }

        return $this;
    }
}
