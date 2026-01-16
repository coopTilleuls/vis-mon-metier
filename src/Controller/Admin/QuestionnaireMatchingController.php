<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Appariement;
use App\Entity\Questionnaire;
use App\Entity\Roles;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Roles::REFERENT)]
class QuestionnaireMatchingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/match/{sourceId}/{targetId}/{date}', name: 'admin_questionnaire_match')]
    public function match(int $sourceId, int $targetId, string $date, Request $request): Response
    {
        $source = $this->entityManager->getRepository(Questionnaire::class)->find($sourceId);
        $target = $this->entityManager->getRepository(Questionnaire::class)->find($targetId);

        if (!$source || !$target) {
            $this->addFlash('danger', 'L\'un des questionnaires est introuvable. Impossible de matcher.');

            return $this->redirectBack($request, $sourceId);
        }

        $participant = null;
        $employer = null;

        if (Type::PARTICIPANT === $source->getType()) {
            $participant = $source;
        } elseif (Type::EMPLOYEUR === $source->getType()) {
            $employer = $source;
        }

        if (Type::PARTICIPANT === $target->getType()) {
            $participant = $target;
        } elseif (Type::EMPLOYEUR === $target->getType()) {
            $employer = $target;
        }

        if (!$participant || !$employer) {
            $this->addFlash('danger', 'Impossible de matcher deux profils du même type.');

            return $this->redirectBack($request, $sourceId);
        }

        try {
            $dateFormatted = str_replace('-', '/', $date);
            $currentYear = new \DateTime()->format('Y');

            if (str_contains($dateFormatted, '/')) {
                $dateObj = \DateTimeImmutable::createFromFormat('!d/m/Y', $dateFormatted.'/'.$currentYear);
            } else {
                $dateObj = new \DateTimeImmutable($dateFormatted);
            }

            if (!$dateObj) {
                throw new \Exception('Date invalide.');
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Format de date invalide.');

            return $this->redirectBack($request, $sourceId);
        }

        $repo = $this->entityManager->getRepository(Appariement::class);

        $existingPair = $repo->findOneBy([
            'participant' => $participant,
            'entreprise' => $employer,
        ]);

        if ($existingPair) {
            $this->addFlash('danger', 'Pour ces deux profils une immersion est déjà prévue (le '.$existingPair->getDateMatch()->format('d/m/Y').').');

            return $this->redirectBack($request, $sourceId);
        }

        $participantBusy = $repo->findOneBy([
            'participant' => $participant,
            'dateMatch' => $dateObj,
        ]);

        if ($participantBusy) {
            $this->addFlash('danger', 'Le participant a déjà une immersion prévue à cette date.');

            return $this->redirectBack($request, $sourceId);
        }

        $employerBusy = $repo->findOneBy([
            'entreprise' => $employer,
            'dateMatch' => $dateObj,
        ]);

        if ($employerBusy) {
            $this->addFlash('danger', 'L\'employeur a déjà une immersion prévue à cette date.');

            return $this->redirectBack($request, $sourceId);
        }

        // Create the match
        $appariement = new Appariement();
        $appariement->setParticipant($participant);
        $appariement->setEntreprise($employer);
        $appariement->setDateMatch($dateObj);

        $this->entityManager->persist($appariement);
        $this->entityManager->flush();

        // Update 'appaire' flags
        $participant->setAppaire(true);
        $employer->setAppaire(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'immersion a été créée avec succès.');

        return $this->redirectBack($request, $sourceId);
    }

    #[Route('/admin/unmatch/{id}', name: 'admin_questionnaire_unmatch')]
    public function unmatch(int $id, Request $request): Response
    {
        $appariement = $this->entityManager->getRepository(Appariement::class)->find($id);

        if (!$appariement) {
            $this->addFlash('danger', 'Immersion introuvable.');
            $referer = $request->headers->get('referer');

            return $referer ? $this->redirect($referer) : $this->redirectToRoute('admin');
        }

        $participant = $appariement->getParticipant();
        $entreprise = $appariement->getEntreprise();
        $entrepriseId = $entreprise ? $entreprise->getId() : null;
        $sourceId = $participant ? $participant->getId() : $entrepriseId;

        $referer = $request->headers->get('referer');

        $this->entityManager->remove($appariement);
        $this->entityManager->flush();

        if ($participant) {
            $hasMatches = $participant->getAppariements()->count() > 0;
            $participant->setAppaire($hasMatches);
        }
        if ($entreprise) {
            $hasMatches = $entreprise->getAppariements()->count() > 0;
            $entreprise->setAppaire($hasMatches);
        }
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'appariement a été supprimé.');

        if ($referer) {
            return $this->redirect($referer);
        }

        if ($sourceId) {
            return $this->redirectBack($request, $sourceId);
        }

        return $this->redirectToRoute('admin');
    }

    private function redirectBack(Request $request, int $entityId): Response
    {
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(QuestionnaireCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entityId)
            ->generateUrl());
    }
}
