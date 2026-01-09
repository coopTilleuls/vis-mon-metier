<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Roles;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Creates default users for ADMIN, REFERENT, and LECTEUR roles with password "123456"',
)]
class CreateUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $usersConfig = [
            [
                'email' => 'admin@vis-mon-metier.fr',
                'role' => Roles::ADMIN,
            ],
            [
                'email' => 'referent@vis-mon-metier.fr',
                'role' => Roles::REFERENT,
            ],
            [
                'email' => 'lecteur@vis-mon-metier.fr',
                'role' => Roles::LECTEUR,
            ],
        ];

        $createdCount = 0;

        foreach ($usersConfig as $userData) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);

            if ($existingUser) {
                $io->note(sprintf('User "%s" already exists. Skipped.', $userData['email']));
                continue;
            }

            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles([$userData['role']]);

            $hashedPassword = $this->passwordHasher->hashPassword($user, '123456');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            ++$createdCount;
        }

        $this->entityManager->flush();

        if ($createdCount > 0) {
            $io->success(sprintf('%d users created successfully with password "123456".', $createdCount));
        } else {
            $io->success('No new users were created (they likely already exist).');
        }

        return Command::SUCCESS;
    }
}
