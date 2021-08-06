<?php

namespace App\Entity;

use App\Repository\MovieRepository;
// On va appliquer la logique de mapping via l'annotation @ORM
// qui correspond à un dossier "Mapping" de Doctrine
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Classe qui représente la table "movie" et ses enregistrements
 * 
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 * 
 * Cette entité va réagir aux événements "lifecycle callbacks" de Doctrine
 * https://symfony.com/doc/current/doctrine/events.html#doctrine-lifecycle-callbacks
 * @ORM\HasLifecycleCallbacks()
 * 
 * Unicité sur les propriétés $title
 * @UniqueEntity("title")
 */
class Movie
{
    /**
     * Ceci est un DocBlock
     * 
     * Clé primaire
     * Auto-increment
     * type INT
     * 
     * Ceci est une série d'annotations dans un DocBlock
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("movies_get")
     */
    private $id;

    /**
     * Titre
     * 
     * @ORM\Column(type="string", length=211, unique=true)
     * 
     * @Assert\NotBlank
     * @Assert\Length(max=211)
     * @Groups("movies_get")
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Genre::class, inversedBy="movies")
     * @ORM\OrderBy({"name"="ASC"})
     * @Assert\Count(min=1)
     * @Groups("movies_get")
     */
    private $genres;

    /**
     * @ORM\OneToMany(targetEntity=Casting::class, mappedBy="movie", cascade={"remove"})
     * @ORM\OrderBy({"creditOrder"="ASC"})
     * @Groups("movies_get")
     */
    private $castings;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="movie", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Assert\DateTime
     * @Groups("movies_get")
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="smallint")
     * 
     * @Assert\NotBlank
     * @Assert\Positive
     * @Assert\LessThanOrEqual(1440)
     * @Groups("movies_get")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("movies_get")
     */
    private $poster;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("movies_get")
     * @Assert\NotBlank
     * @Assert\Type("int") 
     * @Assert\Length(max = 1)
     * @Assert\Choice({5, 4, 3, 2, 1}) 
     * @Groups({"movies_get"})
     */
    private $rating;

    /**
     * @ORM\Column(type="string", length=211, unique=true)
     * @Groups("movies_get")
     */
    private $slugger;

    /**
     * Les valeur par défaut utiles
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->releaseDate = new DateTime();
        $this->genres = new ArrayCollection();
        $this->castings = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * Get clé primaire
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get titre
     */ 
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set titre
     *
     * @return  self
     */ 
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     */ 
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @return  self
     */ 
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres[] = $genre;
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection|Casting[]
     */
    public function getCastings(): Collection
    {
        return $this->castings;
    }

    public function addCasting(Casting $casting): self
    {
        if (!$this->castings->contains($casting)) {
            $this->castings[] = $casting;
            $casting->setMovie($this);
        }

        return $this;
    }

    public function removeCasting(Casting $casting): self
    {
        if ($this->castings->removeElement($casting)) {
            // set the owning side to null (unless already changed)
            if ($casting->getMovie() === $this) {
                $casting->setMovie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setMovie($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMovie() === $this) {
                $review->setMovie(null);
            }
        }

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTime $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getSlugger(): ?string
    {
        return $this->slugger;
    }

    public function setSlugger(string $slugger): self
    {
        $this->slugger = $slugger;

        return $this;
    }

    /**
     * Exécute cette méthode avant l'update de l'entité en BDD
     * /!\ Géré en interne par Doctrine
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValueToNow()
    {
        $this->updatedAt = new DateTime();
    }

}