<?php

namespace App\Entity;

use App\Repository\DossierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=DossierRepository::class)
 */
class Dossier
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
    private $numeroOuverture;

    

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

 

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;


     /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $etat = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $objet;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="dossiers")
     */
    private $typeActe;

    /**
     * @ORM\OneToMany(targetEntity=Calendar::class, mappedBy="dossier")
     */
    private $calendars;


    /**
     * @ORM\OneToMany(targetEntity=CourierArrive::class, mappedBy="dossier")
     */
    private $courriers;


    /**
     * @ORM\OneToMany(targetEntity=DossierWorkflow::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $dossierWorkflows;

    /**
     * @ORM\OneToMany(targetEntity=Identification::class, mappedBy="dossier",cascade={"persist", "remove"})
     * @Assert\Valid(groups={"identification"})
     */
    private $identifications;

    /**
     * @ORM\OneToMany(targetEntity=Verification::class, mappedBy="dossier",cascade={"persist", "remove"})
     * @Assert\Valid(groups={"verifcation"})
     */
    private $verifications;

    /**
     * @ORM\OneToMany(targetEntity=Piece::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $pieces;

    /**
     * @ORM\OneToMany(targetEntity=DocumentSigne::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $documentSignes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etape;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Enregistrement::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $enregistrements;

    /**
     * @ORM\OneToMany(targetEntity=PieceVendeur::class, mappedBy="dossier",cascade={"persist"})
     */
    private $pieceVendeurs;

    /**
     * @ORM\OneToMany(targetEntity=Redaction::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $redactions;

    /**
     * @ORM\OneToMany(targetEntity=Obtention::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $obtentions;

    /**
     * @ORM\OneToMany(targetEntity=Remise::class, mappedBy="dossier",cascade={"persist", "remove"})
     */
    private $remises;

    /**
     * @ORM\OneToMany(targetEntity=RemiseActe::class, mappedBy="dossier", cascade={"persist", "remove"})
     */
    private $remiseActes;

    /**
     * @ORM\OneToOne(targetEntity=InfoClassification::class, mappedBy="dossier", cascade={"persist", "remove"})
     */
    private $infoClassification;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $numeroC;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $numeroO;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $comparant1;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $comparant2;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $repertoire;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $renvoi;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $piece;





    public function __construct()
    {
        $this->calendars = new ArrayCollection();
        $this->courriers = new ArrayCollection();
        $this->dossierWorkflows = new ArrayCollection();
        $this->identifications = new ArrayCollection();
        $this->verifications = new ArrayCollection();
        $this->pieces = new ArrayCollection();
        $this->documentSignes = new ArrayCollection();
        $this->enregistrements = new ArrayCollection();
        $this->pieceVendeurs = new ArrayCollection();
        $this->redactions = new ArrayCollection();
        $this->obtentions = new ArrayCollection();
        $this->remises = new ArrayCollection();
        $this->remiseActes = new ArrayCollection();
        $this->setActive(true);
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroOuverture(): ?string
    {
        return $this->numeroOuverture;
    }

    public function setNumeroOuverture(string $numeroOuverture): self
    {
        $this->numeroOuverture = $numeroOuverture;

        return $this;
    }

    public function getNumeroClassification(): ?string
    {
        return $this->numeroClassification;
    }

    public function setNumeroClassification(string $numeroClassification): self
    {
        $this->numeroClassification = $numeroClassification;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateClassification(): ?\DateTimeInterface
    {
        return $this->dateClassification;
    }

    public function setDateClassification(\DateTimeInterface $dateClassification): self
    {
        $this->dateClassification = $dateClassification;

        return $this;
    }



    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }



    public function getEtat(): ?array
    {
        return $this->etat;
    }

    public function setEtat(array $etat): self
    {
        $this->etat = $etat;

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

    public function getTypeActe(): ?Type
    {
        return $this->typeActe;
    }

    public function setTypeActe(?Type $typeActe): self
    {
        $this->typeActe = $typeActe;

        return $this;
    }

    /**
     * @return Collection<int, DossierWorkflow>
     */
    public function getDossierWorkflows(): Collection
    {
        return $this->dossierWorkflows;
    }

    public function addDossierWorkflow(DossierWorkflow $dossierWorkflow): self
    {
        if (!$this->dossierWorkflows->contains($dossierWorkflow)) {
            $this->dossierWorkflows[] = $dossierWorkflow;
            $dossierWorkflow->setDossier($this);
        }

        return $this;
    }

    public function removeDossierWorkflow(DossierWorkflow $dossierWorkflow): self
    {
        if ($this->dossierWorkflows->removeElement($dossierWorkflow)) {
            // set the owning side to null (unless already changed)
            if ($dossierWorkflow->getDossier() === $this) {
                $dossierWorkflow->setDossier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Verification>
     */
    public function getVerifications(): Collection
    {
        return $this->verifications;
    }

    public function addVerification(Verification $verification): self
    {
        if (!$this->verifications->contains($verification)) {
            $this->verifications[] = $verification;
            $verification->setDossier($this);
        }

        return $this;
    }

    public function removeVerification(Verification $verification): self
    {
        if ($this->verifications->removeElement($verification)) {
            // set the owning side to null (unless already changed)
            if ($verification->getDossier() === $this) {
                $verification->setDossier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Identification>
     */
    public function getIdentifications(): Collection
    {
        return $this->identifications;
    }

    public function addIdentification(Identification $identification): self
    {
        if (!$this->identifications->contains($identification)) {
            $this->identifications[] = $identification;
            $identification->setDossier($this);
        }

        return $this;
    }

    public function removeIdentification(Identification $identification): self
    {
        if ($this->identifications->removeElement($identification)) {
            // set the owning side to null (unless already changed)
            if ($identification->getDossier() === $this) {
                $identification->setDossier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Piece>
     */
    public function getPieces(): Collection
    {
        return $this->pieces;
    }

    public function addPiece(Piece $piece): self
    {
        if (!$this->pieces->contains($piece)) {
            $this->pieces[] = $piece;
            $piece->setDossier($this);
        }

        return $this;
    }

    public function removePiece(Piece $piece): self
    {
        if ($this->pieces->removeElement($piece)) {
            // set the owning side to null (unless already changed)
            if ($piece->getDossier() === $this) {
                $piece->setDossier(null);
                $piece->setFichier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DocumentSigne>
     */
    public function getDocumentSignes(): Collection
    {
        return $this->documentSignes;
    }

    public function addDocumentSigne(DocumentSigne $documentSigne): self
    {
        if (!$this->documentSignes->contains($documentSigne)) {
            $this->documentSignes[] = $documentSigne;
            $documentSigne->setDossier($this);
        }

        return $this;
    }

    public function removeDocumentSigne(DocumentSigne $documentSigne): self
    {
        if ($this->documentSignes->removeElement($documentSigne)) {
            // set the owning side to null (unless already changed)
            if ($documentSigne->getDossier() === $this) {
                $documentSigne->setDossier(null);
                $documentSigne->setFichier(null);
            }
        }

        return $this;
    }

    public function getEtape(): ?string
    {
        return $this->etape;
    }

    public function setEtape(string $etape): self
    {
        $this->etape = $etape;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Enregistrement>
     */
    public function getEnregistrements(): Collection
    {
        return $this->enregistrements;
    }

    public function addEnregistrement(Enregistrement $enregistrement): self
    {
        if (!$this->enregistrements->contains($enregistrement)) {
            $this->enregistrements[] = $enregistrement;
            $enregistrement->setDossier($this);
        
        }

        return $this;
    }

    public function removeEnregistrement(Enregistrement $enregistrement): self
    {
        if ($this->enregistrements->removeElement($enregistrement)) {
            // set the owning side to null (unless already changed)
            if ($enregistrement->getDossier() === $this) {
                $enregistrement->setDossier(null);
                $enregistrement->setFichier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PieceVendeur>
     */
    public function getPieceVendeurs(): Collection
    {
        return $this->pieceVendeurs;
    }

    public function addPieceVendeur(PieceVendeur $pieceVendeur): self
    {
        if (!$this->pieceVendeurs->contains($pieceVendeur)) {
            $this->pieceVendeurs[] = $pieceVendeur;
            $pieceVendeur->setDossier($this);
        }

        return $this;
    }

    public function removePieceVendeur(PieceVendeur $pieceVendeur): self
    {
        if ($this->pieceVendeurs->removeElement($pieceVendeur)) {
            // set the owning side to null (unless already changed)
            if ($pieceVendeur->getDossier() === $this) {
                $pieceVendeur->setDossier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Redaction>
     */
    public function getRedactions(): Collection
    {
        return $this->redactions;
    }

    public function addRedaction(Redaction $redaction): self
    {
        if (!$this->redactions->contains($redaction)) {
            $this->redactions[] = $redaction;
            $redaction->setDossier($this);
        }

        return $this;
    }

    public function removeRedaction(Redaction $redaction): self
    {
        if ($this->redactions->removeElement($redaction)) {
            // set the owning side to null (unless already changed)
            if ($redaction->getDossier() === $this) {
                $redaction->setDossier(null);
                $redaction->setFichier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Obtention>
     */
    public function getObtentions(): Collection
    {
        return $this->obtentions;
    }

    public function addObtention(Obtention $obtention): self
    {
        if (!$this->obtentions->contains($obtention)) {
            $this->obtentions[] = $obtention;
            $obtention->setDossier($this);
        }

        return $this;
    }

    public function removeObtention(Obtention $obtention): self
    {
        if ($this->obtentions->removeElement($obtention)) {
            // set the owning side to null (unless already changed)
            if ($obtention->getDossier() === $this) {
                $obtention->setDossier(null);
                $obtention->setFichier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Remise>
     */
    public function getRemises(): Collection
    {
        return $this->remises;
    }

    public function addRemise(Remise $remise): self
    {
        if (!$this->remises->contains($remise)) {
            $this->remises[] = $remise;
            $remise->setDossier($this);
        }

        return $this;
    }

    public function removeRemise(Remise $remise): self
    {
        if ($this->remises->removeElement($remise)) {
            // set the owning side to null (unless already changed)
            if ($remise->getDossier() === $this) {
                $remise->setDossier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RemiseActe>
     */
    public function getRemiseActes(): Collection
    {
        return $this->remiseActes;
    }

    public function addRemiseActe(RemiseActe $remiseActe): self
    {
        if (!$this->remiseActes->contains($remiseActe)) {
            $this->remiseActes[] = $remiseActe;
            $remiseActe->setDossier($this);
        }

        return $this;
    }

    public function removeRemiseActe(RemiseActe $remiseActe): self
    {
        if ($this->remiseActes->removeElement($remiseActe)) {
            // set the owning side to null (unless already changed)
            if ($remiseActe->getDossier() === $this) {
                $remiseActe->setDossier(null);
                $remiseActe->setFichier(null);
            }
        }
        return $this;
    }

    public function getInfoClassification(): ?InfoClassification
    {
        return $this->infoClassification;
    }

    public function setInfoClassification(InfoClassification $infoClassification): self
    {
        // set the owning side of the relation if necessary
        if ($infoClassification->getDossier() !== $this) {
            $infoClassification->setDossier($this);
        }

        $this->infoClassification = $infoClassification;

        return $this;
    }


     /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $enregistrements = $this->getEnregistrements();

        foreach ($enregistrements as $index => $enregistrement) {
            
            if ($enregistrement->getDate() && (!$enregistrement->getNumero() ||  !$enregistrement->getFichier())) {
                $context->buildViolation(sprintf(
                    'Veuillez renseigner le numéro et/ou le fichier pour la ligne [%s]',
                    Enregistrement::SENS[$enregistrement->getSens()]
                ))
                ->addViolation();
            }


            if ($enregistrement->getNumero() && (!$enregistrement->getDate() || !$enregistrement->getFichier())) {
                $context->buildViolation(sprintf(
                    'Veuillez renseigner la date et/ou le fichier pour le numéro [%s] dans la ligne [%s]',
                    $enregistrement->getNumero(),
                    Enregistrement::SENS[$enregistrement->getSens()]
                ))
                ->addViolation();
            }
        }
    }

    /**
     * @return Collection<int, Calendar>
     */
    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

    public function addCalendar(Calendar $calendar): self
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars[] = $calendar;
            $calendar->setDossier($this);
        }

        return $this;
    }

    public function removeCalendar(Calendar $calendar): self
    {
        if ($this->calendars->removeElement($calendar)) {
            // set the owning side to null (unless already changed)
            if ($calendar->getDossier() === $this) {
                $calendar->setDossier(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, CourierArrive>
     */
    public function getCourriers(): Collection
    {
        return $this->courriers;
    }

    public function addCourrier(CourierArrive $courrier): self
    {
        if (!$this->courriers->contains($courrier)) {
            $this->courriers[] = $courrier;
            $courrier->setDossier($this);
        }

        return $this;
    }

    public function removeCourriers(CourierArrive $courrier): self
    {
        if ($this->courriers->removeElement($courrier)) {
            // set the owning side to null (unless already changed)
            if ($courrier->getDossier() === $this) {
                $courrier->setDossier(null);
            }
        }

        return $this;
    }


    public function getNumeroC(): ?string
    {
        return $this->numeroC;
    }

    public function setNumeroC(string $numeroc): self
    {
        $this->numeroC = $numeroc;

        return $this;
    }

    public function getNumeroO(): ?string
    {
        return $this->numeroO;
    }

    public function setNumeroO(string $numeroO=null): self
    {
        $this->numeroO = $numeroO;

        return $this;
    }

    public function getComparant1(): ?string
    {
        return $this->comparant1;
    }

    public function setComparant1(string $comparant1): self
    {
        $this->comparant1 = $comparant1;

        return $this;
    }

    public function getComparant2(): ?string
    {
        return $this->comparant2;
    }

    public function setComparant2(string $comparant2): self
    {
        $this->comparant2 = $comparant2;

        return $this;
    }

    public function getRepertoire(): ?string
    {
        return $this->repertoire;
    }

    public function setRepertoire(string $repertoire): self
    {
        $this->repertoire = $repertoire;

        return $this;
    }
    public function getRenvoi(): ?string
    {
        return $this->renvoi;
    }

    public function setRenvoi(string $renvoi): self
    {
        $this->renvoi = $renvoi;

        return $this;
    }
    public function getPiece(): ?string
    {
        return $this->piece;
    }

    public function setPiece(string $piece): self
    {
        $this->piece = $piece;

        return $this;
    }

}
