<?php

namespace App\Entity;

use App\Repository\GestionWorkflowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GestionWorkflowRepository::class)
 */
class GestionWorkflow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Workflow::class, mappedBy="gestionWorkflow",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $workflow;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="integer")
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="gestionWorkflows")
     */
    private $type;

    public function __construct()
    {
        $this->workflow = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Workflow>
     */
    public function getWorkflow(): Collection
    {
        return $this->workflow;
    }

    public function addWorkflow(Workflow $workflow): self
    {
        if (!$this->workflow->contains($workflow)) {
            $this->workflow[] = $workflow;
            $workflow->setGestionWorkflow($this);
        }

        return $this;
    }

    public function removeWorkflow(Workflow $workflow): self
    {
        if ($this->workflow->removeElement($workflow)) {
            // set the owning side to null (unless already changed)
            if ($workflow->getGestionWorkflow() === $this) {
                $workflow->setGestionWorkflow(null);
            }
        }

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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}
