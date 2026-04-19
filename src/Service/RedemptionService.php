<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Gift;
use App\Entity\Member;
use App\Entity\Point;
use App\Entity\Redemption;
use App\Entity\Wallet;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class RedemptionService
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    /**
     * @return array{redemption_id:int,member_id:int,gift_id:int,points_used:int,wallet_balance:int,gift_stock:int,status:string}
     */
    public function createRedemption(int $memberId, int $giftId): array
    {
        if ($memberId <= 0) {
            throw new \InvalidArgumentException('member_id must be a positive integer');
        }

        if ($giftId <= 0) {
            throw new \InvalidArgumentException('gift_id must be a positive integer');
        }

        return $this->entityManager->wrapInTransaction(function () use ($memberId, $giftId): array {
            $member = $this->entityManager->getRepository(Member::class)->find($memberId);
            if (!$member instanceof Member) {
                throw new \DomainException('member not found');
            }

            $gift = $this->entityManager->getRepository(Gift::class)->find($giftId);
            if (!$gift instanceof Gift) {
                throw new \DomainException('gift not found');
            }

            $wallet = $member->getWallet();
            if (!$wallet instanceof Wallet) {
                throw new \DomainException('wallet not found for member');
            }

            // Lock rows to avoid race condition for last stock / concurrent redemptions.
            $this->entityManager->lock($gift, LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_WRITE);

            if ($gift->getStock() <= 0) {
                throw new \RuntimeException('gift is out of stock');
            }

            $pointCost = $gift->getPointCost();
            if ($pointCost <= 0) {
                throw new \RuntimeException('gift point cost is invalid');
            }

            if ($wallet->getBalance() < $pointCost) {
                throw new \RuntimeException('insufficient wallet balance');
            }

            $newStock = $gift->getStock() - 1;
            if ($newStock < 0) {
                throw new \RuntimeException('negative stock is not allowed');
            }
            $gift->setStock($newStock);

            $redemption = new Redemption($pointCost);
            $redemption->setMember($member);
            $redemption->setGift($gift);
            $redemption->setStatus(Redemption::STATUS_FULFILLED);

            $negativePoint = -$pointCost;
            $point = new Point($negativePoint, sprintf('Redeem gift: %s', $gift->getGiftName()));
            $point->setRedemption($redemption);
            $wallet->addPoint($point);

            if ($wallet->getBalance() < 0) {
                throw new \RuntimeException('negative wallet balance is not allowed');
            }

            $this->entityManager->persist($gift);
            $this->entityManager->persist($redemption);
            $this->entityManager->persist($point);
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return [
                'redemption_id' => (int) $redemption->getId(),
                'member_id' => (int) $member->getId(),
                'gift_id' => (int) $gift->getId(),
                'points_used' => $pointCost,
                'wallet_balance' => $wallet->getBalance(),
                'gift_stock' => $gift->getStock(),
                'status' => $redemption->getStatus(),
            ];
        });
    }
}
