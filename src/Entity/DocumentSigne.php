<?php

namespace App\Entity;

use App\Repository\DocumentSigneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentSigneRepository::class)
 */
class DocumentSigne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;




    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="documentSignes")
     */
    private $dossier;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist"})
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentTypeActe::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $document;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateAcheteur;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateVendeur;

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

    public function getDateAcheteur(): ?\DateTimeInterface
    {
        return $this->dateAcheteur;
    }

    public function setDateAcheteur(?\DateTimeInterface $dateAcheteur): self
    {
        $this->dateAcheteur = $dateAcheteur;

        return $this;
    }

    public function getDateVendeur(): ?\DateTimeInterface
    {
        return $this->dateVendeur;
    }

    public function setDateVendeur(?\DateTimeInterface $dateVendeur): self
    {
        $this->dateVendeur = $dateVendeur;

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
