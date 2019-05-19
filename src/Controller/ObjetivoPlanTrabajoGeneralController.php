<?php

namespace App\Controller;

use App\Entity\PlanMensualGeneral;
use App\Entity\ObjetivoPlanMensualGeneral;
use App\Form\ObjetivoPlanTrabajoGeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/objetivoplantrabajogeneral")
 */
class ObjetivoPlanTrabajoGeneralController extends AbstractController
{
    /**
     * @Route("/{id}/new", name="objetivo_plan_trabajo_general_new", methods={"GET","POST"})
     */
    public function new(Request $request,PlanMensualGeneral $plantrabajo): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $objetivo = new ObjetivoPlanMensualGeneral();
        $objetivo->setPlantrabajo($plantrabajo);
        $this->denyAccessUnlessGranted('NEW', $objetivo);
        $form = $this->createForm(ObjetivoPlanTrabajoGeneralType::class, $objetivo, array('action' => $this->generateUrl('objetivo_plan_trabajo_general_new',['id'=>$plantrabajo->getId()])));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($objetivo);
                $em->flush();
                return $this->json(array('mensaje' =>'La actividad principal fue registrada satisfactoriamente',
                    'numero' => $objetivo->getNumero(),
                    'descripcion' => $objetivo->getDescripcion(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$objetivo->getId())->getValue(),
                    'id' => $objetivo->getId(),
                ));
            } else {
                $page = $this->renderView('objetivo_plan_trabajo_general/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('objetivo_plan_trabajo_general/_new.html.twig', [
            'objetivo' => $objetivo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="objetivo_plan_trabajo_general_edit",options={"expose"=true}, methods={"GET","POST"})
     */
    public function edit(Request $request,ObjetivoPlanMensualGeneral $objetivo): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT', $objetivo);
        $form = $this->createForm(ObjetivoPlanTrabajoGeneralType::class, $objetivo, array('action' => $this->generateUrl('objetivo_plan_trabajo_general_edit',['id'=>$objetivo->getId()])));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($objetivo);
                $em->flush();
                return $this->json(array('mensaje' =>'La actividad principal fue actualizada satisfactoriamente',
                    'descripcion' => $objetivo->getDescripcion(),
                    'numero' => $objetivo->getNumero(),
                    'id' => $objetivo->getId(),
                ));
            } else {
                $page = $this->renderView('objetivo_plan_trabajo_general/_form.html.twig', array(
                    'form' => $form->createView(),
                    'objetivo' => $objetivo,
                    'action'=>'Actualizar',
                    'title'=>'Editar actividad principal',
                    'form_id'=>'objetivoplantrabajogeneral_edit'
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('objetivo_plan_trabajo_general/_new.html.twig', [
            'objetivo' => $objetivo,
            'form' => $form->createView(),
            'action'=>'Actualizar',
            'title'=>'Editar objetivo',
            'form_id'=>'objetivoplantrabajogeneral_edit'
        ]);
    }

    /**
     * @Route("/{id}/show", name="objetivo_plan_trabajo_general_show",options={"expose"=true}, methods={"GET"})
     */
    public function show(Request $request, ObjetivoPlanMensualGeneral $objetivoPlanTrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('objetivo_plan_trabajo_general/show.html.twig', [
            'objetivo' => $objetivoPlanTrabajo,
        ]);
    }


    /**
     * @Route("/{id}/delete", name="objetivo_plan_trabajo_general_delete",options={"expose"=true})
     */
    public function delete(Request $request, ObjetivoPlanMensualGeneral $objetivoPlanTrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete'.$objetivoPlanTrabajo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $this->denyAccessUnlessGranted('DELETE', $objetivoPlanTrabajo);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($objetivoPlanTrabajo);
            $entityManager->flush();
            return $this->json(array('mensaje' =>'La actividad principal fue eliminada satisfactoriamente'));



    }
}
