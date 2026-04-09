<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur (email + mot de passe) pour se connecter au tableau de bord.',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email de l’administrateur')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe (min. 8 caractères)')
            ->addOption('nom', null, InputOption::VALUE_OPTIONAL, 'Nom', 'Admin')
            ->addOption('prenom', null, InputOption::VALUE_OPTIONAL, 'Prénom', 'Système');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getOption('email');
        if ($email === null || $email === '') {
            $email = $io->ask('Email');
        }
        $plain = $input->getOption('password');
        if ($plain === null || $plain === '') {
            $plain = $io->askHidden('Mot de passe (caché)');
        }

        if (!is_string($email) || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Email invalide.');

            return Command::FAILURE;
        }
        if (!is_string($plain) || strlen($plain) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');

            return Command::FAILURE;
        }

        $repo = $this->em->getRepository(Users::class);
        if ($repo->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur existe déjà avec cet email.');

            return Command::FAILURE;
        }

        $user = new Users();
        $user->setEmail($email);
        $user->setNom((string) $input->getOption('nom'));
        $user->setPrenom((string) $input->getOption('prenom'));
        $user->setTelephone('');
        $user->setTypeUtilisateur('ADMIN');
        $user->setPassword($this->hasher->hashPassword($user, $plain));
        $user->setPhotoProfil('default.png');
        $user->setVerificationToken(bin2hex(random_bytes(16)));
        $user->setVerificationExpiry(new \DateTime('+1 year'));
        $user->setIsVerified(true);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Administrateur créé : %s — connectez-vous sur /login', $email));

        return Command::SUCCESS;
    }
}
