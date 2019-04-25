<?php

namespace App\Controller;

use App\Entity\ActividadGeneral;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\PlanMensualGeneral;
use App\Form\PlanMensualGeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/planmensualgeneral")
 */
class PlanMensualGeneralController extends Controller
{

    /**
     * @Route("/", name="planmensualgeneral_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        $em=$this->getDoctrine()->getManager();
        $esmiembro = null!=$em->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($this->getUser());

        if(!$esmiembro && !$this->isGranted('ROLE_COORDINADOR'))
            throw $this->createAccessDeniedException();

        $planmensualgenerals = $this->getDoctrine()->getRepository(PlanMensualGeneral::class)->findAll();
        $esCoordinador=$this->isGranted('ROLE_COORDINADOR');
        if ($request->isXmlHttpRequest())
            return $this->render('planmensualgeneral/_table.html.twig', [
                'planmensualgenerals' => $planmensualgenerals,
                'esCoordinador'=>$esCoordinador
            ]);

        return $this->render('planmensualgeneral/index.html.twig', [
            'user_id' => $this->getUser()->getId(),
            'user_foto'=>null!=$this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
            'planmensualgenerals' => $planmensualgenerals,
            'esCoordinador'=>$esCoordinador
        ]);
    }

    /**
     * @Route("/new", name="planmensualgeneral_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('ROLE_COORDINADOR');
        $planmensualgeneral = new PlanMensualGeneral();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PlanMensualGeneralType::class, $planmensualgeneral, array('action' => $this->generateUrl('planmensualgeneral_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($planmensualgeneral);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El plan mensual fue registrado satisfactoriamente',
                    'mes' => $planmensualgeneral->getMestoString(),
                    'anno' => $planmensualgeneral->getAnno(),
                    'fechainicio'=>$planmensualgeneral->getEdicionfechainicio()->format('d-m-Y'),
                    'fechafin'=>$planmensualgeneral->getEdicionfechafin()->format('d-m-Y'),
                    'id' => $planmensualgeneral->getId(),
                ));
            } else {
                $page = $this->renderView('planmensualgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('planmensualgeneral/_new.html.twig', [
            'planmensualgeneral' => $planmensualgeneral,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="planmensualgeneral_show", options={"expose"=true},methods="GET")
     */
    public function show(Request $request, PlanMensualGeneral $planmensualgeneral): Response
    {
        $this->denyAccessUnlessGranted('VIEW',$planmensualgeneral);
        $actividads = $this->getDoctrine()
            ->getRepository(ActividadGeneral::class)
            ->findBy(array('planmensualgeneral' => $planmensualgeneral));

        $today=new \DateTime('today');
        $enTiempo=$today>=$planmensualgeneral->getEdicionfechainicio() && $today<= $planmensualgeneral->getEdicionfechafin();
        dump($today);
        dump($planmensualgeneral->getEdicionfechafin());
        dump($enTiempo);
        return $this->render('planmensualgeneral/show.html.twig', ['planmensualgeneral' => $planmensualgeneral,
            'actividads' => $actividads,
            'user_id' => $this->getUser()->getId(),
            'user_foto'=>null!=$this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
            'enTiempo'=>$enTiempo
        ]);
    }

    /**
     * @Route("/{id}/edit", name="planmensualgeneral_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, PlanMensualGeneral $plan): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT',$plan);
        $form = $this->createForm(PlanMensualGeneralType::class, $plan,
            array('action' => $this->generateUrl('planmensualgeneral_edit', array('id' => $plan->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($plan);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El plan mensual fue actualizado satisfactoriamente',
                    'mes' => $plan->getMestoString(),
                    'anno' => $plan->getAnno(),
                    'fechainicio'=>$plan->getEdicionfechainicio()->format('d-m-Y'),
                    'fechainicio'=>$plan->getEdicionfechafin()->format('d-m-Y'),
                ));
            } else {
                $page = $this->renderView('planmensualgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'planmensualgeneral_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('planmensualgeneral/_new.html.twig', [
            'plan' => $plan,
            'title' => 'Editar plan mensual',
            'action' => 'Actualizar',
            'form_id' => 'planmensualgeneral_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="planmensualgeneral_delete")
     */
    public function delete(Request $request, PlanMensualGeneral $planmensualgeneral): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('ROLE_COORDINADOR');
        $em = $this->getDoctrine()->getManager();
        $em->remove($planmensualgeneral);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El plan mensual fue eliminado satisfactoriamente'));
    }
}
