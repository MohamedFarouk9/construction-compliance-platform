<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class JsonRequestHelper
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    public function decodeBody(Request $request): array|JsonResponse
    {
        $content = trim($request->getContent());
        if ($content === '') {
            return new JsonResponse(
                ['error' => 'Request body must be valid JSON.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(
                ['error' => 'Malformed JSON payload.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        if (!\is_array($decoded)) {
            return new JsonResponse(
                ['error' => 'JSON payload must be an object.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        return $decoded;
    }

    public function validate(object $dto): ?JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (\count($violations) === 0) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return new JsonResponse(
            [
                'error' => 'Validation failed.',
                'details' => $errors,
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
