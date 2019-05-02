<?php

namespace App\Security;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Services\LdapService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class LdapAuthenticator extends AbstractGuardAuthenticator
{
    private $repository;
    private $ldapservice;
    private $encoder;


    public function __construct(UsuarioRepository $repository, LdapService $ldapservice, UserPasswordEncoderInterface $encoder)
    {
        $this->repository = $repository;
        $this->ldapservice = $ldapservice;
        $this->encoder = $encoder;
    }

    public function supports(Request $request)
    {
        return $request->request->has('_username') && $request->request->has('_password');
    }

    public function getCredentials(Request $request)
    {
        if (!$request->request->has('_username') || !$request->request->has('_password')) {
            return;
        }
        $credentials = [
            '_username' => $request->request->get('_username'),
            '_password' => $request->request->get('_password')
        ];
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $user = $this->repository->loadUserByUsername($credentials['_username']);
        if (!$user)
            return;

        if ($this->encoder->isPasswordValid($user, $credentials['_password'])) {
            return $user;
        }

        $login_status = $this->ldapservice->login($credentials['_username'], $credentials['_password']);

        if (!$login_status)
            return;

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
