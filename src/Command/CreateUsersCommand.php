<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Roles;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Crée les comptes ADMIN, REFERENT et LECTEUR.',
)]
class CreateUsersCommand extends Command
{
    private const array DEFAULT_USERS = [
        'admin@vis-mon-metier.fr' => Roles::ADMIN,
        'referent@vis-mon-metier.fr' => Roles::REFERENT,
        'lecteur@vis-mon-metier.fr' => Roles::LECTEUR,
    ];

    private const int MIN_PASSWORD_LENGTH = 12;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'password',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf('Mot de passe appliqué aux comptes créés (%d caractères minimum). Par défaut, un mot de passe aléatoire est généré par compte et affiché une seule fois.', self::MIN_PASSWORD_LENGTH),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $option = $input->getOption('password');
        $chosenPassword = \is_string($option) ? $option : null;

        if (null !== $chosenPassword && mb_strlen($chosenPassword) < self::MIN_PASSWORD_LENGTH) {
            $io->error(sprintf('Le mot de passe doit contenir au moins %d caractères.', self::MIN_PASSWORD_LENGTH));

            return Command::INVALID;
        }

        $repository = $this->entityManager->getRepository(User::class);
        $created = [];

        foreach (self::DEFAULT_USERS as $email => $role) {
            if (null !== $repository->findOneBy(['email' => $email])) {
                $io->note(sprintf('Le compte « %s » existe déjà. Ignoré.', $email));
                continue;
            }

            $password = $chosenPassword ?? $this->generatePassword();

            $user = new User();
            $user->setEmail($email);
            $user->setRoles([$role]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));

            $this->entityManager->persist($user);
            $created[] = [$email, null === $chosenPassword ? $password : '(fourni en option)'];
        }

        $this->entityManager->flush();

        if ([] === $created) {
            $io->success('Aucun compte à créer.');

            return Command::SUCCESS;
        }

        $io->success(sprintf('%d compte(s) créé(s).', \count($created)));

        if (null === $chosenPassword) {
            $io->table(['Compte', 'Mot de passe'], $created);
            $io->warning('Ces mots de passe ne seront plus affichés : conservez-les dès maintenant.');
        }

        return Command::SUCCESS;
    }

    private function generatePassword(): string
    {
        return substr(rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '='), 0, 24);
    }
}
