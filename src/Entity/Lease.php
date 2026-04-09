<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Lease
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Property::class)]
    private ?Property $property = null;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Tenant $tenant = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $rentAmount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getRentAmount(): string
    {
        return $this->rentAmount;
    }

    public function setRentAmount(string $rentAmount): self
    {
        $this->rentAmount = $rentAmount;

        return $this;
    }

    public function __toString(): string
    {
        $property = $this->property?->getTitle() ?? 'Bien inconnu';
        $start = $this->startDate->format('d/m/Y');
        return sprintf('%s — %s€/mois (dès le %s)', $property, $this->rentAmount, $start);
    }
}
