<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;


class DefaultController extends Controller
{
    /**
     * @Route("/prueba", name="prueba")
     */
    public function prueba(Request $request)
    {

        return $this->render('default/prueba.html.twig');
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->isGranted('ROLE_ADMIN'))
                return $this->redirectToRoute('usuario_index');

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
     * @Route("/translate/{language}", name="translate", requirements={"language":"es|en|fr"})
     */
    public function english(Request $request,$language)
    {
        return $this->alternadIdioma($request,$language);
        //CODIGO ORIGINAL
        $request->getSession()->set('_locale', $language);
        return $this->redirect($request->headers->get('referer'));
    }

    private function alternadIdioma(Request $request, $idioma){
        $base=$request-> getSchemeAndHttpHost().$request->getBaseUrl();
        $longbase=strlen($base);
        $anterior=$request->headers->get('referer');
        $segmento=substr($anterior,$longbase);
        if($segmento=="")
            $segmento='/';
        $collection= $this->get('router')->getRouteCollection();
        $context = new RequestContext();
        $matcher=new UrlMatcher($collection,$this->get('router')->getContext());
        $propiedades=$matcher->match($segmento);
        $ruta=$propiedades['_route'];
        if($segmento=='/')
            $request->getSession()->set('_locale', $idioma);
        else
            $propiedades['_locale']=$idioma;

        unset($propiedades['_controller']);
        unset($propiedades['_route']);
        return $this->redirectToRoute($ruta,$propiedades);
    }
}
