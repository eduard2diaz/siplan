<?php

namespace App\Controller;

use App\Entity\PlanMensualGeneral;
use App\Entity\PuntualizacionPlanMensualGeneral;
use App\Form\PuntualizacionPlanTrabajoGeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/puntualizacionplantrabajogeneral")
 */
class PuntualizacionPlanTrabajoGeneralController extends AbstractController
{
    /**
     * @Route("/{id}/new", name="puntualizacion_plan_trabajo_general_new", methods={"GET","POST"})
     */
    public function new(Request $request,PlanMensualGeneral $plantrabajo): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $puntualizacion = new PuntualizacionPlanMensualGeneral();
        $puntualizacion->setPlantrabajo($plantrabajo);
        $puntualizacion->setUsuario($this->getUser());

        $this->denyAccessUnlessGranted('NEW', $puntualizacion);
        $form = $this->createForm(PuntualizacionPlanTrabajoGeneralType::class, $puntualizacion, array('action' => $this->generateUrl('puntualizacion_plan_trabajo_general_new',['id'=>$plantrabajo->getId()])));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($puntualizacion);
                $em->flush();
                return $this->json(array('mensaje' =>'La puntualización fue registrada satisfactoriamente',
                    'actividad' => $puntualizacion->getActividad(),
                    'fecha' => $puntualizacion->getFechacreacion()->format('d-m-Y H:i'),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$puntualizacion->getId())->getValue(),
                    'id' => $puntualizacion->getId(),
                ));
            } else {
                $page = $this->renderView('puntualizacion_plan_trabajo_general/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('puntualizacion_plan_trabajo_general/_new.html.twig', [
            'puntualizacion' => $puntualizacion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="puntualizacion_plan_trabajo_general_show",options={"expose"=true}, methods={"GET"})
     */
    public function show(Request $request, PuntualizacionPlanMensualGeneral $puntualizacionPlanTrabajo): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('puntualizacion_plan_trabajo_general/show.html.twig', [
            'puntualizacion' => $puntualizacionPlanTrabajo,
        ]);
    }


    /**
     * @Route("/{id}/delete", name="puntualizacion_plan_trabajo_general_delete",options={"expose"=true})
     */
    public function delete(Request $request, PuntualizacionPlanMensualGeneral $puntualizacionPlanTrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$puntualizacionPlanTrabajo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $this->denyAccessUnlessGranted('DELETE', $puntualizacionPlanTrabajo);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($puntualizacionPlanTrabajo);
            $entityManager->flush();
            return $this->json(array('mensaje' =>'La puntualización fue eliminada satisfactoriamente'));
    }
}
