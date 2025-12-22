<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Questionnaire;
use App\Entity\Session;
use App\Form\Dto\Questionnaire as QuestionnaireDto;
use App\Form\Type\QuestionnaireType;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Flow\FormFlowInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

class QuestionnaireController extends AbstractController
{
    public const string SESSION_ENTITY = 'session_entity';
    public const string IS_PRO = 'is_pro';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    #[Route('/', name: 'app_questionnaire_new')]
    public function index(Request $request): Response
    {
        $questionnaireData = new QuestionnaireDto();
        $currentSession = $this->entityManager->getRepository(Session::class)->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);
        $isPro = $request->query->has('pro');

        if (!$currentSession) {
            throw new BadRequestException('Invalid or missing session.');
        }
        /** @var FormFlowInterface $flow */
        $flow = $this->createForm(QuestionnaireType::class, $questionnaireData, [
            self::SESSION_ENTITY => $currentSession,
            self::IS_PRO => $isPro,
        ])
            ->handleRequest($request);

        if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
            $this->mapDtoToEntity($currentSession, $flow->getData(), $isPro);

            return $this->redirectToRoute('app_questionnaire_success');
        }

        return $this->render('form/questionnaire/form_flow.html.twig', [
            'form' => $flow->getStepForm(),
            'currentStep' => $questionnaireData->currentStep,
            'data' => $questionnaireData,
        ]);
    }

    #[Route('/succes', name: 'app_questionnaire_success')]
    public function success(): Response
    {
        return $this->render('questionnaire_success.html.twig');
    }

    private function mapDtoToEntity(Session $session, QuestionnaireDto $questionnaireData, bool $isPro): void
    {
        $questionnaire = new Questionnaire();
        $contact = $this->objectMapper->map($questionnaireData->contact, new Contact());

        $questionnaire->setSession($session);
        $questionnaire->setContact($contact);
        $questionnaire->setType($isPro ? Type::EMPLOYEUR : Type::PARTICIPANT);
        $questionnaire->setMetier($isPro ? $questionnaireData->contact->metier : null);
        $questionnaire->setReponsesDisposition($questionnaireData->reponsesDisposition->reponses);
        $questionnaire->setReponsesScore($questionnaireData->reponsesScore->reponses);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($questionnaire);
        $this->entityManager->flush();
    }
}
