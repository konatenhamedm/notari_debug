<?php

namespace App\Entity;

use App\Repository\PieceVendeurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PieceVendeurRepository::class)
 */
class PieceVendeur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="pieceVendeurs")
     */
    private $client;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="pieceVendeurs")
     */
    private $dossier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath($path): self
    {
        if (!is_null($path)){
            $this->path = $path;
        }

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
}
