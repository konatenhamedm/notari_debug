<?php

namespace App\Entity;

use App\Repository\ObtentionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ObtentionRepository::class)
 */
class Obtention
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
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="obtentions")
     */
    private $dossier;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentTypeActe::class)
     */
    private $document;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libDocument;

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

    public function getDocument(): ?DocumentTypeActe
    {
        return $this->document;
    }

    public function setDocument(?DocumentTypeActe $document): self
    {
        $this->document = $document;

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
