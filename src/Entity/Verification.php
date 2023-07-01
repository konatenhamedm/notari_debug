<?php

namespace App\Entity;

use App\Repository\DocumentSigneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentSigneRepository::class)
 */
class Verification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;




    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="verifications")
     */
    private $dossier;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist"})
     */
    private $fichierDemande;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateEnvoi;


    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist"})
     */
    private $fichierReponse;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateReponse;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libDocument;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getFichierDemande(): ?Fichier
    {
        return $this->fichierDemande;
    }

    public function setFichierDemande(?Fichier $fichierDemande): self
    {
        if ($fichierDemande->getFile()) {
            $this->fichierDemande = $fichierDemande;
        }
       

        return $this;
    }

    public function getFichierReponse(): ?Fichier
    {
        return $this->fichierReponse;
    }

    public function setFichierReponse(?Fichier $fichierReponse): self
    {
        if ($fichierReponse->getFile()) {
            $this->fichierReponse = $fichierReponse;
        }


        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(?\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(?\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;

        return $this;
    }

    public function getLibDocument(): ?string
    {
        return $this->libDocument;
    }

    public function setLibDocument(string $libDocument): self
    {
        $this->libDocument = $libDocument;

        return $this;
    }

}
