<?php

namespace App\Controller;

use App\Form\AreaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Area;
use App\Entity\Usuario;

/**
 * @Route("/area")
 */
class AreaController extends Controller
{

    /**
     * @Route("/", name="area_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        $areas = $this->getDoctrine()->getRepository(Area::class)->findAll();

        if ($request->isXmlHttpRequest())
            if ($request->get('_format') == 'xml') {
                $cadena = "";
                foreach ($areas as $value)
                    $cadena .= "<option value={$value->getId()}>{$value->getNombre()}</option>";
                return new Response($cadena);
            } else
                return $this->render('area/_table.html.twig', [
                    'areas' => $areas,
                ]);

        return $this->render('area/index.html.twig', ['areas' => $areas]);
    }


    /**
     * @Route("/new", name="area_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $area = new Area();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(AreaType::class, $area, array('action' => $this->generateUrl('area_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($area);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El área fue registrada satisfactoriamente',
                    'nombre' => $area->getNombre(),
                    'area_padre' => null !== $area->getPadre() ? $area->getPadre()->getNombre() : '',
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $area->getId())->getValue(),
                    'id' => $area->getId(),
                ));
            } else {
                $page = $this->renderView('area/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="area_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(AreaType::class, $area,
            array('action' => $this->generateUrl('area_edit', array('id' => $area->getId()))));
        $form->handleRequest($request);

        $eliminable = $this->esEliminable($area);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($area);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El área fue actualizada satisfactoriamente', 'nombre' => $area->getNombre(), 'area_padre' => null !== $area->getPadre() ? $area->getPadre()->getNombre() : '',));
            } else {
                $page = $this->renderView('area/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'area_edit',
                    'action' => 'Actualizar',
                    'eliminable' => $eliminable,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('area/_new.html.twig', [
            'area' => $area,
            'title' => 'Editar área',
            'action' => 'Actualizar',
            'form_id' => 'area_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="area_delete")
     */
    public function delete(Request $request, Area $area): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $area->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE', $area);
            $em = $this->getDoctrine()->getManager();
            $em->remove($area);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'El área fue eliminada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    //OPCIONES AJAX ADICIONALES

    /**
     * @Route("/{id}/findByUsuario", name="area_findbyusuario", methods="GET",options={"expose"=true})
     */
    public function findByUsuario(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $area = $usuario->getArea();
        $areas = $this->get('area_service')->areasHijas($area);

        $cadena = "";
        foreach ($areas as $area)
            $cadena .= "<option value={$area->getId()}>{$area->getNombre()}</option>";

        return new Response($cadena);
    }

    private function esEliminable(Area $area): bool
    {
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository(Area::class)->findOneByPadre($area) != null)
            return false;
        if ($em->getRepository(Usuario::class)->findOneByArea($area) != null)
            return false;

        return true;
    }
}
