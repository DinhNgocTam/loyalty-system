<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Repository\MemberReadRepository;
use App\Repository\PointReadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MemberWalletController extends AbstractController
{
    public function __construct(
        private readonly MemberReadRepository $memberReadRepository,
        private readonly PointReadRepository $pointReadRepository
    ) {
    }

    #[Route('/api/v1/members/{member_id}/wallet', name: 'api_v1_member_wallet_show', methods: ['GET'], requirements: ['member_id' => '\\d+'])]
    public function show(string $member_id): JsonResponse
    {
        $memberId = (int) $member_id;
        if ($memberId <= 0) {
            return $this->json(['message' => 'member_id must be a positive integer'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $memberWallet = $this->memberReadRepository->findMemberWalletSnapshot($memberId);
        if ($memberWallet === null) {
            return $this->json(['message' => 'member not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $pointLogs = $this->pointReadRepository->findLastPointLogsByMemberId($memberId, 10);

        return $this->json([
            'member' => [
                'id' => $memberWallet['id'],
                'fullname' => $memberWallet['fullname'],
                'email' => $memberWallet['email'],
                'created_at' => $memberWallet['created_at'],
            ],
            'wallet' => [
                'balance' => $memberWallet['wallet_balance'],
                'updated_at' => $memberWallet['wallet_updated_at'],
            ],
            'point_logs' => $pointLogs,
        ]);
    }
}
