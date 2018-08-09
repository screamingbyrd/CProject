<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 09/07/2018
 * Time: 15:08
 */

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AfterLoginRedirect implements AuthenticationSuccessHandlerInterface
{

    public function __construct($router)
    {
        $this->router    = $router;
    }

    /**
     * @param Request        $request
     *
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $roles = $token->getRoles();

        $rolesTab = array_map(function ($role) {
            return $role->getRole();
        }, $roles);

        if (in_array('ROLE_PROPOSER', $rolesTab, true)) {
            $redirection = new RedirectResponse($this->router->generate('dashboard_proposer'));
        } elseif(in_array('ROLE_VOTER', $rolesTab, true)) {
            $redirection = new RedirectResponse($this->router->generate('dashboard_voter'));
        }else {
            $redirection = new RedirectResponse($this->router->generate('jobnow_home'));
        }

        return $redirection;
    }

}