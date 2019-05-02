<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Repository\ApiTokenRepository;

class ApiTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $apiTokenRepo;

    public function __construct(ApiTokenRepository $apiTokenRepo)
    {
        $this->apiTokenRepo = $apiTokenRepo;
    }

    /*
     * Retorna un booleano indicando si el autenticador soporta la solicitud dada
     */
    public function supports(Request $request)
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    /*
     * Se llama en cada solicitud y retorna las credenciales o null para detener la
     * autenticacion
     */
    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            // no token? Return null and no other methods will be called
            return;
        }

        $credentials = [
            'token' => $token
        ];
        return $credentials;
    }

    /*
     * Si el getCredentials no retorna null este metodo es llamado y retorna las credenciales que recibio
     * como argumento. Su responsabilidad es retornar el objeto que implementa UserInterface, si retornas
     * null sera para decir que la autenticacion fallo, en caso contrario se llama al metodo checkCredentials()
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $this->apiTokenRepo->findOneBy([
            'token' => $credentials['token']
        ]);

        if (!$token) {
            //return;
            throw new CustomUserMessageAuthenticationException(
                'Invalid API Token'
            );
        }

        if ($token->isExpired()) {
            throw new CustomUserMessageAuthenticationException(
                'Token expired'
            );
        }

        return $token->getUsuario();
    }

    /*
     * Su trabajo s comprobar que las credenciales son correctas, en caso de formularios
     * de  login su responsailidad en comprobar que el password introducido es el
     * del usuario
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /* Se ejecuta cuando la autenticacion falla, su trabajo es retornar el Response que
     *  recibira el cliente o una excepcion indicando que hubo un error durante la autenticaion
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessageKey()
        ], 401);
    }

    /* Se ejecuta cunado la autenticacion fue correcta y su responsabilidad es retornar un
     * Response que es el que recibira el usuario o null para continuar con el request
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    /*
    * Se ejecuta cuando la autenticaion en necesaria(cuando el cliente accede a una URI
    * o recurso donde la autetnicacon es requerida), su trabajo es devolver un Response
    * que diga al usuario que debe autenticarse
    */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, 401);
    }

    //Se usa ara idicar que se desea el remember me
    public function supportsRememberMe()
    {
        return false;
    }
}
