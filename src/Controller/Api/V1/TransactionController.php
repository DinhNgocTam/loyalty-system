<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionController extends AbstractController
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    #[Route('/api/v1/transactions', name: 'api_v1_transactions_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (false === is_array($payload)) {
            return $this->json(['message' => 'Invalid JSON payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $memberId = $payload['member_id'] ?? null;
        $amount = $payload['amount'] ?? null;

        $memberIsInt = is_int($memberId) || ctype_digit((string) $memberId);
        if (false === $memberIsInt) {
            return $this->json(['message' => 'member_id must be an integer'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (false === is_scalar($amount)) {
            return $this->json(['message' => 'amount must be a decimal number'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->transactionService->createTransaction((int) $memberId, (string) $amount);

            return $this->json($result, JsonResponse::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return $this->json(
                ['message' => 'Unable to create transaction atomically', 'error' => $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
