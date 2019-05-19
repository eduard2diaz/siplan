<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\ActividadGeneral;
use App\Entity\Subcapitulo;
use App\Form\ARCType;
use App\Entity\MiembroConsejoDireccion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ARC;

/**
 * @Route("/arc")
 */
class ARCController extends AbstractController
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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $arc = new ARC();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ARCType::class, $arc, array('action' => $this->generateUrl('arc_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($arc);
                $em->flush();
                return $this->json(array('mensaje' => 'El área de resultados claves fue registrada satisfactoriamente',
                    'nombre' => $arc->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $arc->getId())->getValue(),
                    'id' => $arc->getId(),
                ));
            } else {
                $page = $this->renderView('arc/_form.html.twig', array(
                    'form' => $form->createView(),
                    'arc' => $arc,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
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
        if (!$request->isXmlHttpRequest())
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
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(ARCType::class, $arc,
            array('action' => $this->generateUrl('arc_edit', array('id' => $arc->getId()))));
        $form->handleRequest($request);
        $eliminable = $this->esEliminable($arc);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($arc);
                $em->flush();
                return $this->json(array('mensaje' => 'El área de resultados claves fue actualizada satisfactoriamente',
                    'nombre' => $arc->getNombre(),
                ));
            } else {
                $page = $this->renderView('arc/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'arc_edit',
                    'action' => 'Actualizar',
                    'arc' => $arc,
                    'eliminable' => $eliminable,
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('arc/_new.html.twig', [
            'arc' => $arc,
            'title' => 'Editar área de resultados claves ',
            'action' => 'Actualizar',
            'form_id' => 'arc_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="arc_delete")
     */
    public function delete(Request $request, ARC $arc): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->esEliminable($arc) || !$this->isCsrfTokenValid('delete' . $arc->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($arc);
        $em->flush();
        return $this->json(array('mensaje' => 'El área de resultados claves fue eliminada satisfactoriamente'));
    }

    /*
    *Funcion que devuelve si un arc es eliminable teniendo en cuenta si otras entidades que dependen de ella
    */
    private function esEliminable(ARC $arc): bool
    {
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository(ActividadGeneral::class)->findOneByAreaconocimiento($arc) != null)
            return false;
        if ($em->getRepository(Actividad::class)->findOneByAreaconocimiento($arc) != null)
            return false;

        return true;
    }

    //Funciones ajax

    /**
     * @Route("/{subcapitulo}/findbysubcapitulo", name="arc_findbysubcapitulo", options={"expose"=true},methods="GET")
     * Se utiliza para la gestion de actividades del plan general
     */
    public function findbysubcapitulo(Request $request, Subcapitulo $subcapitulo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COORDINADORINSTITUCIONAL') && null == $this->getDoctrine()->getManager()->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($this->getUser()))
            throw $this->createAccessDeniedException();

        $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT a.id, a.nombre FROM App:ARC a JOIN a.subcapitulo s WHERE s.id= :id');
        $consulta->setParameter('id', $subcapitulo->getId());
        $subcapitulos = $consulta->getResult();
        return $this->json($subcapitulos);
    }
}
