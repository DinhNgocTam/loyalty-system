<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "redemption")]
#[ORM\Index(name: "idx_redemption_member_status_created", columns: ["member_id", "status", "created_at"])]
#[ORM\Index(name: "idx_redemption_gift_id", columns: ["gift_id"])]
class Redemption
{
    public const STATUS_PENDING = "pending";
    public const STATUS_APPROVED = "approved";
    public const STATUS_REJECTED = "rejected";
    public const STATUS_FULFILLED = "fulfilled";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "redemptions", targetEntity: Member::class)]
    #[ORM\JoinColumn(name: "member_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Member $member = null;

    #[ORM\ManyToOne(inversedBy: "redemptions", targetEntity: Gift::class)]
    #[ORM\JoinColumn(name: "gift_id", referencedColumnName: "id", nullable: false, onDelete: "RESTRICT")]
    private ?Gift $gift = null;

    #[ORM\Column(name: "points_used", type: "integer")]
    private int $pointsUsed;

    #[ORM\Column(name: "status", type: "string", length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Point> */
    #[ORM\OneToMany(mappedBy: "redemption", targetEntity: Point::class)]
    private Collection $points;

    public function __construct(int $pointsUsed)
    {
        $this->pointsUsed = $pointsUsed;
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

    public function getGift(): ?Gift
    {
        return $this->gift;
    }

    public function setGift(?Gift $gift): self
    {
        $this->gift = $gift;

        return $this;
    }

    public function getPointsUsed(): int
    {
        return $this->pointsUsed;
    }

    public function setPointsUsed(int $pointsUsed): self
    {
        $this->pointsUsed = $pointsUsed;

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
