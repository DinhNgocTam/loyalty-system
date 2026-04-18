<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\RedemptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RedemptionController extends AbstractController
{
    public function __construct(private readonly RedemptionService $redemptionService)
    {
    }

    #[Route('/api/v1/redemptions', name: 'api_v1_redemptions_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'Invalid JSON payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $memberId = $payload['member_id'] ?? null;
        $giftId = $payload['gift_id'] ?? null;

        $memberIsInt = is_int($memberId) || ctype_digit((string) $memberId);
        $giftIsInt = is_int($giftId) || ctype_digit((string) $giftId);

        if (!$memberIsInt) {
            return $this->json(['message' => 'member_id must be an integer'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$giftIsInt) {
            return $this->json(['message' => 'gift_id must be an integer'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->redemptionService->createRedemption((int) $memberId, (int) $giftId);

            return $this->json($result, JsonResponse::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (\Throwable $e) {
            return $this->json(
                ['message' => 'Unable to create redemption atomically', 'error' => $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
