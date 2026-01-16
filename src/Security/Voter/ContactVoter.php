<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Contact;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\User;
use App\ValueObject\Type;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Contact>
 */
class ContactVoter extends Voter
{
    public const VIEW = 'CONTACT_VIEW';
    public const EDIT = 'CONTACT_EDIT';
    public const DELETE = 'CONTACT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Contact;
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

        /** @var Contact $contact */
        $contact = $subject;

        if (in_array(Roles::LECTEUR, $user->getRoles(), true)) {
            return match ($attribute) {
                self::VIEW => true,
                default => false,
            };
        }

        return match ($attribute) {
            self::VIEW => $this->canView($contact, $user),
            self::EDIT => $this->canEdit($contact, $user),
            default => false,
        };
    }

    private function canView(Contact $contact, User $user): bool
    {
        if (Type::EMPLOYEUR === $contact->getType()) {
            return true;
        }

        if ($this->isLinkedToReferent($contact, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Contact $contact, User $user): bool
    {
        if (Type::EMPLOYEUR === $contact->getType()) {
            return false;
        }

        return $this->isLinkedToReferent($contact, $user);
    }

    private function isLinkedToReferent(Contact $contact, User $user): bool
    {
        /** @var Referent|null $referent */
        $referent = $contact->getReferent();

        return $referent && $referent->getUtilisateur() === $user;
    }
}
