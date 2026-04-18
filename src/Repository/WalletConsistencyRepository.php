<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class WalletConsistencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @return list<array{wallet_id:int,member_id:int,stored_balance:int,computed_balance:int,diff:int}>
     */
    public function findWalletBalanceMismatches(int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('w')
            ->select(
                'w.id AS walletId',
                'm.id AS memberId',
                'w.balance AS storedBalance',
                'COALESCE(SUM(p.pointAmount), 0) AS computedBalance'
            )
            ->innerJoin('w.member', 'm')
            ->leftJoin('w.points', 'p')
            ->groupBy('w.id, m.id, w.balance')
            ->having('w.balance <> COALESCE(SUM(p.pointAmount), 0)')
            ->orderBy('w.id', 'ASC')
            ->setMaxResults($limit);

        $rows = $qb->getQuery()->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $stored = (int) $row['storedBalance'];
            $computed = (int) $row['computedBalance'];

            $result[] = [
                'wallet_id' => (int) $row['walletId'],
                'member_id' => (int) $row['memberId'],
                'stored_balance' => $stored,
                'computed_balance' => $computed,
                'diff' => $computed - $stored,
            ];
        }

        return $result;
    }
}
