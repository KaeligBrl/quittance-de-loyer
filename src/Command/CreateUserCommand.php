<?php

namespace App\Command;

use App\Entity\Team;
use App\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('class', InputArgument::REQUIRED, 'Tenant or Team')
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('name', InputArgument::OPTIONAL, 'Full name (Tenant) or first name (Team)', 'Locataire')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'Last name (Team only)', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class     = $input->getArgument('class');
        $email     = $input->getArgument('email');
        $password  = $input->getArgument('password');
        $name      = $input->getArgument('name');
        $lastname  = $input->getArgument('lastname');

        if ($class === 'Tenant') {
            $user = new Tenant();
            $user->setEmail($email);
            $user->setName($name);
        } elseif ($class === 'Team') {
            $user = new Team();
            $user->setEmail($email);
            $user->setRoles(['ROLE_TEAM']);
            if ($name !== 'Locataire') $user->setFirstName($name);
            if ($lastname) $user->setLastName($lastname);
        } else {
            $output->writeln('<error>Class must be Tenant or Team</error>');
            return Command::FAILURE;
        }

        $hashed = $this->hasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>' . $class . ' cree : ' . $email . '</info>');
        return Command::SUCCESS;
    }
}
