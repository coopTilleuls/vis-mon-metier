<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Session;
use App\Form\Dto\Questionnaire as QuestionnaireDto;
use App\Form\Dto\Step\Contact as ContactDto;
use App\Form\Dto\Step\ReponsesDisposition;
use App\Form\Dto\Step\ReponsesScore;
use App\Form\Type\QuestionnaireType;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Flow\FormFlowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\String\u;

class QuestionnaireController extends AbstractController
{
    public const string SESSION_ENTITY = 'session_entity';
    public const string IS_PRO = 'is_pro';
    private const string SESSION_KEY_QUESTIONNAIRE_ID = 'latest_questionnaire_id';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    #[Route('/questionnaire', name: 'app_questionnaire_new')]
    public function index(Request $request): Response
    {
        $questionnaireData = new QuestionnaireDto();
        $currentSession = $this->entityManager->getRepository(Session::class)->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);
        $isPro = $request->query->has('pro');

        if (!$currentSession || !$currentSession->getModelQuestionnaire()) {
            return $this->render('form/questionnaire/no_session.html.twig');
        }

        if ($isPro) {
            $this->handlePrefill($request, $questionnaireData);
        }

        /** @var FormFlowInterface $flow */
        $flow = $this->createForm(QuestionnaireType::class, $questionnaireData, [
            self::SESSION_ENTITY => $currentSession,
            self::IS_PRO => $isPro,
        ])
            ->handleRequest($request);

        if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
            $questionnaire = $this->mapDtoToEntity($currentSession, $flow->getData(), $isPro);

            $params = [];
            if ($isPro) {
                $request->getSession()->set(self::SESSION_KEY_QUESTIONNAIRE_ID, $questionnaire->getId());
                $params['pro'] = 1;
            }

