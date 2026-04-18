<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "wallet")]
#[ORM\Index(name: "idx_wallet_updated_at", columns: ["updated_at"])]
#[ORM\HasLifecycleCallbacks]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: "wallet", targetEntity: Member::class)]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", nullable: false, unique: true, onDelete: "CASCADE")]
    private ?Member $member = null;

    #[ORM\Column(name: "balance", type: "integer", options: ["default" => 0])]
    private int $balance = 0;

    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Point> */
    #[ORM\OneToMany(mappedBy: "wallet", targetEntity: Point::class, cascade: ["persist"], orphanRemoval: true)]
    private Collection $points;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        if ($member !== null && $member->getWallet() !== $this) {
            $member->setWallet($this);
        }

        return $this;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, Point> */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (false === $this->points->contains($point)) {
            $this->points->add($point);
            $point->setWallet($this);
            $this->balance += $point->getPointAmount();
            $this->touch();
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->removeElement($point)) {
            if ($point->getWallet() === $this) {
                $point->setWallet(null);
            }

            $this->balance -= $point->getPointAmount();
            $this->touch();
        }

        return $this;
    }

    public function recalculateBalance(): self
    {
        $this->balance = 0;

        foreach ($this->points as $point) {
            $this->balance += $point->getPointAmount();
        }

        $this->touch();

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function keepBalanceConsistent(): void
    {
        $this->recalculateBalance();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
