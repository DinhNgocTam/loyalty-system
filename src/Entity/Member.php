<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "member")]
#[ORM\Index(name: "idx_member_created_at", columns: ["created_at"])]
#[ORM\UniqueConstraint(name: "uniq_member_email", columns: ["email"])]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "fullname", type: "string", length: 150)]
    private string $fullname;

    #[ORM\Column(name: "email", type: "string", length: 180, unique: true)]
    private string $email;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToOne(mappedBy: "member", targetEntity: Wallet::class, cascade: ["persist", "remove"])]
    private ?Wallet $wallet = null;

    /** @var Collection<int, Transaction> */
    #[ORM\OneToMany(mappedBy: "member", targetEntity: Transaction::class)]
    private Collection $transactions;

    /** @var Collection<int, Redemption> */
    #[ORM\OneToMany(mappedBy: "member", targetEntity: Redemption::class)]
    private Collection $redemptions;

    public function __construct(string $fullname, string $email)
    {
        $this->fullname = $fullname;
        $this->email = $email;
        $this->createdAt = new \DateTimeImmutable();
        $this->transactions = new ArrayCollection();
        $this->redemptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        if ($wallet !== null && $wallet->getMember() !== $this) {
            $wallet->setMember($this);
        }

        return $this;
    }

    /** @return Collection<int, Transaction> */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (false === $this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setMember($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction) && $transaction->getMember() === $this) {
            $transaction->setMember(null);
        }

        return $this;
    }

    /** @return Collection<int, Redemption> */
    public function getRedemptions(): Collection
    {
        return $this->redemptions;
    }

    public function addRedemption(Redemption $redemption): self
    {
        if (false === $this->redemptions->contains($redemption)) {
            $this->redemptions->add($redemption);
            $redemption->setMember($this);
        }

        return $this;
    }

    public function removeRedemption(Redemption $redemption): self
    {
        if ($this->redemptions->removeElement($redemption) && $redemption->getMember() === $this) {
            $redemption->setMember(null);
        }

        return $this;
    }
}
