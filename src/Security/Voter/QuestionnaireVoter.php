<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\User;
use App\ValueObject\Type;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Questionnaire>
 */
class QuestionnaireVoter extends Voter
{
    public const VIEW = 'QUESTIONNAIRE_VIEW';
    public const EDIT_ANSWERS = 'QUESTIONNAIRE_EDIT_ANSWERS';
    public const DELETE = 'QUESTIONNAIRE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT_ANSWERS, self::DELETE])
            && $subject instanceof Questionnaire;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (in_array(Roles::ADMIN, $user->getRoles(), true)) {
            return true;
        }

        /** @var Questionnaire $questionnaire */
        $questionnaire = $subject;

        if (in_array(Roles::LECTEUR, $user->getRoles(), true)) {
            return match ($attribute) {
                self::VIEW => true,
                default => false,
            };
        }

        return match ($attribute) {
            self::VIEW => $this->canView($questionnaire, $user),
            self::EDIT_ANSWERS => $this->canEditAnswers($questionnaire, $user),
            self::DELETE => false,
            default => false,
        };
    }

    private function canView(Questionnaire $questionnaire, User $user): bool
    {
        if (Type::EMPLOYEUR === $questionnaire->getType()) {
            return true;
        }

        $contact = $questionnaire->getContact();

        if (!$contact) {
            return false;
        }

        return $this->isLinkedToReferent($contact->getReferent(), $user);
    }

    private function canEditAnswers(Questionnaire $questionnaire, User $user): bool
    {
        if (Type::EMPLOYEUR === $questionnaire->getType()) {
            return false;
        }

        $contact = $questionnaire->getContact();

        return $contact && $this->isLinkedToReferent($contact->getReferent(), $user);
    }

    private function isLinkedToReferent(?Referent $referent, User $user): bool
    {
        return $referent && $referent->getUtilisateur() === $user;
    }
}
