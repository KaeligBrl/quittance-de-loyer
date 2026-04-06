<?php

namespace App\Entity;

use App\Repository\RentReceiptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RentReceiptRepository::class)]
class RentReceipt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Tenant $tenant = null;

    #[ORM\Column(length: 100)]
    private string $number;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $periodStart;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $periodEnd;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $issuedAt;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'rentReceipt')]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTenant(): ?Tenant { return $this->tenant; }
    public function setTenant(?Tenant $tenant): self { $this->tenant = $tenant; return $this; }

    public function getNumber(): string { return $this->number; }
    public function setNumber(string $number): self { $this->number = $number; return $this; }

    public function getPeriodStart(): \DateTimeInterface { return $this->periodStart; }
    public function setPeriodStart(\DateTimeInterface $periodStart): self { $this->periodStart = $periodStart; return $this; }

    public function getPeriodEnd(): \DateTimeInterface { return $this->periodEnd; }
    public function setPeriodEnd(\DateTimeInterface $periodEnd): self { $this->periodEnd = $periodEnd; return $this; }

    public function getAmount(): string { return $this->amount; }
    public function setAmount(string $amount): self { $this->amount = $amount; return $this; }

    public function getIssuedAt(): \DateTimeInterface { return $this->issuedAt; }
    public function setIssuedAt(\DateTimeInterface $issuedAt): self { $this->issuedAt = $issuedAt; return $this; }

    public function getPayments(): Collection { return $this->payments; }

    public function isPaid(): bool
    {
        return $this->payments->count() > 0;
    }

    public function getPaidAmount(): float
    {
        $total = 0.0;
        foreach ($this->payments as $p) {
            $total += (float) $p->getAmount();
        }
        return $total;
    }
}
