<?php

namespace App\Controller;

use App\Form\CapituloType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Capitulo;
use App\Entity\Usuario;
use App\Entity\Subcapitulo;

/**
 * @Route("/capitulo")
 */
class CapituloController extends Controller
{

    /**
     * @Route("/", name="capitulo_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        $capitulos = $this->getDoctrine()->getRepository(Capitulo::class)->findAll();

        if ($request->isXmlHttpRequest())
            if ($request->get('_format') == 'xml') {
                $cadena = "";
                foreach ($capitulos as $value)
                    $cadena .= "<option value={$value->getId()}>{$value->getNombre()}</option>";
                return new Response($cadena);
            } else
                return $this->render('capitulo/_table.html.twig', [
                    'capitulos' => $capitulos,
                ]);

        return $this->render('capitulo/index.html.twig', ['capitulos' => $capitulos]);
    }


    /**
     * @Route("/new", name="capitulo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {

        $capitulo = new Capitulo();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CapituloType::class, $capitulo, array('action' => $this->generateUrl('capitulo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($capitulo);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El capítulo fue registrado satisfactoriamente',
                    'nombre' => $capitulo->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $capitulo->getId())->getValue(),
                    'id' => $capitulo->getId(),
                ));
            } else {
                $page = $this->renderView('capitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('capitulo/_new.html.twig', [
            'capitulo' => $capitulo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="capitulo_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Capitulo $capitulo): Response
    {
        $form = $this->createForm(CapituloType::class, $capitulo,
            array('action' => $this->generateUrl('capitulo_edit', array('id' => $capitulo->getId()))));
        $form->handleRequest($request);

        $eliminable = $this->esEliminable($capitulo);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($capitulo);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El capítulo fue actualizado satisfactoriamente', 'nombre' => $capitulo->getNombre(),
                    ));
            } else {
                $page = $this->renderView('capitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'capitulo_edit',
                    'action' => 'Actualizar',
                    'eliminable' => $eliminable,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('capitulo/_new.html.twig', [
            'capitulo' => $capitulo,
            'title' => 'Editar capítulo',
            'action' => 'Actualizar',
            'form_id' => 'capitulo_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="capitulo_delete")
     */
    public function delete(Request $request, Capitulo $capitulo): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $capitulo->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($capitulo);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'El capítulo fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    private function esEliminable(Capitulo $capitulo): bool
    {
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository(SubCapitulo::class)->findOneByCapitulo($capitulo) != null)
            return false;

        return true;
    }
}
