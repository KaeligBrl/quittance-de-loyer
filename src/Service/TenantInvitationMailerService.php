<?php

namespace App\Service;

use App\Entity\Tenant;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TenantInvitationMailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private string $senderEmail = 'contact@kaeligberel.fr',
        private string $senderName = 'Quittance de Loyer',
    ) {}

    public function sendInvitation(Tenant $tenant, string $landlordName): void
    {
        if (!$tenant->getEmail()) {
            return;
        }

        $link = $this->urlGenerator->generate(
            'app_tenant_set_password',
            ['token' => $tenant->getInvitationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $html = $this->twig->render('emails/tenant_invitation.html.twig', [
            'tenantName'   => $tenant->getName(),
            'landlordName' => $landlordName,
            'link'         => $link,
        ]);

        $email = (new Email())
            ->from(Address::create(sprintf('%s <%s>', $this->senderName, $this->senderEmail)))
            ->to($tenant->getEmail())
            ->subject('Votre compte locataire — Définissez votre mot de passe')
            ->html($html);

        $this->mailer->send($email);
    }
}
