<?php

namespace App\Entity;

use App\Repository\DocumentClientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentClientRepository::class)
 */
class DocumentClient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Fichier::class, cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichier;



    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentTypeActe::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $document;

    /**
     * @var string
     */
    private $docHash;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libelle;

    public function getId(): ?int
    {
        return $this->id;
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



    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

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


    public function getDocHash(): ?string
    {
        return $this->docHash;
    }

    public function setDochash(?string $docHash): self
    {
        $this->docHash = $docHash;
        return $this;
    }

    public function getLibelle(): ?string
    {
        if ($this->libelle) {
            return $this->libelle;
        }

        if ($this->getDocument()) {
            return $this->document->getLibelle();
        }

        return $this->libelle;
        
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
}
