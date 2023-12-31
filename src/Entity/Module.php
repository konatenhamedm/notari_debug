<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ModuleRepository::class)
 */
class Module
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=Groupe::class, mappedBy="module",cascade={"persist"})
     */
    private $groupes;

    /**
     * @ORM\ManyToOne(targetEntity=ModuleParent::class, inversedBy="modules")
     */
    private $parent;

    /**
     * @ORM\Column(type="integer")
     */
    private $ordre;

    /**
     * @ORM\Column(type="integer")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity=Icons::class, inversedBy="modules")
     */
    private $icon;

    public function __construct()
    {
        $this->groupes = new ArrayCollection();
    }

    public function getProperties(){
        return  ['titre'=> 'titre','parent'=> 'parent'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }


    /**
     * @return Collection|Groupe[]
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->setModule($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getModule() === $this) {
                $groupe->setModule(null);
            }
        }

        return $this;
    }

    public function getParent(): ?ModuleParent
    {
        return $this->parent;
    }

    public function setParent(?ModuleParent $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

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

    public function getIcon(): ?Icons
    {
        return $this->icon;
    }

    public function setIcon(?Icons $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

}
