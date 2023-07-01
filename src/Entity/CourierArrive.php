<?php

namespace App\Entity;

use App\Repository\CourierArriveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourierArriveRepository::class)
 */
class CourierArrive
{
    const OPTIONS = ['Hébergement assuré', 'Repas fourni'];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $numero;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $dateReception;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $dateEnvoi;

    /**
     * @ORM\Column(type="text")
     */
    private $objet;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="courierArrives")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="recep")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $recep;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $existe;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $finalise;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $rangement;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $expediteur;


    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $destinataire;

    /**
     * @ORM\OneToMany(targetEntity=DocumentCourrier::class, mappedBy="courier", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $documentCourriers;

    /**
     * @ORM\OneToMany(targetEntity=DocumentReception::class, mappedBy="courier", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $documentReceptions;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $etat;
    /**
     * @ORM\Column(type="text" ,nullable=true)
     */
    private $courrier;

    /**
     * @ORM\ManyToOne(targetEntity=Dossier::class, inversedBy="courriers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dossier;

    public function __construct()
    {
        $this->documentCourriers = new ArrayCollection();
        $this->documentReceptions = new ArrayCollection();
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier=null): self
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getCourrier(): ?string
    {
        return $this->courrier;
    }

    public function setCourrier(string $courrier): self
    {
        $this->courrier = $courrier;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDateReception(): ?\DateTimeInterface
    {
        return $this->dateReception;
    }

    public function setDateReception($dateReception): self
    {
        $this->dateReception = $dateReception;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi($dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setActive(string $active): self
    {
        $this->active = $active;

        return $this;
    }



    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getRecep(): ?Client
    {
        return $this->recep;
    }

    public function setRecep(?Client $recep): self
    {
        $this->recep = $recep;

        return $this;
    }

    public function isExiste(): ?bool
    {
        return $this->existe;
    }

    public function setExiste(?bool $existe): self
    {
        $this->existe = $existe;

        return $this;
    }
    public function isFinalise(): ?bool
    {
        return $this->finalise;
    }

    public function setFinalise(?bool $finalise): self
    {
        $this->finalise = $finalise;

        return $this;
    }
    public function getRangement(): ?string
    {
        return $this->rangement;
    }

    public function setRangement(string $rangement): self
    {
        $this->rangement = $rangement;

        return $this;
    }

    public function getExpediteur(): ?string
    {
        return $this->expediteur;
    }

    public function setExpediteur(string $expediteur): self
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinataire(): ?string
    {
        return $this->destinataire;
    }

    public function setDestinataire(string $destinataire): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * @return Collection<int, DocumentCourrier>
     */
    public function getDocumentCourriers(): Collection
    {
        return $this->documentCourriers;
    }

    public function addDocumentCourrier(DocumentCourrier $documentCourrier): self
    {
        if (!$this->documentCourriers->contains($documentCourrier)) {
            $this->documentCourriers[] = $documentCourrier;
            $documentCourrier->setCourier($this);
        }

        return $this;
    }

    public function removeDocumentCourrier(DocumentCourrier $documentCourrier): self
    {
        if ($this->documentCourriers->removeElement($documentCourrier)) {
            // set the owning side to null (unless already changed)
            if ($documentCourrier->getCourier() === $this) {
                $documentCourrier->setCourier(null);
            }
        }

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }


    /**
     * @return Collection<int, DocumentReception>
     */
    public function getDocumentReceptions(): Collection
    {
        return $this->documentReceptions;
    }

    public function addDocumentReception(DocumentReception $documentReception): self
    {
        if (!$this->documentReceptions->contains($documentReception)) {
            $this->documentReceptions[] = $documentReception;
            $documentReception->setCourier($this);
        }

        return $this;
    }

    public function removeDocumentReception(DocumentReception $documentReception): self
    {
        if ($this->documentReceptions->removeElement($documentReception)) {
            // set the owning side to null (unless already changed)
            if ($documentReception->getCourier() === $this) {
                $documentReception->setCourier(null);
            }
        }

        return $this;
    }
}
