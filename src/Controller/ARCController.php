<?php

namespace App\Controller;

use App\Form\ARCType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\ARC;
use App\Entity\Usuario;

/**
 * @Route("/arc")
 */
class ARCController extends Controller
{

    /**
     * @Route("/", name="arc_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        $arcs = $this->getDoctrine()->getRepository(ARC::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('arc/_table.html.twig', [
                'arcs' => $arcs,
            ]);

        return $this->render('arc/index.html.twig', ['arcs' => $arcs]);
    }


    /**
     * @Route("/new", name="arc_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $arc = new ARC();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(arcType::class, $arc, array('action' => $this->generateUrl('arc_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($arc);
                $em->flush();
                return new JsonResponse(array('mensaje' =>'El área del conocimiento fue registrada satisfactoriamente',
                    'nombre' => $arc->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$arc->getId())->getValue(),
                    'id' => $arc->getId(),
                ));
            } else {
                $page = $this->renderView('arc/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('arc/_new.html.twig', [
            'arc' => $arc,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="arc_show",options={"expose"=true}, methods="GET")
     */
    public function show(Request $request, ARC $arc): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('arc/_show.html.twig', [
            'arc' => $arc,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="arc_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, ARC $arc): Response
    {
        $form = $this->createForm(arcType::class, $arc,
            array('action' => $this->generateUrl('arc_edit', array('id' => $arc->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($arc);
                $em->flush();
                return new JsonResponse(array('mensaje' =>'El área del conocimiento fue actualizada satisfactoriamente',
                    'nombre' => $arc->getNombre(),
                    ));
            } else {
                $page = $this->renderView('arc/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'arc_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('arc/_new.html.twig', [
            'arc' => $arc,
            'title' => 'Editar área del conocimiento ',
            'action' => 'Actualizar',
            'form_id' => 'arc_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="arc_delete")
     */
    public function delete(Request $request, ARC $arc): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete'.$arc->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($arc);
            $em->flush();
            return new JsonResponse(array('mensaje' =>'El área del conocimiento fue eliminada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

}
