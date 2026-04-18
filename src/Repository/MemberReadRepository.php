<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class MemberReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * @return array{id:int,fullname:string,email:string,created_at:string,wallet_balance:int,wallet_updated_at:?string}|null
     */
    public function findMemberWalletSnapshot(int $memberId): ?array
    {
        $qb = $this->createQueryBuilder('m')
            ->select(
                'm.id AS id',
                'm.fullname AS fullname',
                'm.email AS email',
                'm.createdAt AS createdAt',
                'w.balance AS walletBalance',
                'w.updatedAt AS walletUpdatedAt'
            )
            ->leftJoin('m.wallet', 'w')
            ->where('m.id = :memberId')
            ->setParameter('memberId', $memberId)
            ->setMaxResults(1);

        $row = $qb->getQuery()->getOneOrNullResult();
        if (!is_array($row)) {
            return null;
        }

        $createdAt = $row['createdAt'];
        $walletUpdatedAt = $row['walletUpdatedAt'] ?? null;

        return [
            'id' => (int) $row['id'],
            'fullname' => (string) $row['fullname'],
            'email' => (string) $row['email'],
            'created_at' => $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : '',
            'wallet_balance' => isset($row['walletBalance']) ? (int) $row['walletBalance'] : 0,
            'wallet_updated_at' => $walletUpdatedAt instanceof \DateTimeInterface ? $walletUpdatedAt->format(DATE_ATOM) : null,
        ];
    }
}
