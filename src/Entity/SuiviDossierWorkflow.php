<?php

namespace App\Entity;

use App\Repository\SuiviDossierWorkflowRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SuiviDossierWorkflowRepository::class)
 */
class SuiviDossierWorkflow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=DossierWorkflow::class, inversedBy="suivi", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $dossierWorkflow;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private $etat;


    public function __construct()
    {
        $this->setEtat(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDossierWorkflow(): ?DossierWorkflow
    {
        return $this->dossierWorkflow;
    }

    public function setDossierWorkflow(DossierWorkflow $dossierWorkflow): self
    {
        $this->dossierWorkflow = $dossierWorkflow;

        return $this;
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

    public function getEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
