<?php

namespace App\Entity;

use App\Repository\TenantFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TenantFileRepository::class)]
class TenantFile
{
    public const CATEGORIES = [
        'assurance_habitation' => 'Assurance habitation',
        'bulletin_salaire'     => 'Bulletin de salaire',
        'quittance'            => 'Preuve de quittance',
        'contrat_travail'      => "CDI / Preuve d'embauche",
        'declaration_garant'   => "Declaration sur l'honneur garant",
        'autre'                => 'Autre',
    ];

    public const REQUIRED_CATEGORIES = [
        'assurance_habitation',
        'bulletin_salaire',
        'contrat_travail',
        'declaration_garant',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tenant::class, inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Tenant $tenant;

    #[ORM\Column(length: 50)]
    private string $category;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $uploadedAt;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTenant(): Tenant { return $this->tenant; }
    public function setTenant(Tenant $tenant): self { $this->tenant = $tenant; return $this; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }
    public function getCategoryLabel(): string { return self::CATEGORIES[$this->category] ?? $this->category; }
    public function getOriginalName(): string { return $this->originalName; }
    public function setOriginalName(string $n): self { $this->originalName = $n; return $this; }
    public function getFilename(): string { return $this->filename; }
    public function setFilename(string $f): self { $this->filename = $f; return $this; }
    public function getUploadedAt(): \DateTimeImmutable { return $this->uploadedAt; }
    public function setUploadedAt(\DateTimeImmutable $dt): self { $this->uploadedAt = $dt; return $this; }
}