            return $this->redirectToRoute('app_questionnaire_success', $params);
        }
        /** @var ?ModelQuestionnaire $modelQuestionnaire */
        $modelQuestionnaire = $currentSession->getModelQuestionnaire();
        $introKey = $isPro ? 'introduction_employeur' : 'introduction_participant';
        $introductionScore = $modelQuestionnaire?->getQuestionsScore()[$introKey] ?? null;
        $introductionDisposition = $modelQuestionnaire?->getQuestionsDisposition()[$introKey] ?? null;

        return $this->render('form/questionnaire/form_flow.html.twig', [
            'form' => $flow->getStepForm()->createView(),
            'currentStep' => $questionnaireData->currentStep,
            'data' => $questionnaireData,
            'introductionScore' => $introductionScore,
            'introductionDisposition' => $introductionDisposition,
        ]);
    }

    #[Route('/questionnaire/restart', name: 'app_questionnaire_restart', methods: ['POST'])]
    public function restart(Request $request): Response
    {
        if ($questionnaireId = $request->request->get('questionnaire_id')) {
            $request->getSession()->set(self::SESSION_KEY_QUESTIONNAIRE_ID, $questionnaireId);
        }

        $params = [];
        if ($request->request->get('pro')) {
            $params['pro'] = 1;
        }

        return $this->redirectToRoute('app_questionnaire_new', $params);
    }

    #[Route('/questionnaire/succes', name: 'app_questionnaire_success')]
    public function success(Request $request): Response
    {
        $session = $request->getSession();

        $questionnaireId = $session->get(self::SESSION_KEY_QUESTIONNAIRE_ID);
        $session->remove(self::SESSION_KEY_QUESTIONNAIRE_ID);

        return $this->render('questionnaire_success.html.twig', [
            'questionnaire_id' => $questionnaireId,
        ]);
    }

    private function handlePrefill(Request $request, QuestionnaireDto $questionnaireData): void
    {
        if (!$request->query->has('pro')) {
            return;
        }

        $session = $request->getSession();
        $questionnaireId = $session->get(self::SESSION_KEY_QUESTIONNAIRE_ID);

        $previousQuestionnaire = null;
        $previousContact = null;

        if ($questionnaireId) {
            $previousQuestionnaire = $this->entityManager->getRepository(Questionnaire::class)->find($questionnaireId);
            $previousContact = $previousQuestionnaire?->getContact();
        }

        if ($previousContact) {
            $questionnaireData->contact = $this->mapEntityToContactDto($previousContact);

            $questionnaireData->contact->metier = $previousQuestionnaire->getMetier();
            $questionnaireData->contact->nombreImmersion = $previousQuestionnaire->getNombreImmersion();
            $questionnaireData->contact->immersionMemeDate = null !== $previousQuestionnaire->getImmersionMemeDate()
                ? ($previousQuestionnaire->getImmersionMemeDate() ? '1' : '0')
                : null;

            $questionnaireData->reponsesScore = new ReponsesScore();
            $questionnaireData->reponsesScore->reponses = $previousQuestionnaire->getReponsesScore();

            $questionnaireData->reponsesDisposition = new ReponsesDisposition();
            $questionnaireData->reponsesDisposition->reponses = $previousQuestionnaire->getReponsesDisposition();
        }
    }

    private function mapEntityToContactDto(Contact $contact): ContactDto
    {
        $dto = new ContactDto();

        $dto->nomEntreprise = $contact->getNomEntreprise();
        $dto->secteurActivite = $contact->getSecteurActivite();
        $dto->nombreEmployes = $contact->getNombreEmployes();
        $dto->prenom = $contact->getPrenom();
        $dto->nomFamille = $contact->getNomFamille();
        $dto->email = $contact->getEmail();
        $dto->numeroTelephone = $contact->getNumeroTelephone();
        $dto->commune = $contact->getCommune();
        $dto->prenomTuteur = $contact->getPrenomTuteur();
        $dto->nomFamilleTuteur = $contact->getNomFamilleTuteur();
        $dto->situation = $contact->getSituation();
        $dto->trancheAge = $contact->getTrancheAge();
        $dto->referent = $contact->getReferent();
        $dto->siret = $contact->getSiret();
        $dto->horaires = $contact->getHoraires();
        $dto->epiDisponible = null !== $contact->getEpiDisponible() ? ($contact->getEpiDisponible() ? '1' : '0') : null;
        $dto->autresMesuresPrevention = null !== $contact->getAutresMesuresPrevention() ? ($contact->getAutresMesuresPrevention() ? '1' : '0') : null;
        $dto->precisionMesuresPrevention = $contact->getPrecisionMesuresPrevention();

        return $dto;
    }

    private function mapDtoToEntity(Session $session, QuestionnaireDto $questionnaireData, bool $isPro): Questionnaire
    {
        $questionnaire = new Questionnaire();

        $contact = $this->objectMapper->map($questionnaireData->contact, new Contact());

        if ('Autre' === $questionnaireData->contact->commune && !empty($questionnaireData->contact->communeAutre)) {
            $contact->setCommune(u($questionnaireData->contact->communeAutre)->trim()->collapseWhitespace()->lower()->title()->toString());
        }

        $questionnaire->setSession($session);
        $questionnaire->setContact($contact);
        $questionnaire->setType($isPro ? Type::EMPLOYEUR : Type::PARTICIPANT);
        $questionnaire->setMetier($isPro ? $questionnaireData->contact->metier : null);
        $questionnaire->setNombreImmersion($isPro ? $questionnaireData->contact->nombreImmersion : 1);
        $questionnaire->setImmersionMemeDate(
            $isPro && null !== $questionnaireData->contact->immersionMemeDate
                ? '1' === $questionnaireData->contact->immersionMemeDate
                : null
        );
        $referent = $questionnaireData->contact->referent;
        if ($referent?->getId()) {
            $referent = $this->entityManager->getRepository(Referent::class)->find($referent->getId());
        }
        $contact->setReferent($referent);
        $contact->setEpiDisponible($isPro && null !== $questionnaireData->contact->epiDisponible ? '1' === $questionnaireData->contact->epiDisponible : null);
        $contact->setAutresMesuresPrevention($isPro && null !== $questionnaireData->contact->autresMesuresPrevention ? '1' === $questionnaireData->contact->autresMesuresPrevention : null);
        $questionnaire->setReponsesDisposition($questionnaireData->reponsesDisposition->reponses);
        $questionnaire->setReponsesScore($questionnaireData->reponsesScore->reponses);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($questionnaire);
        $this->entityManager->flush();

        return $questionnaire;
    }
}
