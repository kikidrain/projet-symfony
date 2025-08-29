<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'crypto_transaction')]
class Transaction
{
    public const TYPE_BUY = 'buy';
    public const TYPE_SELL = 'sell';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_ADMIN_TRANSFER = 'admin_transfer'; // Pour les vols par le super admin

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crypto $crypto = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $priceAtTransaction = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $totalValue = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isVisible = null; // Pour masquer les transactions de vol

    #[ORM\ManyToOne]
    private ?User $adminUser = null; // Super admin qui a effectué l'action

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isVisible = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCrypto(): ?Crypto
    {
        return $this->crypto;
    }

    public function setCrypto(?Crypto $crypto): static
    {
        $this->crypto = $crypto;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPriceAtTransaction(): ?string
    {
        return $this->priceAtTransaction;
    }

    public function setPriceAtTransaction(string $priceAtTransaction): static
    {
        $this->priceAtTransaction = $priceAtTransaction;
        return $this;
    }

    public function getTotalValue(): ?string
    {
        return $this->totalValue;
    }

    public function setTotalValue(string $totalValue): static
    {
        $this->totalValue = $totalValue;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function getAdminUser(): ?User
    {
        return $this->adminUser;
    }

    public function setAdminUser(?User $adminUser): static
    {
        $this->adminUser = $adminUser;
        return $this;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_BUY => 'Achat',
            self::TYPE_SELL => 'Vente',
            self::TYPE_TRANSFER_IN => 'Transfert entrant',
            self::TYPE_TRANSFER_OUT => 'Transfert sortant',
            self::TYPE_ADMIN_TRANSFER => 'Transfert administrateur',
            default => 'Inconnu'
        };
    }
}
