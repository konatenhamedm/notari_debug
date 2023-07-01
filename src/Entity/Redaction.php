<?php

namespace App\Entity;

use App\Repository\RedactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RedactionRepository::class)
 */
class Redaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="redactions")
     */
    private $dossier;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     */
    private $fichier;

    /**
     * @ORM\Column(type="smallint")
     */
    private $numVersion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): self
    {
        if ($fichier->getFile()) {
            $this->fichier = $fichier;
        }
        

        return $this;
    }

    public function getNumVersion(): ?int
    {
        return $this->numVersion;
    }

    public function setNumVersion(int $numVersion): self
    {
        $this->numVersion = $numVersion;

        return $this;
    }
}
