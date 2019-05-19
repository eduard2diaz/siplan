<?php

namespace App\Controller;

use App\Form\PuntualizacionPlanMensualAreaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\PuntualizacionPlanMensualArea;
use App\Entity\PlanMensualArea;

/**
 * @Route("/puntualizacionplantrabajoarea")
 */
class PuntualizacionPlanTrabajoAreaController extends AbstractController
{
    /**
     * @Route("/{id}/new", name="puntualizacion_plan_trabajo_area_new", methods={"GET","POST"})
     */
    public function new(Request $request, PlanMensualArea $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);
        $puntualizacion = new PuntualizacionPlanMensualArea();
        $puntualizacion->setPlantrabajo($plantrabajo);
        $puntualizacion->setUsuario($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PuntualizacionPlanMensualAreaType::class, $puntualizacion, array('action' => $this->generateUrl('puntualizacion_plan_trabajo_area_new', ['id' => $plantrabajo->getId()])));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($puntualizacion);
                $em->flush();
                return $this->json(array('mensaje' => 'La puntualización fue registrada satisfactoriamente',
                    'actividad' => $puntualizacion->getActividad(),
                    'fecha' => $puntualizacion->getFechacreacion()->format('d-m-Y H:i'),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $puntualizacion->getId())->getValue(),
                    'id' => $puntualizacion->getId(),
                ));
            } else {
                $page = $this->renderView('puntualizacion_plan_trabajo_area/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('puntualizacion_plan_trabajo_area/_new.html.twig', [
            'puntualizacion' => $puntualizacion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="puntualizacion_plan_trabajo_area_show",options={"expose"=true}, methods={"GET"})
     */
    public function show(Request $request, PuntualizacionPlanMensualArea $puntualizacionPlanTrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $puntualizacionPlanTrabajo->getPlantrabajo());
        return $this->render('puntualizacion_plan_trabajo_area/show.html.twig', [
            'puntualizacion' => $puntualizacionPlanTrabajo,
        ]);
    }


    /**
     * @Route("/{id}/delete", name="puntualizacion_plan_trabajo_area_delete",options={"expose"=true})
     */
    public function delete(Request $request, PuntualizacionPlanMensualArea $puntualizacionPlanTrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $puntualizacionPlanTrabajo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $puntualizacionPlanTrabajo->getPlantrabajo());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($puntualizacionPlanTrabajo);
        $entityManager->flush();
        return $this->json(array('mensaje' => 'La puntualización fue eliminada satisfactoriamente'));
    }
}
