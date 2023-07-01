<?php

namespace App\Entity;

use App\Repository\RemiseActeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RemiseActeRepository::class)
 */
class RemiseActe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist"})
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="remiseActes")
     */
    private $dossier;

    public function getId(): ?int
    {
        return $this->id;
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
}
