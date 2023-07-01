<?php

namespace App\Entity;

use App\Repository\WorkflowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WorkflowRepository::class)
 */
class Workflow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $numeroEtape;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelleEtape;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombreJours;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="workflows")
     */
    private $typeActe;

    /**
     * @ORM\ManyToOne(targetEntity=GestionWorkflow::class, inversedBy="workflow")
     */
    private $gestionWorkflow;


    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=DossierWorkflow::class, mappedBy="workflow")
     */
    private $dossierWorkflows;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $propriete;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $route;

    public function __construct()
    {
        $this->acteVenteWorkflows = new ArrayCollection();
        $this->dossierWorkflows = new ArrayCollection();
        $this->active = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroEtape(): ?int
    {
        return $this->numeroEtape;
    }

    public function setNumeroEtape(int $numeroEtape): self
    {
        $this->numeroEtape = $numeroEtape;

        return $this;
    }

    public function getLibelleEtape(): ?string
    {
        return $this->libelleEtape;
    }

    public function setLibelleEtape(string $libelleEtape): self
    {
        $this->libelleEtape = $libelleEtape;

        return $this;
    }

    public function getNombreJours(): ?int
    {
        return $this->nombreJours;
    }

    public function setNombreJours(int $nombreJours): self
    {
        $this->nombreJours = $nombreJours;

        return $this;
    }

    public function getTypeActe(): ?Type
    {
        return $this->typeActe;
    }

    public function setTypeActe(?Type $typeActe): self
    {
        $this->typeActe = $typeActe;

        return $this;
    }

    public function getGestionWorkflow(): ?GestionWorkflow
    {
        return $this->gestionWorkflow;
    }

    public function setGestionWorkflow(?GestionWorkflow $gestionWorkflow): self
    {
        $this->gestionWorkflow = $gestionWorkflow;

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
     * @return Collection<int, DossierWorkflow>
     */
    public function getDossierWorkflows(): Collection
    {
        return $this->dossierWorkflows;
    }

    public function addDossierWorkflow(DossierWorkflow $dossierWorkflow): self
    {
        if (!$this->dossierWorkflows->contains($dossierWorkflow)) {
            $this->dossierWorkflows[] = $dossierWorkflow;
            $dossierWorkflow->setWorkflow($this);
        }

        return $this;
    }

    public function removeDossierWorkflow(DossierWorkflow $dossierWorkflow): self
    {
        if ($this->dossierWorkflows->removeElement($dossierWorkflow)) {
            // set the owning side to null (unless already changed)
            if ($dossierWorkflow->getWorkflow() === $this) {
                $dossierWorkflow->setWorkflow(null);
            }
        }

        return $this;
    }

    public function getPropriete(): ?string
    {
        return $this->propriete;
    }

    public function setPropriete(string $propriete): self
    {
        $this->propriete = $propriete;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }
}
