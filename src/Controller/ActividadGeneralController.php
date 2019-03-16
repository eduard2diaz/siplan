<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\ActividadGeneral;
use App\Entity\PlanMensualGeneral;
use App\Entity\Plantrabajo;
use App\Form\ActividadGeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/actividadgeneral")
 */
class ActividadGeneralController extends Controller
{

    /**
     * @Route("/{id}/new", name="actividadgeneral_new", methods="GET|POST")
     */
    public function new(Request $request, PlanMensualGeneral $plantrabajo): Response
    {
        $actividadgeneral = new ActividadGeneral();

        $actividadgeneral->setPlanMensualGeneral($plantrabajo);
        $form = $this->createForm(ActividadGeneralType::class, $actividadgeneral, array('action' => $this->generateUrl('actividadgeneral_new', array('id' => $plantrabajo->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadgeneral);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La actividad general fue registrada satisfactoriamente",
                    'nombre' => $actividadgeneral->getNombre(),
                    'fecha' => $actividadgeneral->getFecha()->format('d-m-Y H:i'),
                    'fechaF' => $actividadgeneral->getFechaF()->format('d-m-Y H:i'),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $actividadgeneral->getId())->getValue(),
                    'id' => $actividadgeneral->getId(),
                ));
            } else {
                $page = $this->renderView('actividadgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadgeneral' => $actividadgeneral,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadgeneral/_new.html.twig', [
            'actividadgeneral' => $actividadgeneral,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="actividadgeneral_show",options={"expose"=true}, methods="GET")
     */
    public function show(ActividadGeneral $actividadgeneral): Response
    {
        $em=$this->getDoctrine()->getManager();
        return $this->render('actividadgeneral/show.html.twig', ['actividad' => $actividadgeneral]);
    }

    /**
     * @Route("/{id}/edit", name="actividadgeneral_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, ActividadGeneral $actividadgeneral): Response
    {
        $form = $this->createForm(ActividadGeneralType::class, $actividadgeneral, array('action' => $this->generateUrl('actividadgeneral_edit', array('id' => $actividadgeneral->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadgeneral);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La actividad general fue actualizada satisfactoriamente",
                    'nombre' => $actividadgeneral->getNombre(),
                    'fecha' => $actividadgeneral->getFecha()->format('d-m-Y H:i'),
                    'fechaF' => $actividadgeneral->getFechaF()->format('d-m-Y H:i')
                ));
            } else {
                $page = $this->renderView('actividadgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadgeneral' => $actividadgeneral,
                    'action' => 'Actualizar',
                    'form_id' => 'actividadgeneral_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadgeneral/_new.html.twig', [
            'actividadgeneral' => $actividadgeneral,
            'title' => 'Editar actividad general',
            'action' => 'Actualizar',
            'form_id' => 'actividadgeneral_edit',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/delete", name="actividadgeneral_delete",options={"expose"=true})
     */
    public function delete(Request $request, ActividadGeneral $actividadgeneral): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $actividadgeneral->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($actividadgeneral);
            $em->flush();
            return new JsonResponse(array('mensaje' => "La actividad general fue eliminada satisfactoriamente"));
        }

        throw $this->createAccessDeniedException();
    }

    //ajax
    /**
     * @Route("/ajax", name="actividadgeneral_plangeneral", methods="GET",options={"expose"=true})
     */
    public function actividadesAjax(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            $this->createAccessDeniedException();

        $anno=date('Y');
        $mes=(integer)date('m');
        $parameters=['actividades'=>[]];

        $planmensualgeneral=$this->getDoctrine()->getRepository(PlanMensualGeneral::class)->findOneBy([
           'mes'=>$mes,'anno'=>$anno
        ]);

        if(null!=$planmensualgeneral){
            $actividades = $this->getDoctrine()
                ->getRepository(ActividadGeneral::class)
                ->findBy(array('planmensualgeneral'=>$planmensualgeneral), array('fecha' => 'DESC'));

            $parameters['actividades']=$actividades;
        }

        return $this->render('actividadgeneral/ajax/_actividadesajax.html.twig', $parameters);
    }

    /**
     * @Route("/clonar", name="actividadgeneral_clonar",options={"expose"=true}, methods="POST")
     */
    public function clonar(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();


        if (!$request->request->has('array'))
            return new JsonResponse(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $array = json_decode($request->request->get('array'));
        if (empty($array))
            return new JsonResponse(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades = $em->createQuery('SELECT a FROM App:ActividadGeneral a WHERE a.id IN (:lista)')->setParameter('lista', $array)->getResult();
        $errores=[];
        $validator=$this->get('validator');
        $contador=0;

        $anno=date('Y');
        $mes=(integer)date('m');

        $plantrabajo = $em->getRepository(Plantrabajo::class)
                       ->findOneBy(array('usuario' => $this->getUser(),'mes'=>$mes,'anno'=>$anno));

        if(!$plantrabajo){
            $plantrabajo=new Plantrabajo();
            $plantrabajo->setAnno($anno);
            $plantrabajo->setMes($mes);
            $plantrabajo->setUsuario($this->getUser());
            $em->persist($plantrabajo);
            $em->flush();
        }

        foreach ($actividades as $actividad) {
            $activity = new Actividad();
            $activity->setResponsable($this->getUser());
            $activity->setAseguramiento($actividad->getAseguramiento());
            $activity->setDirigen($actividad->getDirigen());
            $activity->setParticipan($actividad->getParticipan());
            $activity->setLugar($actividad->getLugar());
            $activity->setNombre($actividad->getNombre());

            $activity->setFecha($actividad->getFecha());
            $activity->setFechaF($actividad->getFechaF());
            $activity->getFecha()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFecha()->format('d'));
            $activity->getFechaF()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFechaF()->format('d'));
            $activity->setAsignadapor($this->getUser());
            $activity->setActividadGeneral($actividad);
            $activity->setPlantrabajo($plantrabajo);

            $errors = $validator->validate($activity);
            if(count($errors)==0) {
                $em->persist($activity);
                $contador++;
            }

        }

        $array=[];
        if($contador>0) {
            $em->flush();
            if($contador==count($actividades))
                $array['mensaje']='Las actividades fueron clonadas satisfactoriamente';
            else
                $array['warning']='Algunas actividades no pudieron ser clonadas';
        }else
            $array['error']='Las actividades no pudieron ser clonadas';

        return new JsonResponse($array);
    }
}
