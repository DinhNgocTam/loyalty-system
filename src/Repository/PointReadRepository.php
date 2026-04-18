<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PointReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Point::class);
    }

    /**
     * @return list<array{id:int,point_amount:int,description:string,created_at:string,transaction_id:?int,redemption_id:?int}>
     */
    public function findLastPointLogsByMemberId(int $memberId, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select(
                'p.id AS id',
                'p.pointAmount AS pointAmount',
                'p.description AS description',
                'p.createdAt AS createdAt',
                'IDENTITY(p.transaction) AS transactionId',
                'IDENTITY(p.redemption) AS redemptionId'
            )
            ->innerJoin('p.wallet', 'w')
            ->innerJoin('w.member', 'm')
            ->where('m.id = :memberId')
            ->setParameter('memberId', $memberId)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit);

        $rows = $qb->getQuery()->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $createdAt = $row['createdAt'];
            $result[] = [
                'id' => (int) $row['id'],
                'point_amount' => (int) $row['pointAmount'],
                'description' => (string) $row['description'],
                'created_at' => $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : '',
                'transaction_id' => isset($row['transactionId']) ? (int) $row['transactionId'] : null,
                'redemption_id' => isset($row['redemptionId']) ? (int) $row['redemptionId'] : null,
            ];
        }

        return $result;
    }
}
