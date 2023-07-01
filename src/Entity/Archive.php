<?php

namespace App\Entity;

use App\Repository\ArchiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=ArchiveRepository::class)
 */
class Archive
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $acheteur;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $vendeur;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $dateCreation;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeActe;

    /**
     * @ORM\OneToMany(targetEntity=DocumentArchive::class, mappedBy="archive", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $documents;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner le numéro de classification")
     * @ORM\Column(type="string", length=25)
     */
    private $numeroClassification;

    /**
     *  @Assert\NotBlank(message="Veuillez renseigner la date de classification")
     * @ORM\Column(type="date")
     */
    private $dateClassification;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner l'objet")
     * @ORM\Column(type="string", length=255)
     */
    private $objet;

    /**
    *  @Assert\NotBlank(message="Veuillez renseigner le numéro d'ouverture")
     * @ORM\Column(type="string", length=25)
    */
    private $numeroOuverture;

    /**
     * @ORM\Column(type="date")
     */
    private $dateOuverture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getAcheteur(): ?Client
    {
        return $this->acheteur;
    }

    public function setAcheteur(?Client $acheteur): self
    {
        $this->acheteur = $acheteur;

        return $this;
    }

    public function getVendeur(): ?Client
    {
        return $this->vendeur;
    }

    public function setVendeur(?Client $vendeur): self
    {
        $this->vendeur = $vendeur;

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
     * @return Collection<int, DocumentArchive>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DocumentArchive $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setArchive($this);
        }

        return $this;
    }

    public function removeDocument(DocumentArchive $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getArchive() === $this) {
                $document->setArchive(null);
            }
        }

        return $this;
    }

    public function getNumeroClassification(): ?string
    {
        return $this->numeroClassification;
    }

    public function setNumeroClassification(?string $numeroClassification): self
    {
        $this->numeroClassification = $numeroClassification;

        return $this;
    }

    public function getDateClassification(): ?\DateTimeInterface
    {
        return $this->dateClassification;
    }

    public function setDateClassification(?\DateTimeInterface $dateClassification): self
    {
        $this->dateClassification = $dateClassification;

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


    public function getNumeroOuverture(): ?string
    {
        return $this->numeroOuverture;
    }

    public function setNumeroOuverture(string $numeroOuverture): self
    {
        $this->numeroOuverture = $numeroOuverture;

        return $this;
    }

    public function getDateOuverture(): ?\DateTimeInterface
    {
        return $this->dateOuverture;
    }

    public function setDateOuverture(?\DateTimeInterface $dateOuverture): self
    {
        $this->dateOuverture = $dateOuverture;

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

}
