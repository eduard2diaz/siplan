<?php

namespace App\Controller;

use App\Entity\ActividadGeneral;
use App\Entity\ARC;
use App\Entity\Capitulo;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\PlanMensualGeneral;
use App\Entity\PuntualizacionPlanMensualGeneral;
use App\Entity\Subcapitulo;
use App\Form\PlanMensualGeneralType;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/planmensualgeneral")
 */
class PlanMensualGeneralController extends AbstractController
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

        $planmensualgeneral = new PlanMensualGeneral();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PlanMensualGeneralType::class, $planmensualgeneral, array('action' => $this->generateUrl('planmensualgeneral_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($planmensualgeneral);
                $em->flush();
                return $this->json(array('mensaje' => 'El plan mensual fue registrado satisfactoriamente',
                    'mes' => $planmensualgeneral->getMestoString(),
                    'anno' => $planmensualgeneral->getAnno(),
                    'fechainicio'=>$planmensualgeneral->getEdicionfechainicio()->format('d-m-Y'),
                    'fechafin'=>$planmensualgeneral->getEdicionfechafin()->format('d-m-Y'),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$planmensualgeneral->getId())->getValue(),
                    'id' => $planmensualgeneral->getId(),
                ));
            } else {
                $page = $this->renderView('planmensualgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
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

        $em=$this->getDoctrine()->getManager();
        $actividads = $em->getRepository(ActividadGeneral::class)
            ->findBy(array('planmensualgeneral' => $planmensualgeneral));

        $today=new \DateTime('today');
        $enTiempo=$today>=$planmensualgeneral->getEdicionfechainicio() && $today<= $planmensualgeneral->getEdicionfechafin();

        $puntualizaciones=$em->getRepository(PuntualizacionPlanMensualGeneral::class)->findBy(array('plantrabajo' => $planmensualgeneral));

        return $this->render('planmensualgeneral/show.html.twig', ['planmensualgeneral' => $planmensualgeneral,
            'actividads' => $actividads,
            'puntualizaciones' => $puntualizaciones,
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

        $form = $this->createForm(PlanMensualGeneralType::class, $plan,
            array('action' => $this->generateUrl('planmensualgeneral_edit', array('id' => $plan->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($plan);
                $em->flush();
                return $this->json(array('mensaje' => 'El plan mensual fue actualizado satisfactoriamente',
                    'mes' => $plan->getMestoString(),
                    'anno' => $plan->getAnno(),
                    'fechainicio'=>$plan->getEdicionfechainicio()->format('d-m-Y'),
                    'fechafin'=>$plan->getEdicionfechafin()->format('d-m-Y'),
                ));
            } else {
                $page = $this->renderView('planmensualgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'planmensualgeneral_edit',
                    'action' => 'Actualizar',
                ));
                return $this->json(array('form' => $page, 'error' => true));
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
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete'.$planmensualgeneral->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($planmensualgeneral);
            $em->flush();
            return $this->json(array('mensaje' => 'El plan mensual fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/{id}/exportar",options={"expose"=true}, name="planmensualgeneral_exportar")
     */
    public function exportar(PlanMensualGeneral $planmensualgeneral, Pdf $pdf){
        $em = $this->getDoctrine()->getManager();
        $capitulos_array=[];
        $capitulos=$em->getRepository(Capitulo::class)->findBy([],['numero'=>'ASC']);
        foreach ($capitulos as $capitulo){
            $subcapitulos_array=[];
            $subcapitulos=$em->getRepository(Subcapitulo::class)->findBy(['capitulo'=>$capitulo],['numero'=>'ASC']);
            foreach ($subcapitulos as $subcapitulo){
                $arcs=$em->getRepository(ARC::class)->findBy(['subcapitulo'=>$subcapitulo]);
                $arcs_array=[];
                foreach ($arcs as $arc){
                    $actividades_array=[];
                    $actividades=$em->getRepository(ActividadGeneral::class)->findBy(['areaconocimiento'=>$arc,'planmensualgeneral'=>$planmensualgeneral]);
                    foreach ($actividades as $value)
                        $actividades_array[]=['nombre'=>$value->getNombre(), 'fecha'=>$value->getFecha(),'fechaF'=>$value->getFechaF()];
                    $arcs_array[]=['nombre'=>$arc->getNombre(),'actividades'=>$actividades_array];
                }

                $actividades_array=[];
                $actividades=$em->getRepository(ActividadGeneral::class)->findBy(['areaconocimiento'=>null,'subcapitulo'=>$subcapitulo,'planmensualgeneral'=>$planmensualgeneral]);
                foreach ($actividades as $value)
                    $actividades_array[]=['nombre'=>$value->getNombre(), 'fecha'=>$value->getFecha(),'fechaF'=>$value->getFechaF()];

                $subcapitulos_array[]=['nombre'=>$subcapitulo->getNombre(),'arcs'=>$arcs_array,'actividades'=>$actividades_array];
            }
            $capitulos_array[]=['nombre'=>$capitulo->getNombre(),'subcapitulos'=>$subcapitulos_array];
        }
        $html=$this->renderView('planmensualgeneral/_pdf.html.twig',['plan'=>$planmensualgeneral,'capitulos'=>$capitulos_array]);
        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }
}
