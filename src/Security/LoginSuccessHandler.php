<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $roles = $token->getRoleNames();

        if (in_array('ROLE_TEAM', $roles, true)) {
            $url = $this->router->generate('owner_dashboard');
        } elseif (in_array('ROLE_USER', $roles, true)) {
            $url = $this->router->generate('tenant_dashboard');
        } else {
            $url = $this->router->generate('homepage');
        }

        return new RedirectResponse($url);
    }
}