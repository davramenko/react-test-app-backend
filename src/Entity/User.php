<?php

/**
 * @noinspection PhpUnused
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\Auth\CheckEmailAvailableController;
use App\Controller\Auth\RegisterUserController;
use App\Controller\Auth\VerifyEmailAddressController;
use App\Repository\UserRepository;
use App\State\MeProvider;
use App\Validator as AssertCustom;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/auth/me',
            normalizationContext: ['groups' => ['user:read']],
            security: 'object == user',
            provider: MeProvider::class,
        ),
        new Post(
            uriTemplate: '/public/check-email-available',
            controller: CheckEmailAvailableController::class,
            denormalizationContext: ['groups' => ['email:check']],
        ),
        new Post(
            uriTemplate: '/public/register',
            controller: RegisterUserController::class,
            denormalizationContext: ['groups' => ['user:write', 'user:register', 'user:update_password']]
        ),
        new Post(
            uriTemplate: '/public/confirm-email',
            controller: VerifyEmailAddressController::class,
        ),
    ]
)]
#[UniqueEntity(fields: ['email'], groups: ['email:check', 'user:register'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(name: 'first_name', length: 255)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank(groups: ['user:write'])]
    #[SerializedName('first_name')]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[SerializedName('last_name')]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['user:read', 'email:check', 'user:register'])]
    #[Assert\NotBlank(groups: ['email:check', 'user:register'])]
    #[Assert\Email(groups: ['email:check', 'user:register'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[AssertCustom\CheckPassword(groups: ['user:update_password'])]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    #[SerializedName('email_verified_at')]
    private ?DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column]
    #[SerializedName('created_at')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[SerializedName('updated_at')]
    private ?DateTimeImmutable $updatedAt = null;

    #[Groups(['user:register', 'user:update_password'])]
    #[Assert\NotBlank(groups: ['user:register', 'user:update_password'])]
    #[Assert\PasswordStrength(groups: ['user:register', 'user:update_password'])]
    #[Assert\Expression(
        expression: 'this.getNewPassword() === this.getNewPasswordConfirmation()',
        message: 'New password does not match the confirmation.',
        groups: ['user:register', 'user:update_password']
    )]
    #[SerializedName('new_password')]
    private ?string $newPassword = null;

    #[Groups(['user:register', 'user:update_password'])]
    #[Assert\NotBlank(groups: ['user:register', 'user:update_password'])]
    #[SerializedName('new_password_confirmation')]
    private ?string $newPasswordConfirmation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $last_name): static
    {
        $this->lastName = $last_name;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ($this->lastName ? ' ' . $this->lastName : '');
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function isVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function setEmailVerifiedAt(?DateTimeImmutable $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function updateCreatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getNewPasswordConfirmation(): ?string
    {
        return $this->newPasswordConfirmation;
    }

    public function setNewPasswordConfirmation(string $newPasswordConfirmation): void
    {
        $this->newPasswordConfirmation = $newPasswordConfirmation;
    }

    public function __toString()
    {
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        return $serializer->serialize($this, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => [
            'password', "emailVerifiedAt", "createdAt", "updatedAt", 'userIdentifier'
        ]]);
    }
}
