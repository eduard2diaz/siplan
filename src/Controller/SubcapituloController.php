<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\Capitulo;
use App\Form\SubcapituloType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subcapitulo;
use App\Entity\Usuario;

/**
 * @Route("/subcapitulo")
 */
class SubcapituloController extends Controller
{

    /**
     * @Route("/", name="subcapitulo_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        $subcapitulos = $this->getDoctrine()->getRepository(Subcapitulo::class)->findAll();

        if ($request->isXmlHttpRequest())
                return $this->render('subcapitulo/_table.html.twig', [
                    'subcapitulos' => $subcapitulos,
                ]);

        return $this->render('subcapitulo/index.html.twig', ['subcapitulos' => $subcapitulos]);
    }


    /**
     * @Route("/new", name="subcapitulo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {

        $subcapitulo = new Subcapitulo();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(SubcapituloType::class, $subcapitulo, array('action' => $this->generateUrl('subcapitulo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($subcapitulo);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El subcapítulo fue registrado satisfactoriamente',
                    'nombre' => $subcapitulo->getNombre(),
                    'capitulo' => $subcapitulo->getCapitulo()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $subcapitulo->getId())->getValue(),
                    'id' => $subcapitulo->getId(),
                ));
            } else {
                $page = $this->renderView('subcapitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('subcapitulo/_new.html.twig', [
            'subcapitulo' => $subcapitulo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="subcapitulo_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Subcapitulo $subcapitulo): Response
    {
        $form = $this->createForm(SubcapituloType::class, $subcapitulo,
            array('action' => $this->generateUrl('subcapitulo_edit', array('id' => $subcapitulo->getId()))));
        $form->handleRequest($request);

        $eliminable = $this->esEliminable($subcapitulo);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($subcapitulo);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El subcapítulo fue actualizado satisfactoriamente', 'nombre' => $subcapitulo->getNombre(),
                    'capitulo' => $subcapitulo->getCapitulo()->getNombre() ));
            } else {
                $page = $this->renderView('subcapitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'subcapitulo_edit',
                    'action' => 'Actualizar',
                    'eliminable' => $eliminable,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('subcapitulo/_new.html.twig', [
            'subcapitulo' => $subcapitulo,
            'title' => 'Editar subcapítulo',
            'action' => 'Actualizar',
            'form_id' => 'subcapitulo_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="subcapitulo_delete")
     */
    public function delete(Request $request, Subcapitulo $subcapitulo): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $subcapitulo->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($subcapitulo);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'El subcapítulo fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    private function esEliminable(Subcapitulo $subcapitulo): bool
    {
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository(Actividad::class)->findOneByCapitulo($subcapitulo) != null)
            return false;

        return true;
    }
    
    //Funciones ajax
    /**
     * @Route("/{capitulo}/findbycapitulo", name="subcapitulo_findbycapitulo", options={"expose"=true},methods="GET")
     */
    public function findbycapitulo(Request $request, Capitulo $capitulo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $subcapitulos = $this->getDoctrine()->getRepository(Subcapitulo::class)->findByCapitulo($capitulo);
        $subcapitulosHtml = "";
        foreach ($subcapitulos as $value)
            $subcapitulosHtml .= "<option value={$value->getId()}>{$value->getNombre()}</option>";

        return new Response($subcapitulosHtml);
    }
}
