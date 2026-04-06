<?php

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
class Tenant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\ManyToOne(targetEntity: Property::class, inversedBy: null)]
    private ?Property $property = null;

    #[ORM\OneToMany(targetEntity: TenantFile::class, mappedBy: 'tenant', cascade: ['remove'])]
    #[ORM\OrderBy(['uploadedAt' => 'DESC'])]
    private Collection $files;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true, unique: true)]
    private ?string $invitationToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $invitationTokenExpiresAt = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }

    public function getProperty(): ?Property { return $this->property; }
    public function setProperty(?Property $property): self { $this->property = $property; return $this; }

    public function getFiles(): Collection { return $this->files; }

    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getRoles(): array { return ['ROLE_USER']; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function eraseCredentials(): void {}

    public function getInvitationToken(): ?string { return $this->invitationToken; }
    public function setInvitationToken(?string $invitationToken): self { $this->invitationToken = $invitationToken; return $this; }

    public function getInvitationTokenExpiresAt(): ?\DateTimeImmutable { return $this->invitationTokenExpiresAt; }
    public function setInvitationTokenExpiresAt(?\DateTimeImmutable $invitationTokenExpiresAt): self { $this->invitationTokenExpiresAt = $invitationTokenExpiresAt; return $this; }

    public function isInvitationTokenValid(): bool
    {
        return $this->invitationToken !== null
            && $this->invitationTokenExpiresAt !== null
            && $this->invitationTokenExpiresAt > new \DateTimeImmutable();
    }

    public function generateInvitationToken(): void
    {
        $this->invitationToken = bin2hex(random_bytes(32));
        $this->invitationTokenExpiresAt = new \DateTimeImmutable('+72 hours');
    }
}
