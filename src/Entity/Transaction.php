<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "transactions")]
#[ORM\Index(name: "idx_tx_member_status_created", columns: ["member_id", "status", "created_at"])]
class Transaction
{
    public const STATUS_PENDING = "pending";
    public const STATUS_SUCCESS = "success";
    public const STATUS_FAILED = "failed";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "transactions", targetEntity: Member::class)]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Member $member = null;

    #[ORM\Column(name: "amount", type: "decimal", precision: 15, scale: 2)]
    private string $amount;

    #[ORM\Column(name: "status", type: "string", length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Point> */
    #[ORM\OneToMany(mappedBy: "transaction", targetEntity: Point::class)]
    private Collection $points;

    public function __construct(string $amount)
    {
        $this->amount = $amount;
        $this->createdAt = new \DateTimeImmutable();
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

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Point> */
    public function getPoints(): Collection
    {
        return $this->points;
    }
}
