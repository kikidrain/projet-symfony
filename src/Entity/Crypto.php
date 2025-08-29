<?php

namespace App\Entity;

use App\Repository\CryptoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CryptoRepository::class)]
class Crypto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $symbol = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $currentPrice = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUpdated = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoUrl = null;

    /**
     * @var Collection<int, CryptoPriceHistory>
     */
    #[ORM\OneToMany(targetEntity: CryptoPriceHistory::class, mappedBy: 'crypto', orphanRemoval: true)]
    private Collection $priceHistory;

    /**
     * @var Collection<int, Wallet>
     */
    #[ORM\OneToMany(targetEntity: Wallet::class, mappedBy: 'crypto', orphanRemoval: true)]
    private Collection $wallets;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'crypto', orphanRemoval: true)]
    private Collection $transactions;

    public function __construct()
    {
        $this->priceHistory = new ArrayCollection();
        $this->wallets = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function getCurrentPrice(): ?string
    {
        return $this->currentPrice;
    }

    public function setCurrentPrice(string $currentPrice): static
    {
        $this->currentPrice = $currentPrice;
        $this->lastUpdated = new \DateTimeImmutable();
        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;
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

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;
        return $this;
    }

    /**
     * @return Collection<int, CryptoPriceHistory>
     */
    public function getPriceHistory(): Collection
    {
        return $this->priceHistory;
    }

    public function addPriceHistory(CryptoPriceHistory $priceHistory): static
    {
        if (!$this->priceHistory->contains($priceHistory)) {
            $this->priceHistory->add($priceHistory);
            $priceHistory->setCrypto($this);
        }

        return $this;
    }

    public function removePriceHistory(CryptoPriceHistory $priceHistory): static
    {
        if ($this->priceHistory->removeElement($priceHistory)) {
            // set the owning side to null (unless already changed)
            if ($priceHistory->getCrypto() === $this) {
                $priceHistory->setCrypto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function getWallets(): Collection
    {
        return $this->wallets;
    }

    public function addWallet(Wallet $wallet): static
    {
        if (!$this->wallets->contains($wallet)) {
            $this->wallets->add($wallet);
            $wallet->setCrypto($this);
        }

        return $this;
    }

    public function removeWallet(Wallet $wallet): static
    {
        if ($this->wallets->removeElement($wallet)) {
            // set the owning side to null (unless already changed)
            if ($wallet->getCrypto() === $this) {
                $wallet->setCrypto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setCrypto($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCrypto() === $this) {
                $transaction->setCrypto(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->symbol . ' - ' . $this->name;
    }
}
