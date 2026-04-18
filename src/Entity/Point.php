<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "point")]
#[ORM\Index(name: "idx_point_wallet_created", columns: ["wallet_id", "created_at"])]
#[ORM\Index(name: "idx_point_transaction_id", columns: ["transaction_id"])]
#[ORM\Index(name: "idx_point_redemption_id", columns: ["redemption_id"])]
class Point
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "points", targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "wallet_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Wallet $wallet = null;

    #[ORM\ManyToOne(inversedBy: "points", targetEntity: Transaction::class)]
    #[ORM\JoinColumn(name: "transaction_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Transaction $transaction = null;

    #[ORM\ManyToOne(inversedBy: "points", targetEntity: Redemption::class)]
    #[ORM\JoinColumn(name: "redemption_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Redemption $redemption = null;

    #[ORM\Column(name: "point_amount", type: "integer")]
    private int $pointAmount;

    #[ORM\Column(name: "description", type: "string", length: 255)]
    private string $description;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct(int $pointAmount, string $description)
    {
        $this->pointAmount = $pointAmount;
        $this->description = $description;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getRedemption(): ?Redemption
    {
        return $this->redemption;
    }

    public function setRedemption(?Redemption $redemption): self
    {
        $this->redemption = $redemption;

        return $this;
    }

    public function getPointAmount(): int
    {
        return $this->pointAmount;
    }

    public function setPointAmount(int $pointAmount): self
    {
        $this->pointAmount = $pointAmount;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
