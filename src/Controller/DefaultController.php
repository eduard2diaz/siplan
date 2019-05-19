<?php

namespace App\Controller;

use App\Services\NotificacionService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->isGranted('ROLE_ADMIN'))
                return $this->redirectToRoute('usuario_index',array('id'=>$this->getUser()->getId()));

            return $this->redirectToRoute('plantrabajo_index',['id'=>$this->getUser()->getId()]);
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('default/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'locale' => $request->getLocale(),
        ]);
    }

    /**
     * @Route("/estatica/{page}", name="estatica")
     */
    public function estatica($page)
    {
        return $this->render('default/static/'.$page.'.html.twig');
    }
}
