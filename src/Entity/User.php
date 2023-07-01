<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="utilisateur")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields ="email", message= "Ce code est déjà associé à un utilisateur")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotNull(message="Le champs email est requis")
     *
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotNull(message="Le champs role est requis")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string",nullable=true)
     * @Assert\NotNull(message="Le champs mot de passe est requis")
     *
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(message="Le champs nom est requis")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(message="Le champs prenoms est requis")
     */
    private $prenoms;

    /**
     * @ORM\Column(type="integer")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=CourierArrive::class, mappedBy="user")
     */
    private $courierArrives;

    /**
     * @ORM\ManyToMany(targetEntity=UserGroupe::class, inversedBy="utilisateurs")
     */
    private $groupes;


    public function __construct()
    {
        $this->courierArrives = new ArrayCollection();
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getOwnRoles()
    {
        return $this->roles;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
      
        $roles = (array)$this->roles;
        $roles[] = 'ROLE_USER';
        foreach ($this->getGroupes() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }       

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }


        /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param $roles
     */
    public function hasRoles($roles)
    {
        return array_intersect($this->getRoles(), $roles);
    }


    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @return Collection|Groupe[]
     */
    public function getGroupes(): Collection
    {
        return $this->groupes ?: $this->groupes = new ArrayCollection();
    }

    public function addGroupe(UserGroupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
        }

        return $this;
    }

    public function removeGroupe(UserGroupe $groupe): self
    {
        $this->groupes->removeElement($groupe);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(string $prenoms): self
    {
        $this->prenoms = $prenoms;

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, CourierArrive>
     */
    public function getCourierArrives(): Collection
    {
        return $this->courierArrives;
    }

    public function addCourierArrife(CourierArrive $courierArrife): self
    {
        if (!$this->courierArrives->contains($courierArrife)) {
            $this->courierArrives[] = $courierArrife;
            $courierArrife->setUser($this);
        }

        return $this;
    }

    public function removeCourierArrife(CourierArrive $courierArrife): self
    {
        if ($this->courierArrives->removeElement($courierArrife)) {
            // set the owning side to null (unless already changed)
            if ($courierArrife->getUser() === $this) {
                $courierArrife->setUser(null);
            }
        }

        return $this;
    }


}
