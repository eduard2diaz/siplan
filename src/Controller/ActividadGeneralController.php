<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\ActividadGeneral;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\PlanMensualGeneral;
use App\Entity\Plantrabajo;
use App\Form\ActividadGeneralType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/actividadgeneral")
 */
class ActividadGeneralController extends AbstractController
{

    /**
     * @Route("/{id}/new", name="actividadgeneral_new", methods="GET|POST")
     */
    public function new(Request $request, PlanMensualGeneral $plantrabajo): Response
    {
        $actividadgeneral = new ActividadGeneral();
        $actividadgeneral->setPlanMensualGeneral($plantrabajo);
        $actividadgeneral->setUsuario($this->getUser());

        $this->denyAccessUnlessGranted('NEW', $actividadgeneral);
        $form = $this->createForm(ActividadGeneralType::class, $actividadgeneral, array('action' => $this->generateUrl('actividadgeneral_new', array('id' => $plantrabajo->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if(!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadgeneral);
                $em->flush();
                $this->addFlash('success', "La actividad general fue registrada satisfactoriamente");
                return $this->json(['url' => $this->generateUrl('planmensualgeneral_show',['id'=>$plantrabajo->getId()]),
                ]);
            } else {
                $page = $this->renderView('actividadgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadgeneral' => $actividadgeneral,
                    'plantrabajo'=>$plantrabajo->getId()
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadgeneral/_new.html.twig', [
            'actividadgeneral' => $actividadgeneral,
            'form' => $form->createView(),
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'plantrabajo'=>$plantrabajo->getId()
        ]);
    }

    /**
     * @Route("/{id}/show", name="actividadgeneral_show",options={"expose"=true}, methods="GET")
     */
    public function show(Request $request, ActividadGeneral $actividadgeneral): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $actividadgeneral);
        return $this->render('actividadgeneral/show.html.twig', ['actividad' => $actividadgeneral]);
    }

    /**
     * @Route("/{id}/edit", name="actividadgeneral_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, ActividadGeneral $actividadgeneral): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $actividadgeneral);
        $form = $this->createForm(ActividadGeneralType::class, $actividadgeneral, array('action' => $this->generateUrl('actividadgeneral_edit', array('id' => $actividadgeneral->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if(!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadgeneral);
                $em->flush();
                $this->addFlash('success', "La actividad general fue actualizada satisfactoriamente");
                return $this->json(['url' => $this->generateUrl('planmensualgeneral_show',['id'=>$actividadgeneral->getPlanMensualGeneral()->getId()])]);
            } else {
                $page = $this->renderView('actividadgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadgeneral' => $actividadgeneral,
                    'action' => 'Actualizar',
                    'form_id' => 'actividadgeneral_edit',
                    'plantrabajo'=>$actividadgeneral->getPlanMensualGeneral()->getId()
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadgeneral/_new.html.twig', [
            'actividadgeneral' => $actividadgeneral,
            'title' => 'Editar actividad general',
            'action' => 'Actualizar',
            'form_id' => 'actividadgeneral_edit',
            'form' => $form->createView(),
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'plantrabajo'=>$actividadgeneral->getPlanMensualGeneral()->getId()
        ]);
    }


    /**
     * @Route("/{id}/delete", name="actividadgeneral_delete",options={"expose"=true})
     */
    public function delete(Request $request, ActividadGeneral $actividadgeneral): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $actividadgeneral->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE', $actividadgeneral);

            $em = $this->getDoctrine()->getManager();
            $em->remove($actividadgeneral);
            $em->flush();
            return $this->json(array('mensaje' => "La actividad general fue eliminada satisfactoriamente"));
        }

        throw $this->createAccessDeniedException();
    }

    //ajax

    /**
     * @Route("/{id}/ajax", name="actividadgeneral_plangeneral", methods="GET",options={"expose"=true})
     * Funcionalidad que devuelve el listado de actividades del actual plan general, se utiliza en la clonacion de las mismas
     */
    public function actividadesAjax(Request $request,Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || $this->getDoctrine()->getManager()->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($this->getUser())==null)
            $this->createAccessDeniedException();

        $anno = $plantrabajo->getAnno();
        $mes = $plantrabajo->getMes();
        $parameters = ['mes'=>$mes,'anno'=>$anno,'actividades' => []];

        $planmensualgeneral = $this->getDoctrine()->getRepository(PlanMensualGeneral::class)->findOneBy([
            'mes' => $mes, 'anno' => $anno
        ]);

        if (null != $planmensualgeneral) {
            $actividades = $this->getDoctrine()
                ->getRepository(ActividadGeneral::class)
                ->findBy(array('planmensualgeneral' => $planmensualgeneral), array('fecha' => 'DESC'));

            $parameters['actividades'] = $actividades;
        }

        return $this->render('actividadgeneral/ajax/_actividadesajax.html.twig', $parameters);
    }

    /**
     * @Route("/clonar", name="actividadgeneral_clonar",options={"expose"=true}, methods="POST")
     * Funcionalidad que realiza la clonacion de las actividades del plan general seleccionadas
     */
    public function clonar(Request $request, ValidatorInterface $validator): Response
    {
        if (!$request->isXmlHttpRequest()  || $this->getDoctrine()->getManager()->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($this->getUser())==null)
            throw $this->createAccessDeniedException();


        if (!$request->request->has('array'))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $array = json_decode($request->request->get('array'));
        if (empty($array))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades = $em->createQuery('SELECT a FROM App:ActividadGeneral a WHERE a.id IN (:lista)')->setParameter('lista', $array)->getResult();
        $errores = [];
        $contador = 0;

        $anno = $request->get('anno');
        $mes = $request->get('mes');
        dump($mes);

        $plantrabajo = $em->getRepository(Plantrabajo::class)
            ->findOneBy(array('usuario' => $this->getUser(), 'mes' => $mes, 'anno' => $anno));

        if (!$plantrabajo) {
            $plantrabajo = new Plantrabajo();
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
            if (count($errors) == 0) {
                $em->persist($activity);
                $contador++;
            }else {
                $errores[] = [
                    'nombre' => $activity->getNombre(),
                    'fecha' => $activity->getFecha()->format('d-m-Y H:i'),
                    'fechaf' => $activity->getFechaF()->format('d-m-Y H:i'),
                ];
            }
        }

        $array = [];
        if ($contador > 0) {
            $em->flush();
            if ($contador == count($actividades))
                $array['mensaje'] = 'Las actividades fueron clonadas satisfactoriamente';
            else{
                $array['warning'] = 'Algunas actividades no pudieron ser clonadas';
                $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig',['actividades'=>$errores]);
            }
        } else{
            $array['error'] = 'Las actividades no pudieron ser clonadas';
            $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig',['actividades'=>$errores]);
        }


        return $this->json($array);
    }
}
