<?php

namespace App\Entity;

use App\Repository\DocumentArchiveRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentArchiveRepository::class)
 */
class DocumentArchive
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentTypeActe::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=Archive::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $archive;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libDocument;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): self
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    public function setArchive(?Archive $archive): self
    {
        $this->archive = $archive;

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
