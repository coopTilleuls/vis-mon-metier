<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Admin\QuestionnaireCrudController;
use App\Entity\Questionnaire;
use App\Entity\Roles;
use App\Form\Dto\Step\ReponsesDisposition;
use App\Form\Dto\Step\ReponsesScore;
use App\Form\Type\Step\ReponsesDispositionType;
use App\Form\Type\Step\ReponsesScoreType;
use App\Security\Voter\QuestionnaireVoter;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuestionnaireAnswersController extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/{id}/edit-answers', name: 'admin_questionnaire_edit_answers')]
    #[IsGranted(QuestionnaireVoter::EDIT_ANSWERS, subject: 'questionnaire')]
    public function editAnswers(Questionnaire $questionnaire, Request $request, EntityManagerInterface $em): Response
    {
        $backUrl = $this->adminUrlGenerator
            ->setController(QuestionnaireCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        $builder = $this->createFormBuilder($questionnaire);

        if (Type::EMPLOYEUR === $questionnaire->getType()) {
            $builder->add('metier', TextType::class, [
                'label' => 'Métier',
                'required' => false,
            ]);
            $builder->add('nombreImmersion', IntegerType::class, [
                'label' => 'Nombre d\'immersions',
                'required' => true,
            ]);
            $builder->add('immersionMemeDate', CheckboxType::class, [
                'label' => 'Plusieurs journées d\'immersions prévues à la même date ?',
                'required' => false,
            ]);
        }

        $builder->add('reponsesDisposition', ReponsesDispositionType::class, [
            'label' => 'Réponses Disposition',
            QuestionnaireController::SESSION_ENTITY => $questionnaire->getSession(),
            QuestionnaireController::IS_PRO => (Type::EMPLOYEUR === $questionnaire->getType()),
        ]);

        $builder->get('reponsesDisposition')->addModelTransformer(new CallbackTransformer(
            function ($reponsesAsArray) {
                $dto = new ReponsesDisposition();
                if (is_array($reponsesAsArray)) {
                    $dto->reponses = $reponsesAsArray;
                }

                return $dto;
            },
            function ($reponsesAsDto) {
                return $reponsesAsDto->reponses;
            }
        ));

        $builder->add('reponsesScore', ReponsesScoreType::class, [
            'label' => 'Réponses Score',
            QuestionnaireController::SESSION_ENTITY => $questionnaire->getSession(),
            QuestionnaireController::IS_PRO => (Type::EMPLOYEUR === $questionnaire->getType()),
        ]);

        $builder->get('reponsesScore')->addModelTransformer(new CallbackTransformer(
            function ($reponsesAsArray) {
                $dto = new ReponsesScore();
                if (is_array($reponsesAsArray)) {
                    $dto->reponses = $reponsesAsArray;
                }

                return $dto;
            },
            function ($reponsesAsDto) {
                return $reponsesAsDto->reponses;
            }
        ));

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les réponses du questionnaire ont été mises à jour avec succès.');

            if ($this->isGranted(Roles::REFERENT) && !$this->isGranted(Roles::ADMIN)) {
                return $this->redirect($this->adminUrlGenerator
                    ->setController(QuestionnaireCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($questionnaire->getId())
                    ->generateUrl());
            }

            return $this->redirect($backUrl);
        }

        return $this->render('admin/edit_answers.html.twig', [
            'form' => $form->createView(),
            'questionnaire' => $questionnaire,
            'backUrl' => $backUrl,
        ]);
    }
}
