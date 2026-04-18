<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Wallet;
use App\Repository\WalletConsistencyRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class WalletConsistencyService
{
    public function __construct(
        private readonly WalletConsistencyRepository $walletConsistencyRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return list<array{wallet_id:int,member_id:int,stored_balance:int,computed_balance:int,diff:int}>
     */
    public function findMismatches(int $limit = 100): array
    {
        return $this->walletConsistencyRepository->findWalletBalanceMismatches($limit);
    }

    /**
     * @return array{fixed_wallets:int,remaining_mismatches:int}
     */
    public function fixMismatches(int $limit = 100): array
    {
        $mismatches = $this->findMismatches($limit);
        if ($mismatches === []) {
            return ['fixed_wallets' => 0, 'remaining_mismatches' => 0];
        }

        $walletIds = array_map(static fn (array $row): int => (int) $row['wallet_id'], $mismatches);

        $fixed = $this->entityManager->wrapInTransaction(function () use ($walletIds): int {
            $fixedCount = 0;

            foreach ($walletIds as $walletId) {
                $wallet = $this->entityManager->find(Wallet::class, $walletId);
                if (!$wallet instanceof Wallet) {
                    continue;
                }

                // Prevent concurrent updates while recalculating current wallet balance.
                $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_WRITE);
                $wallet->recalculateBalance();
                $fixedCount++;
            }

            $this->entityManager->flush();

            return $fixedCount;
        });

        $remaining = count($this->findMismatches($limit));

        return [
            'fixed_wallets' => $fixed,
            'remaining_mismatches' => $remaining,
        ];
    }
}
