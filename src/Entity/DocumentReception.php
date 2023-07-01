<?php

namespace App\Entity;

use App\Repository\DocumentCourrierRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentReceptionRepository::class)
 */
class DocumentReception
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
     * @ORM\ManyToOne(targetEntity=Fichier::class,  cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=CourierArrive::class, inversedBy="documentReceptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courier;

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

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): self
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getCourier(): ?CourierArrive
    {
        return $this->courier;
    }

    public function setCourier(?CourierArrive $courier): self
    {
        $this->courier = $courier;

        return $this;
    }
}
