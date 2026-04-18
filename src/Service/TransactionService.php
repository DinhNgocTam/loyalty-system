<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use App\Entity\Point;
use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class TransactionService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array{transaction_id:int,member_id:int,amount:string,points_awarded:int,wallet_balance:int,status:string}
     */
    public function createTransaction(int $memberId, string $amount): array
    {
        if (false === $this->isStrictPositiveDecimal($amount)) {
            throw new \InvalidArgumentException('amount must be a decimal greater than 0');
        }

        $amount = $this->normalizeAmount($amount);
        $points = $this->calculatePoints($amount);

        if ($points <= 0) {
            throw new \InvalidArgumentException('amount is too small to generate a positive point value');
        }

        return $this->entityManager->wrapInTransaction(function () use ($memberId, $amount, $points): array {
            $member = $this->entityManager->getRepository(Member::class)->find($memberId);
            if (false === ($member instanceof Member)) {
                throw new \DomainException('member not found');
            }

            $wallet = $member->getWallet();
            if (false === ($wallet instanceof Wallet)) {
                $wallet = new Wallet();
                $wallet->setMember($member);
                $this->entityManager->persist($wallet);
                $this->entityManager->flush();
            }

            $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_WRITE);

            $transaction = new Transaction($amount);
            $transaction->setMember($member);
            $transaction->setStatus(Transaction::STATUS_SUCCESS);

            $point = new Point($points, sprintf('Earned from transaction amount %s', $amount));
            $point->setTransaction($transaction);
            $wallet->addPoint($point);

            $this->entityManager->persist($transaction);
            $this->entityManager->persist($point);
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return [
                'transaction_id' => (int) $transaction->getId(),
                'member_id' => (int) $member->getId(),
                'amount' => $transaction->getAmount(),
                'points_awarded' => $points,
                'wallet_balance' => $wallet->getBalance(),
                'status' => $transaction->getStatus(),
            ];
        });
    }

    private function normalizeAmount(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function isStrictPositiveDecimal(string $amount): bool
    {
        if (1 !== preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            return false;
        }

        return (float) $amount > 0;
    }

    private function calculatePoints(string $amount): int
    {
        return (int) floor((float) $amount * 0.01);
    }
}
