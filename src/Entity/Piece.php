<?php

namespace App\Entity;

use App\Repository\PieceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PieceRepository::class)
 */
class Piece
{

    const ORIGINE_ACHETEUR = 1;

    const ORIGINE_VENDEUR = 2;

    const ORIGINES = [
        'Acheteur' => 1,
        'Vendeur' => 2
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
 

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $dateTransmission;

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="pieces")
     */
    private $dossier;

    /**
     * @ORM\ManyToOne(targetEntity=Fichier::class, cascade={"persist"})
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentTypeActe::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $document;

    /**
     * @ORM\Column(type="smallint")
     */
    private $origine;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libDocument;

    /**
     * @ORM\Column(type="boolean")
     */
    private $client;
    

    public function __construct()
    {
        $this->setClient(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getDateTransmission(): ?\DateTimeInterface
    {
        return $this->dateTransmission;
    }

    public function setDateTransmission(\DateTimeInterface $dateTransmission): self
    {
        $this->dateTransmission = $dateTransmission;

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
        if ($fichier->getFile() || ($fichier && $fichier->getId())) {
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

    public function getOrigine(): ?int
    {
        return $this->origine;
    }

    public function setOrigine(int $origine): self
    {
        $this->origine = $origine;

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

    public function getClient(): ?bool
    {
        return $this->client;
    }

    public function setClient(bool $client): self
    {
        $this->client = $client;

        return $this;
    }
}
