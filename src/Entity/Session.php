<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbPlace = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ouvert = null;

    /**
     * @var Collection<int, Stagiaire>
     */
    #[ORM\ManyToMany(targetEntity: Stagiaire::class, inversedBy: 'sessions')]
    // table associative entre Session et Stagiaire, chaque session a une collection de stagiaires
    private Collection $inscription;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    /**
     * @var Collection<int, Programme>
     */
    #[ORM\OneToMany(targetEntity: Programme::class, mappedBy: 'session', cascade: ['persist'],  orphanRemoval: true)]
    #[ORM\OrderBy(["module" => "ASC"])] //rajout d'un order by à la collection
    private Collection $programmes;

    public function __construct()
    {
        $this->inscription = new ArrayCollection();
        $this->programmes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbPlace(): ?int
    {
        return $this->nbPlace;
    }

    public function setNbPlace(?int $nbPlace): static
    {
        $this->nbPlace = $nbPlace;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function isOuvert(): ?bool
    {
        return $this->ouvert;
    }

    public function setOuvert(?bool $ouvert): static
    {
        $this->ouvert = $ouvert;

        return $this;
    }

    /**
     * @return Collection<int, Stagiaire>
     */
    public function getInscription(): Collection
    {
        return $this->inscription;
    }

    public function addInscription(Stagiaire $inscription): static
    {
        if (!$this->inscription->contains($inscription)) {
            $this->inscription->add($inscription);
        }

        return $this;
    }

    public function removeInscription(Stagiaire $inscription): static
    {
        $this->inscription->removeElement($inscription);

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * @return Collection<int, Programme>
     */
    public function getProgrammes(): Collection
    {
        return $this->programmes;
    }

    public function addProgramme(Programme $programme): static
    {
        if (!$this->programmes->contains($programme)) {
            $this->programmes->add($programme);
            $programme->setSession($this);
        }

        return $this;
    }

    public function removeProgramme(Programme $programme): static
    {
        if ($this->programmes->removeElement($programme)) {
            // set the owning side to null (unless already changed)
            if ($programme->getSession() === $this) {
                $programme->setSession(null);
            }
        }

        return $this;
    }

    //probablement à changer
    public function __toString(){
        return $this->formation;
    }

    //duree d'une session
    public function getDuree()
    {
        $interval = $this->dateFin->diff($this->dateDebut);

        $result="";
        //si l'interval contient une année
        if($interval->format('%y') != 0){
            $result .= $interval->format('%y') . " an";
        }
        //si l'interval contient des mois
        if($interval->format('%m') != 0){
            $result .= " " .$interval->format('%m') . " mois";
        }
        //si l'interval contient des jours
        if($interval->format('%d') != 0){
            $result .= " " .$interval->format('%d') . " jours";
        }
        return $result;
        //"d" pour le nombre de jours pas contenu dans le calcul d'un mois, "a" pour le nombre de jours total
    }

    //verifie si la date de début n'est pas passée, et si la date de début n'est pas avant la date de début
    public function getValidDate() : bool 
    {
        $ajd = new \DateTime();
        if($this->dateDebut > $this->dateFin || $this->dateDebut < $ajd){
            return false; 
        } else {
            return true ;
        }
    }

    //nb de stagiaires inscrits
    public function getNbInscription(){
        $nbInscription = count($this->inscription);
        return $nbInscription;
    }

    //places disponibles
    public function getNbDispo(){
        return $this->nbPlace - $this->getNbInscription();
    }

}
