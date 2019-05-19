<?php

namespace App\Controller;

use App\Entity\ActividadGeneral;
use App\Entity\ARC;
use App\Entity\Capitulo;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\ObjetivoPlanMensualGeneral;
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
        $planmensualgenerals = $this->getDoctrine()->getRepository(PlanMensualGeneral::class)->findAll();
        if ($request->isXmlHttpRequest())
            return $this->render('planmensualgeneral/_table.html.twig', [
                'planmensualgenerals' => $planmensualgenerals,
            ]);

        return $this->render('planmensualgeneral/index.html.twig', [
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'planmensualgenerals' => $planmensualgenerals,
            'esDirectivo'=>$this->getUser()->esDirectivo()
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
        $planmensualgeneral->setGestionadopor($this->getUser());
        $form = $this->createForm(PlanMensualGeneralType::class, $planmensualgeneral, array('action' => $this->generateUrl('planmensualgeneral_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if($planmensualgeneral->getAprobado()==true)
                    $planmensualgeneral->setAprobadopor($this->getUser());

                $estado_class=$planmensualgeneral->getAprobado() ? 'danger' : 'success';
                $estado_label=$planmensualgeneral->getAprobado() ? 'Aprobado' : 'Pendiente';

                $em->persist($planmensualgeneral);
                $em->flush();
                return $this->json(array('mensaje' => 'El plan mensual fue registrado satisfactoriamente',
                    'mes' => $planmensualgeneral->getMestoString(),
                    'anno' => $planmensualgeneral->getAnno(),
                    'fechainicio' => $planmensualgeneral->getEdicionfechainicio()->format('d-m-Y'),
                    'fechafin' => $planmensualgeneral->getEdicionfechafin()->format('d-m-Y'),
                    'estado'=>'<span class="m-nav__link-badge m-badge m-badge--'.$estado_class.'">'.$estado_label.'</span>',
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $planmensualgeneral->getId())->getValue(),
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
        $em = $this->getDoctrine()->getManager();
        $today = new \DateTime('today');
        $enTiempo = $today >= $planmensualgeneral->getEdicionfechainicio() && $today <= $planmensualgeneral->getEdicionfechafin();
        $actividads = $em->getRepository(ActividadGeneral::class)->findBy(array('planmensualgeneral' => $planmensualgeneral));
        $puntualizaciones = $em->getRepository(PuntualizacionPlanMensualGeneral::class)->findBy(array('plantrabajo' => $planmensualgeneral));
        $objetivos = $em->getRepository(ObjetivoPlanMensualGeneral::class)->findBy(array('plantrabajo' => $planmensualgeneral));

        return $this->render('planmensualgeneral/show.html.twig', ['planmensualgeneral' => $planmensualgeneral,
            'actividads' => $actividads,
            'puntualizaciones' => $puntualizaciones,
            'objetivos' => $objetivos,
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'enTiempo' => $enTiempo,
            'esDirectivo'=>$this->getUser()->esDirectivo()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="planmensualgeneral_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, PlanMensualGeneral $plan): Response
    {
        if (!$request->isXmlHttpRequest() || ($plan->getAprobado()==true && !$this->isGranted('ROLE_DIRECTIVOINSTITUCIONAL')))
            throw $this->createAccessDeniedException();

        $aprobadoOriginal=$plan->getAprobado();
        $form = $this->createForm(PlanMensualGeneralType::class, $plan,
            array('action' => $this->generateUrl('planmensualgeneral_edit', array('id' => $plan->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                if($plan->getAprobado()!=$aprobadoOriginal && $plan->getAprobado()==true)
                    $plan->setAprobadopor($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($plan);
                $em->flush();
                $estado_class=$plan->getAprobado() ? 'danger' : 'success';
                $estado_label=$plan->getAprobado() ? 'Aprobado' : 'Pendiente';
                return $this->json(array('mensaje' => 'El plan mensual fue actualizado satisfactoriamente',
                    'mes' => $plan->getMestoString(),
                    'anno' => $plan->getAnno(),
                    'fechainicio' => $plan->getEdicionfechainicio()->format('d-m-Y'),
                    'fechafin' => $plan->getEdicionfechafin()->format('d-m-Y'),
                    'estado'=>'<span class="m-nav__link-badge m-badge m-badge--'.$estado_class.'">'.$estado_label.'</span>'
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
        if (!$request->isXmlHttpRequest() ||
            !$this->isCsrfTokenValid('delete' . $planmensualgeneral->getId(), $request->query->get('_token'))
            || ($planmensualgeneral->getAprobado()==true && !$this->isGranted('ROLE_DIRECTIVOINSTITUCIONAL')))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($planmensualgeneral);
        $em->flush();
        return $this->json(array('mensaje' => 'El plan mensual fue eliminado satisfactoriamente'));
    }

    /**
     * @Route("/{id}/exportar",options={"expose"=true}, name="planmensualgeneral_exportar")
     */
    public function exportar(PlanMensualGeneral $planmensualgeneral, Pdf $pdf)
    {
        $em = $this->getDoctrine()->getManager();
        $capitulos_array = [];
        $capitulos = $em->getRepository(Capitulo::class)->findBy([], ['numero' => 'ASC']);
        $objetivos = $em->getRepository(ObjetivoPlanMensualGeneral::class)->findBy(array('plantrabajo' => $planmensualgeneral), ['numero' => 'ASC']);
        foreach ($capitulos as $capitulo) {
            $subcapitulos_array = [];
            $subcapitulos = $em->getRepository(Subcapitulo::class)->findBy(['capitulo' => $capitulo], ['numero' => 'ASC']);
            foreach ($subcapitulos as $subcapitulo) {
                $arcs = $em->getRepository(ARC::class)->findBy(['subcapitulo' => $subcapitulo]);
                $arcs_array = [];
                foreach ($arcs as $arc) {
                    $actividades_array = [];
                    $actividades = $em->getRepository(ActividadGeneral::class)->findBy(['areaconocimiento' => $arc, 'planmensualgeneral' => $planmensualgeneral]);
                    foreach ($actividades as $value)
                        $actividades_array[] = ['nombre' => $value->getNombre(), 'fecha' => $value->getFecha(), 'fechaF' => $value->getFechaF(),'lugar' => $value->getLugar(),'dirigen' => $value->getDirigen(),'participan' => $value->getParticipan()];
                    $arcs_array[] = ['nombre' => $arc->getNombre(), 'actividades' => $actividades_array];
                }

                $actividades_array = [];
                $actividades = $em->getRepository(ActividadGeneral::class)->findBy(['areaconocimiento' => null, 'subcapitulo' => $subcapitulo, 'planmensualgeneral' => $planmensualgeneral]);
                foreach ($actividades as $value)
                    $actividades_array[] = ['nombre' => $value->getNombre(), 'fecha' => $value->getFecha(), 'fechaF' => $value->getFechaF(),'lugar' => $value->getLugar(),'dirigen' => $value->getDirigen(),'participan' => $value->getParticipan()];

                $subcapitulos_array[] = ['nombre' => $subcapitulo->getNombre(), 'arcs' => $arcs_array, 'actividades' => $actividades_array];
            }
            $capitulos_array[] = ['nombre' => $capitulo->getNombre(), 'subcapitulos' => $subcapitulos_array];
        }
        $html = $this->renderView('planmensualgeneral/_pdf.html.twig', ['plan' => $planmensualgeneral, 'capitulos' => $capitulos_array, 'objetivos' => $objetivos]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    /**
     * @Route("/{id}/graficoordinacion",options={"expose"=true}, name="planmensualgeneral_graficocoordinacion")
     */
    public function graficocoordinacion(PlanMensualGeneral $planmensualgeneral, Pdf $pdf)
    {
        $em=$this->getDoctrine()->getManager();
        $consulta=$em->createQuery('Select a.nombre, a.fecha, a.fechaF, a.dirigen, a.participan,a.lugar from App:ActividadGeneral a JOIN a.planmensualgeneral p WHERE p.id= :id Order by a.fecha ASC');
        $consulta->setParameter('id',$planmensualgeneral->getId());
        $actividades=$consulta->getResult();
        $result=[];
        foreach ($actividades as $actividad){
            $aux=$actividad["fecha"]->format('d-m-Y');
            $pos=$this->buscarActividad($result,$aux);
            if($pos==-1)
                $result[]=['fecha'=>$aux,'actividad'=>[['nombre'=>$actividad['nombre'],'fecha'=>$actividad['fecha'],'fechaF'=>$actividad['fechaF'],'dirigen'=>$actividad['dirigen'],'participan'=>$actividad['participan'],'lugar'=>$actividad['lugar']]]];
            else
                $result[$pos]['actividad'][]=['nombre'=>$actividad['nombre'],'fecha'=>$actividad['fecha'],'fechaF'=>$actividad['fechaF'],'dirigen'=>$actividad['dirigen'],'participan'=>$actividad['participan'],'lugar'=>$actividad['lugar']];
        }

         $html=$this->renderView('planmensualgeneral/_coordinacionpdf.html.twig',['actividades'=>$result,'anno'=>$planmensualgeneral->getAnno(),'mes'=>$planmensualgeneral->getMesToString(),]);
        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    private function buscarActividad($listado, $fecha){
        $i=0;
        foreach ($listado as $value){
            if($value['fecha']==$fecha)
                return $i;
            $i++;
        }
        return -1;
    }
}
