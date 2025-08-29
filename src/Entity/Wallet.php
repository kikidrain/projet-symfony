<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wallets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'wallets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crypto $crypto = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $balance = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->balance = '0.00000000';
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

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        $this->balance = $balance;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function addToBalance(string $amount): static
    {
        $this->balance = bcadd($this->balance, $amount, 8);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function subtractFromBalance(string $amount): static
    {
        $this->balance = bcsub($this->balance, $amount, 8);
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getBalanceValue(): float
    {
        return (float) $this->balance;
    }

    public function getCurrentValue(): float
    {
        if ($this->crypto && $this->crypto->getCurrentPrice()) {
            return $this->getBalanceValue() * (float) $this->crypto->getCurrentPrice();
        }
        return 0;
    }
}
