<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="type_acte")
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 * @
 */
class Type
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
     * @ORM\Column(type="string", length=255)
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=Workflow::class, mappedBy="typeActe", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $workflows;

    /**
     * @ORM\OneToMany(targetEntity=Dossier::class, mappedBy="typeActe")
     */
    private $dossiers;

    /**
     * @ORM\OneToMany(targetEntity=GestionWorkflow::class, mappedBy="type")
     */
    private $gestionWorkflows;

    /**
     * @ORM\OneToMany(targetEntity=DocumentTypeActe::class, mappedBy="type", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $documentTypeActes;



    /**
     * @ORM\Column(type="string", length=100)
     */
    private $code;

    public function __construct()
    {
        $this->workflows = new ArrayCollection();
        $this->dossiers = new ArrayCollection();
        $this->gestionWorkflows = new ArrayCollection();
        $this->documentTypeActes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setActive(string $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Workflow>
     */
    public function getWorkflows(): Collection
    {
        return $this->workflows;
    }

    public function addWorkflow(Workflow $workflow): self
    {
        if (!$this->workflows->contains($workflow)) {
            $this->workflows[] = $workflow;
            $workflow->setTypeActe($this);
        }

        return $this;
    }

    public function removeWorkflow(Workflow $workflow): self
    {
        if ($this->workflows->removeElement($workflow)) {
            // set the owning side to null (unless already changed)
            if ($workflow->getTypeActe() === $this) {
                $workflow->setTypeActe(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Dossier>
     */
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }

    public function addDossier(Dossier $dossier): self
    {
        if (!$this->dossiers->contains($dossier)) {
            $this->dossiers[] = $dossier;
            $dossier->setTypeActe($this);
        }

        return $this;
    }

    public function removeDossier(Dossier $dossier): self
    {
        if ($this->dossiers->removeElement($dossier)) {
            // set the owning side to null (unless already changed)
            if ($dossier->getTypeActe() === $this) {
                $dossier->setTypeActe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GestionWorkflow>
     */
    public function getGestionWorkflows(): Collection
    {
        return $this->gestionWorkflows;
    }

    public function addGestionWorkflow(GestionWorkflow $gestionWorkflow): self
    {
        if (!$this->gestionWorkflows->contains($gestionWorkflow)) {
            $this->gestionWorkflows[] = $gestionWorkflow;
            $gestionWorkflow->setType($this);
        }

        return $this;
    }

    public function removeGestionWorkflow(GestionWorkflow $gestionWorkflow): self
    {
        if ($this->gestionWorkflows->removeElement($gestionWorkflow)) {
            // set the owning side to null (unless already changed)
            if ($gestionWorkflow->getType() === $this) {
                $gestionWorkflow->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DocumentTypeActe>
     */
    public function getDocumentTypeActes(): Collection
    {
        return $this->documentTypeActes;
    }

    public function addDocumentTypeActe(DocumentTypeActe $documentTypeActe): self
    {
        if (!$this->documentTypeActes->contains($documentTypeActe)) {
            $this->documentTypeActes[] = $documentTypeActe;
            $documentTypeActe->setType($this);
        }

        return $this;
    }

    public function removeDocumentTypeActe(DocumentTypeActe $documentTypeActe): self
    {
        if ($this->documentTypeActes->removeElement($documentTypeActe)) {
            // set the owning side to null (unless already changed)
            if ($documentTypeActe->getType() === $this) {
                $documentTypeActe->setType(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

}
