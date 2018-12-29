<?php

namespace App\Controller;

use App\Form\CargoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use  App\Entity\Area;
use App\Entity\Cargo;

/**
 * @Route({
 *     "en": "/cargoen",
 *     "es": "/cargo",
 *     "fr": "/cargoey",
 * })
 */
class CargoController extends Controller
{

    /**
     * @Route("/", name="cargo_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $cargos = $this->getDoctrine()->getRepository(Cargo::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('cargo/_table.html.twig', [
                'cargos' => $cargos,
            ]);

        return $this->render('cargo/index.html.twig', ['cargos' => $cargos]);
    }

    /**
     * @Route("/{area}/ajax", name="cargo_ajax", options={"expose"=true},methods="GET")
     */
    public function ajax(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_DIRECTIVO']);

        $cargos = $this->getDoctrine()->getRepository(Cargo::class)->findByArea($area);
        $cargosHtml = "";
        foreach ($cargos as $value)
            $cargosHtml .= "<option value={$value->getId()}>{$value->getNombre()}</option>";

        return new Response($cargosHtml);
    }


    /**
     * @Route("/new", name="cargo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $cargo = new Cargo();
        $this->denyAccessUnlessGranted('NEW', $cargo);
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(CargoType::class, $cargo, array('action' => $this->generateUrl('cargo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($cargo);
                $em->flush();
                return new JsonResponse(array('mensaje' => $this->get('translator')->trans("cargo_register_successfully"),
                    'nombre' => $cargo->getNombre(),
                    'area' => $cargo->getArea()->getNombre(),
                    'id' => $cargo->getId(),
                ));
            } else {
                $page = $this->renderView('cargo/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('cargo/_new.html.twig', [
            'cargo' => $cargo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="cargo_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Cargo $cargo): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $cargo);
        $form = $this->createForm(CargoType::class, $cargo,
            array('action' => $this->generateUrl('cargo_edit', array('id' => $cargo->getId()))));


        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cargo);
                $em->flush();
                return new JsonResponse(array('mensaje' => $this->get('translator')->trans("cargo_update_successfully"), 'nombre' => $cargo->getNombre(), 'area' => $cargo->getArea()->getNombre()));
            } else {
                $page = $this->renderView('cargo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'update_button',
                    'form_id' => 'cargo_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('cargo/_new.html.twig', [
            'cargo' => $cargo,
            'title' => 'edit_cargoheader',
            'action' => 'update_button',
            'form_id' => 'cargo_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="cargo_delete")
     */
    public function delete(Request $request, Cargo $cargo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $cargo);
        $em = $this->getDoctrine()->getManager();
        $em->remove($cargo);
        $em->flush();
        return new JsonResponse(array('mensaje' => $this->get('translator')->trans("cargo_delete_successfully")));
    }
}
