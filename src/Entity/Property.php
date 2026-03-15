<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    public const TYPES = [
        'appartement' => 'Appartement',
        'maison'      => 'Maison',
        'studio'      => 'Studio',
        'parking'     => 'Parking',
        'commercial'  => 'Local commercial',
        'autre'       => 'Autre',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $address;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $surface = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $rentAmount = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $chargesAmount = null;

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getAddress(): string { return $this->address; }
    public function setAddress(string $address): self { $this->address = $address; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): self { $this->city = $city; return $this; }

    public function getZipcode(): ?string { return $this->zipcode; }
    public function setZipcode(?string $zipcode): self { $this->zipcode = $zipcode; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getSurface(): ?string { return $this->surface; }
    public function setSurface(?string $surface): self { $this->surface = $surface; return $this; }

    public function getRentAmount(): ?string { return $this->rentAmount; }
    public function setRentAmount(?string $rentAmount): self { $this->rentAmount = $rentAmount; return $this; }

    public function getChargesAmount(): ?string { return $this->chargesAmount; }
    public function setChargesAmount(?string $chargesAmount): self { $this->chargesAmount = $chargesAmount; return $this; }

    public function getTotalRent(): ?float
    {
        if ($this->rentAmount === null) {
            return null;
        }
        return (float)$this->rentAmount + (float)($this->chargesAmount ?? 0);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ($this->type ?? '—');
    }
}
