<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ModelQuestionnaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ModelQuestionnaireAutosaveController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/model-questionnaire/{id}/autosave', name: 'admin_model_questionnaire_autosave', methods: ['POST'])]
    public function autosave(ModelQuestionnaire $questionnaire, Request $request): JsonResponse
    {
        $payload = $request->toArray();
        $field = $payload['field'] ?? null;
        $data = $payload['data'] ?? null;

        if (!$field || !property_exists($questionnaire, $field)) {
            return $this->json(['error' => 'Invalid field'], Response::HTTP_BAD_REQUEST);
        }

        if ('questionsScore' === $field) {
            $questionnaire->setQuestionsScore($data);
        } elseif ('questionsDisposition' === $field) {
            $questionnaire->setQuestionsDisposition($data);
        } else {
            return $this->json(['error' => 'Field not allowed for autosave'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->flush();

        $parisTime = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        return $this->json(['status' => 'saved', 'timestamp' => $parisTime->format('H:i:s')]);
    }
}
