<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
Use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[Vich\Uploadable]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=3,
     *     minMessage="Nom doit contenir ou moins 3 lettre")
     */
    #[ORM\Column]
    private ?string $nomproduit = null;

    /**
     * @Assert\NotBlank
     * @Assert\Positive
     */
    #[ORM\Column]
    private ?float $prix = null;

    /**
     * @Assert\NotBlank
     * @Assert\Positive
     */
    #[ORM\Column]
    private ?int $quantityproduit = null;

    #[ORM\Column]
    private ?bool $dispoproduit = null;

    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'lignecommande')]
    private Collection $commandes;

    #[ORM\Column(length: 255, type: 'string')]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'products', fileNameProperty:'img')]
    private ?File $imgFile = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $color = null;


    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomproduit(): ?string
    {
        return $this->nomproduit;
    }

    public function setNomproduit(string $nomproduit): self
    {
        $this->nomproduit = $nomproduit;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantityproduit(): ?int
    {
        return $this->quantityproduit;
    }

    public function setQuantityproduit(int $quantityproduit): self
    {
        $this->quantityproduit = $quantityproduit;

        return $this;
    }

    public function isDispoproduit(): ?bool
    {
        return $this->dispoproduit;
    }

    public function setDispoproduit(bool $dispoproduit): self
    {
        $this->dispoproduit = $dispoproduit;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->addLignecommande($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            $commande->removeLignecommande($this);
        }

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function setImgFile(File $imgFile): self
    {
        $this->imgFile = $imgFile;
        if(null !== $imgFile){
            $this->updated_at = new \DateTimeImmutable();
        }
        return $this;
     }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }





}
