<?php

namespace App\Entity;

use App\DataFixtures\UserFixtures;
use App\DateTime\DateTime as DT;
use App\Entity\Traits\BaseTrait;
use App\Entity\Traits\BaseUserTrait;
use App\Entity\User\SocialAccount;
use App\Object\ObjectAccessor;
use App\Validator\Group as ValidationGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupableInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="username", message="Логин занят")
 * @UniqueEntity(fields="email", message="Данный адрес электронной почты уже зарегистрирован")
 */
class User implements UserInterface, GroupableInterface, EquatableInterface
{
    use BaseUserTrait;
    //TODO remove
    use BaseTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="user", orphanRemoval=true)
     */
    private $sessions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Profile", mappedBy="author", orphanRemoval=true)
     */
    private $profiles;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile", inversedBy="users")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $profile;

    /**
     * @ORM\Column(type="integer")
     */
    private $money = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $allMoney = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limitTime;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Ip", inversedBy="users")
     */
    private $ips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transfer", mappedBy="user", orphanRemoval=true)
     */
    private $transfers;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSocial = false;
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=SocialAccount::class, mappedBy="user", orphanRemoval=true)
     */
    private $socialAccounts;

    public function __construct()
    {
        $this->socialAccounts = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->addTime = new \DateTime();
        $this->ips = new ArrayCollection();
        $this->transfers = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->homework = new ArrayCollection();
        $this->roles = [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAddTime(): ?DT
    {
        return DT::createFromDT($this->addTime);
    }

    public function getRegisteredAt(): DT
    {
        return $this->getAddTime();
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function setRegistrationTime(\DateTimeInterface $registrationTime): void
    {
        $this->setAddTime($registrationTime);
    }

    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setUser($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getUser() === $this) {
                $session->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Profile[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            $profile->setAuthor($this);
        }

        return $this;
    }

    public function removeProfile(Profile $profile): self
    {
        if ($this->profiles->contains($profile)) {
            $this->profiles->removeElement($profile);
            // set the owning side to null (unless already changed)
            if ($profile->getAuthor() === $this) {
                $profile->setAuthor(null);
            }
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(? Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getAllMoney(): ?int
    {
        return $this->allMoney;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getLimitTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->limitTime);
    }

    public function setLimitTime(\DateTimeInterface $limitTime): self
    {
        $this->limitTime = $limitTime;

        return $this;
    }

    public function addMoney(int $m)
    {
        $this->allMoney += $m;

        return $this->setMoney($this->getMoney() + $m);
    }

    /**
     * @return Collection|Ip[]
     */
    public function getIps(): Collection
    {
        return $this->ips;
    }

    public function addIp(Ip $ip): self
    {
        if (!$this->ips->contains($ip) && $ip->isValid()) {
            $con = false;

            foreach ($this->ips as $e) {
                if ($e->getIp() === $ip->getIp()) {
                    $con = true;

                    break;
                }
            }

            if (!$con) {
                $this->ips[] = $ip;
            }
        }

        return $this;
    }

    public function removeIp(Ip $ip): self
    {
        if ($this->ips->contains($ip)) {
            $this->ips->removeElement($ip);
        }

        return $this;
    }

    public function setIps(array $ips): self
    {
        $this->ips = $ips;

        return $this;
    }

    /**
     * @return Collection|Transfer[]
     */
    public function getTransfers(): Collection
    {
        return $this->transfers;
    }

    public function addTransfer(Transfer $transfer): self
    {
        if (!$this->transfers->contains($transfer)) {
            $this->transfers[] = $transfer;
            $transfer->setUser($this);
        }

        return $this;
    }

    public function removeTransfer(Transfer $transfer): self
    {
        if ($this->transfers->contains($transfer)) {
            $this->transfers->removeElement($transfer);
            // set the owning side to null (unless already changed)
            if ($transfer->getUser() === $this) {
                $transfer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Имя не должно быть пустым", groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT})
     * @Assert\Length(
     * min = 2,
     * minMessage = "Ваше имя должно содержать как минимум {{ limit }} символа",
     *groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT}
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Фамилия не должна быть пустой", groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT})
     * @Assert\Length(
     * min = 2,
     * minMessage = "Ваша фамилия должна содержать как минимум {{ limit }} символа",
     * groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT}
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $network;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $networkId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Отчество не должно быть пустым", groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT})
     * @Assert\Length(
     * min = 4,
     * minMessage = "Ваше отчество должно содержать как минимум {{ limit }} символа",
     * groups={ValidationGroup::ACCOUNT, ValidationGroup::STUDENT}
     * )
     */
    private $fatherName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTeacher = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="teacher")
     */
    private $students;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="students")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="author", orphanRemoval=true)
     */
    private $tasks;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Task", mappedBy="contractors")
     */
    private $homework;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(? string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(? string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLogin()
    {
        return !$this->isSocialUsername() ? $this->getUsername() : null;
    }

    public function getFullName()
    {
        $fn = $this->getFirstName();
        $ln = $this->getLastName();

        return $fn.$ln ? $fn.' '.$ln : null;
    }

    public function getFFName()
    {
        return $this->getSomeName('%s %s', ['firstName', 'fatherName']);
    }

    private function getSomeName($f, $names)
    {
        $a = [];

        foreach ($names as $n) {
            $a[] = $this->$n;
        }

        return $a ? trim(sprintf(...array_merge([$f], $a))) : null;
    }

    public function getCallName()
    {
        return ($this->isTeacher() or $this->hasStudents()) ? $this->getFFName() : $this->existsName();
    }

    public function hasStudents(): bool
    {
        return (bool) $this->getStudents()->count();
    }

    public function existsName()
    {
        return $this->firstName ?: $this->username;
    }

    public function isSocial(): bool
    {
        return $this->isSocial ?? false;
    }

    public function setIsSocial($s)
    {
        $this->isSocial = $s;

        return $this;
    }

    /*
    public function getUsername() {
    return $this->username ?? $this->getFirstName()." ".$this->getLastName();
    }
     */

    public function setUsername($u)
    {
        $this->username = $u;

        return $this->setUsernameCanonical($u);
    }

    public function getNetwork(): ?string
    {
        return $this->network;
    }

    public function setNetwork(? string $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function getNetworkId(): ?string
    {
        return $this->networkId;
    }

    public function setNetworkId(? string $networkId): self
    {
        $this->networkId = $networkId;

        return $this;
    }

    public function getFatherName(): ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(? string $fatherName): self
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    public function isTeacher(): bool
    {
        return (bool) $this->isTeacher;
    }

    public function isTeacherOf(self $student): bool
    {
        return $this->students->contains($student);
    }

    public function setIsTeacher(? bool $isTeacher): self
    {
        $this->isTeacher = $isTeacher;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(self $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->setTeacher($this);
        }

        return $this;
    }

    public function removeStudent(self $student): self
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
            // set the owning side to null (unless already changed)
            if ($student->getTeacher() === $this) {
                $student->setTeacher(null);
            }
        }

        return $this;
    }

    public function getTeacher()
    {
        return $this->teacher;
    }

    public function setTeacher(? self $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function hasTeacher()
    {
        return (bool) $this->teacher;
    }

    public function isStudentOf(self $teacher): bool
    {
        return $this->hasTeacher() && $this->teacher->isEqualTo($teacher);
    }

    public function fio()
    {
        return $this->lastName.' '.$this->firstName.' '.$this->fatherName;
    }

    public function getAttempts()
    {
        $as = [];

        foreach ($this->sessions as $s) {
            $as = array_merge($as, $s->getAttempts()->getValues());
        }

        return new ArrayCollection($as);
    }

    public function isStudent()
    {
        return $this->hasTeacher();
    }

    public function cleanSocialUsername()
    {
        if ($this->isSocialUsername()) {
            $this->username = '';
        }

        return $this;
    }

    public function isSocialUsername()
    {
        return preg_match('#^\^#', $this->username);
    }

    public function getFLName()
    {
        return $this->getSomeName('%s %s', ['firstName', 'lastName']);
    }

    public function getLFName()
    {
        return $this->getSomeName('%s %s', ['lastName', 'firstName']);
    }

    public function getDumpName()
    {
        $name = $this->isTeacher ? $this->getSomeName('%s %s %s', ['firstName', 'fatherName', 'lastName']) : $this->getSomeName('%s %s', ['firstName', 'lastName']);

        return $name ?: $this->username;
    }

    /**
     * @return Collection|User[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(? self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function isParent($parent): bool
    {
        return $this->parent === $parent;
    }

    public function getRealStudents(): Collection
    {
        return array_reduce(
            $this->getStudents()->getValues(),
            function ($realStudents, $student) {
                if (!$this->children->contains($student)) {
                    $realStudents[] = $student;
                }

                return $realStudents;
            },
            new ArrayCollection()
        );
    }

    public function hasParent(): bool
    {
        return (bool) $this->parent;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setAuthor($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getAuthor() === $this) {
                $task->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getHomework(): Collection
    {
        return $this->homework;
    }

    public function addHomework(Task $homework): void
    {
        if (!$this->homework->contains($homework)) {
            $this->homework[] = $homework;
            $homework->addContractor($this);
        }
    }

    public function removeHomework(Task $homework): self
    {
        if ($this->homework->contains($homework)) {
            $this->homework->removeElement($homework);
            $homework->removeContractor($this);
        }

        return $this;
    }

    public function getLastAttempt(): ?Attempt
    {
        \Webmozart\Assert\Assert::notSame(UserFixtures::GUEST_USERNAME, $this->getUsername());
        /** @var Session|null $session */
        $session = $this->getSessions()->first();

        if (!$session) {
            return null;
        }

        return $session->getAttempts()->last() ?: null;
    }

    public function addSocialAccount(SocialAccount $socialAccount): void
    {
        if (!$this->socialAccounts->contains($socialAccount)) {
            $this->socialAccounts[] = $socialAccount;
        }

        $socialAccount->setUser($this);
    }

    /**
     * @return bool
     */
    public function isEqualTo(SymfonyUserInterface $user)
    {
        foreach (['id', 'username', 'roles'] as $field) {
            if (null === $this->roles && 'roles' === $field) {
                //this user created from serialization and has no roles
                //We don't compare roles field
                continue;
            }

            if (ObjectAccessor::getValue($this, $field) !== ObjectAccessor::getValue($user, $field)) {
                return false;
            }
        }

        return true;
    }
}
