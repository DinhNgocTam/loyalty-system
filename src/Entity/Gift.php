<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "gift")]
#[ORM\Index(name: "idx_gift_status_stock", columns: ["status", "stock"])]
#[ORM\Index(name: "idx_gift_point_cost", columns: ["point_cost"])]
class Gift
{
    public const STATUS_ACTIVE = "active";
    public const STATUS_INACTIVE = "inactive";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "gift_name", type: "string", length: 150)]
    private string $giftName;

    #[ORM\Column(name: "point_cost", type: "integer")]
    private int $pointCost;

    #[ORM\Column(name: "stock", type: "integer")]
    private int $stock;

    #[ORM\Column(name: "status", type: "string", length: 20)]
    private string $status = self::STATUS_ACTIVE;

    /** @var Collection<int, Redemption> */
    #[ORM\OneToMany(mappedBy: "gift", targetEntity: Redemption::class)]
    private Collection $redemptions;

    public function __construct(string $giftName, int $pointCost, int $stock)
    {
        $this->giftName = $giftName;
        $this->pointCost = $pointCost;
        $this->stock = $stock;
        $this->redemptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGiftName(): string
    {
        return $this->giftName;
    }

    public function setGiftName(string $giftName): self
    {
        $this->giftName = $giftName;

        return $this;
    }

    public function getPointCost(): int
    {
        return $this->pointCost;
    }

    public function setPointCost(int $pointCost): self
    {
        $this->pointCost = $pointCost;

        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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

    /** @return Collection<int, Redemption> */
    public function getRedemptions(): Collection
    {
        return $this->redemptions;
    }
}
