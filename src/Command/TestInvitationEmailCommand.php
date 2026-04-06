<?php

namespace App\Command;

use App\Entity\Tenant;
use App\Service\TenantInvitationMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-invitation-email',
    description: 'Envoie un email d\'invitation de test à un locataire existant.',
)]
class TestInvitationEmailCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TenantInvitationMailerService $mailer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tenant-id', InputArgument::REQUIRED, 'ID du locataire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('tenant-id');

        $tenant = $this->entityManager->getRepository(Tenant::class)->find($id);
        if (!$tenant) {
            $io->error("Locataire #$id introuvable.");
            return Command::FAILURE;
        }

        $tenant->generateInvitationToken();
        $this->entityManager->flush();

        $this->mailer->sendInvitation($tenant, 'Bailleur Test');

        $io->success(sprintf('Email envoyé à %s (%s)', $tenant->getName(), $tenant->getEmail()));
        $io->info('Lien : /definir-mot-de-passe/' . $tenant->getInvitationToken());

        return Command::SUCCESS;
    }
}
