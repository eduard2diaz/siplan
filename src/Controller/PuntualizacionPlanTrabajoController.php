<?php

namespace App\Controller;

use App\Entity\Plantrabajo;
use App\Entity\PuntualizacionPlanTrabajo;
use App\Form\PuntualizacionPlanTrabajoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/puntualizacionplantrabajo")
 */
class PuntualizacionPlanTrabajoController extends AbstractController
{

    /**
     * @Route("/{id}/new", name="puntualizacion_plan_trabajo_new", methods={"GET","POST"})
     */
    public function new(Request $request,Plantrabajo $plantrabajo): Response
    {
        $puntualizacion = new PuntualizacionPlanTrabajo();
        $puntualizacion->setPlantrabajo($plantrabajo);

        $this->denyAccessUnlessGranted('NEW',$puntualizacion);

        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PuntualizacionPlanTrabajoType::class, $puntualizacion, array('action' => $this->generateUrl('puntualizacion_plan_trabajo_new',['id'=>$plantrabajo->getId()])));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($puntualizacion);
                $em->flush();
                return $this->json(array('mensaje' =>'La puntualización fue registrada satisfactoriamente',
                    'actividad' => $puntualizacion->getActividad(),
                    'fecha' => $puntualizacion->getFechacreacion()->format('d-m-Y H:i'),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$puntualizacion->getId())->getValue(),
                    'id' => $puntualizacion->getId(),
                ));
            } else {
                $page = $this->renderView('puntualizacion_plan_trabajo/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('puntualizacion_plan_trabajo/_new.html.twig', [
            'puntualizacion' => $puntualizacion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="puntualizacion_plan_trabajo_show",options={"expose"=true}, methods={"GET"})
     */
    public function show(PuntualizacionPlanTrabajo $puntualizacionPlanTrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW',$puntualizacionPlanTrabajo);

        return $this->render('puntualizacion_plan_trabajo/show.html.twig', [
            'puntualizacion' => $puntualizacionPlanTrabajo,
        ]);
    }


    /**
     * @Route("/{id}/delete", name="puntualizacion_plan_trabajo_delete",options={"expose"=true})
     */
    public function delete(Request $request, PuntualizacionPlanTrabajo $puntualizacionPlanTrabajo): Response
    {
        $this->denyAccessUnlessGranted('DELETE',$puntualizacionPlanTrabajo);
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete'.$puntualizacionPlanTrabajo->getId(), $request->query->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($puntualizacionPlanTrabajo);
            $entityManager->flush();
            return $this->json(array('mensaje' =>'La puntualización fue eliminada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }
}
