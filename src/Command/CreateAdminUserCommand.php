<?php

namespace App\Command;

use App\Entity\Usuarios;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crear un usuario administrador',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email del usuario admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Contraseña del usuario admin')
            ->addArgument('nombre', InputArgument::REQUIRED, 'Nombre del usuario admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $nombre = $input->getArgument('nombre');

        // Verificar si ya existe un usuario con este email
        $existingUser = $this->entityManager->getRepository(Usuarios::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Ya existe un usuario con este email: ' . $email);
            return Command::FAILURE;
        }

        // Crear el usuario admin
        $admin = new Usuarios();
        $admin->setEmail($email);
        $admin->setNombre($nombre);
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        
        // Hash de la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        // Persistir en la base de datos
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Usuario administrador creado exitosamente!');
        $io->info('Email: ' . $email);
        $io->info('Nombre: ' . $nombre);
        $io->info('Roles: ROLE_ADMIN, ROLE_USER');

        return Command::SUCCESS;
    }
}
