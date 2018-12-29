<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route({
 *     "en": "/static",
 *     "es": "/estatica",
 *     "fr": "/staticar"
 * })
 */
class EstaticaController extends Controller
{

    /**
     * @Route({"en": "/aboutus", "es": "/acercadenosotros", "fr": "/aproposdenous"}, name="aboutus")
     */
    public function aboutus()
    {
        return $this->static('aboutus');
    }

    /**
     * @Route({"en": "/accesibility", "es": "/accesibilidad", "fr": "/accessibilite"}, name="accesibility")
     */
    public function accesibility()
    {
        return $this->static('accesibility');
    }

    /**
     * @Route({"en": "/account_management", "es": "/cuenta", "fr": "/gestiondecompte"}, name="account_management")
     */
    public function account_management()
    {
        return $this->static('account_management');
    }

    /**
     * @Route({"en": "/cookiespolicy", "es": "/politicacookies", "fr": "/politiquedecookies"}, name="cookiespolicy")
     */
    public function cookiespolicy()
    {
        return $this->static('cookiespolicy');
    }

    /**
     * @Route({"en": "/privacy", "es": "/privacidad", "fr": "/confidentialité"}, name="privacy", options={"utf8": true})
     */
    public function privacy()
    {
        return $this->static('privacy');
    }

    /**
     * @Route({"en": "/privacypolicy", "es": "/politicaprivacidad", "fr": "/politiquedeconfidentialité"}, name="privacypolicy", options={"utf8": true})
     */
    public function privacypolicy()
    {
        return $this->static('privacypolicy');
    }

    /**
     * @Route({"en": "/scalability", "es": "/escalabilidad", "fr": "/évolutivité"}, name="scalability", options={"utf8": true})
     */
    public function scalability()
    {
        return $this->static('scalability');
    }

    /**
     * @Route({"en": "/services", "es": "/servicios", "fr": "/prestationsdeservice"}, name="services")
     */
    public function services()
    {
        return $this->static('services');
    }

    /**
     * @Route({"en": "/serviceterms", "es": "/terminosservicio", "fr": "/conditionsdeservice"}, name="serviceterms")
     */
    public function serviceterms()
    {
        return $this->static('serviceterm');
    }

    private function static($page)
    {
        return $this->render('default/static/'.$page.'.html.twig');
    }
}
