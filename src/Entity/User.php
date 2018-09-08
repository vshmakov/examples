<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\GroupableInterface;
use App\DT;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="username", message="Логин занят")
 * @UniqueEntity(fields="email", message="Данный адрес электронной почты уже зарегистрирован")
 */
class User implements UserInterface, GroupableInterface
{
    use DTTrait, BaseUserTrait;

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
     * @ORM\Column(type="datetime")
     */
    private $limitTime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Code", mappedBy="user")
     */
    private $codes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Ip", inversedBy="users")
     */
    private $ips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transfer", mappedBy="user", orphanRemoval=true)
     */
    private $transfers;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=180, nullable=true)
     * @Assert\Regex(
     *     pattern="/^[a-z][a-z0-9\._\-]+[a-z0-9]$/",
     *     message="Логин должен начинаться с буквы, заканчиваться буквой или цифрой  и может содержать только строчные латинские символы, цифры, а также ._-"
     * )
     * @Assert\Length(
     * min = 3,
     * minMessage = "Ваш логин должен содержать как минимум {{ limit }} символа",
     * )
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="username_canonical", type="string", length=180, unique=true, nullable=true)
     */
    private $usernameCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_canonical", type="string", length=180, unique=true, nullable=true)
     */
    private $emailCanonical;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSocial = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\Length(
     * min = 3,
     * minMessage = "Ваш пароль должен содержать как минимум {{ limit }} символовlong",
     * )
     */
    private $password;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var string|null
     *
     * @ORM\Column(name="confirmation_token", type="string", length=180, unique=true, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array", length=0, nullable=false)
     */
    private $roles;

    public function __construct()
    {
        $this->enabled = false;
        $this->roles = [];
        $this->sessions = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $l = TEST_DAYS;
        $this->limitTime = (new \DateTime())->add(new \DateInterval("P{$l}D"));
        $this->addTime = new \DateTime();
        $this->codes = new ArrayCollection();
        $this->money = DEFAULT_MONEY;
        $this->ips = new ArrayCollection();
        $this->transfers = new ArrayCollection();
        $this->students = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return $this->dt($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
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

    public function setProfile(?Profile $profile): self
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
        return $this->dt($this->limitTime);
    }

    public function setLimitTime(\DateTimeInterface $limitTime): self
    {
        $this->limitTime = $limitTime;

        return $this;
    }

    public function getRemainedTime()
    {
        $d = $this->getLimitTime()->getTimestamp() - time();

        return DT::createFromTimestamp($d > 0 ? $d : 0);
    }

    /**
     * @return Collection|Code[]
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function addCode(Code $code): self
    {
        if (!$this->codes->contains($code)) {
            $this->codes[] = $code;
            $code->setUser($this);
        }

        return $this;
    }

    public function removeCode(Code $code): self
    {
        if ($this->codes->contains($code)) {
            $this->codes->removeElement($code);
            // set the owning side to null (unless already changed)
            if ($code->getUser() === $this) {
                $code->setUser(null);
            }
        }

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
                if ($e->getIp() == $ip->getIp()) {
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
     * @Assert\NotBlank(message="Имя не должно быть пустым")
     * @Assert\Length(
     * min = 2,
     * minMessage = "Ваше имя должно содержать как минимум {{ limit }} символа",
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Фамилия не должна быть пустой")
     * @Assert\Length(
     * min = 2,
     * minMessage = "Ваша фамилия должна содержать как минимум {{ limit }} символа",
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
     */
    private $fatherName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTeacher;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="teacher")
     */
    private $students;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="students")
     */
    private $teacher;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
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
        return $this->getFullName() ?? $this->username;
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

    public function setNetwork(?string $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function getNetworkId(): ?string
    {
        return $this->networkId;
    }

    public function setNetworkId(?string $networkId): self
    {
        $this->networkId = $networkId;

        return $this;
    }

    public function getFatherName(): ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(?string $fatherName): self
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    public function isTeacher(): ?bool
    {
        return (bool) $this->isTeacher;
    }

    public function setIsTeacher(?bool $isTeacher): self
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

    public function addStudent(User $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->setTeacher($this);
        }

        return $this;
    }

    public function removeStudent(User $student): self
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

    public function setTeacher(?User $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function hasTeacher()
    {
        return (bool) $this->teacher;
    }

    public function isUserTeacher(User $teacher)
    {
        return $this->teacher === $teacher;
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
}
