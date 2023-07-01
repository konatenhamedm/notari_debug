<?php

namespace App\Entity;

use App\Repository\DossierWorkflowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DossierWorkflowRepository::class)
 */
class DossierWorkflow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFin;


    /**
     * @ORM\ManyToOne(targetEntity=Workflow::class, inversedBy="dossierWorkflows")
     */
    private $workflow;

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="dossierWorkflows")
     */
    private $dossier;

    /**
     * @ORM\OneToOne(targetEntity=SuiviDossierWorkflow::class, mappedBy="dossierWorkflow", cascade={"persist", "remove"})
     */
    private $suivi;



    public function getId(): ?int
    {
        return $this->id;
    }



    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }


    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): self
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getSuivi(): ?SuiviDossierWorkflow
    {
        return $this->suivi;
    }

    public function setSuivi(SuiviDossierWorkflow $suivi): self
    {
        // set the owning side of the relation if necessary
        if ($suivi->getDossierWorkflow() !== $this) {
            $suivi->setDossierWorkflow($this);
        }

        $this->suivi = $suivi;

        return $this;
    }

}
