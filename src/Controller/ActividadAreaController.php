<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\ActividadArea;
use App\Entity\PlanMensualArea;
use App\Form\ActividadAreaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Plantrabajo;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/actividadarea")
 */
class ActividadAreaController extends AbstractController
{

    /**
     * @Route("/{id}/new", name="actividadarea_new", methods="GET|POST")
     */
    public function new(Request $request, PlanMensualArea $plantrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);
        $actividadarea = new ActividadArea();
        $actividadarea->setPlanMensualArea($plantrabajo);
        $actividadarea->setUsuario($this->getUser());
        $form = $this->createForm(ActividadAreaType::class, $actividadarea, array('action' => $this->generateUrl('actividadarea_new', array('id' => $plantrabajo->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadarea);
                $em->flush();
                $this->addFlash('success', "La actividad area fue registrada satisfactoriamente");
                return $this->json(['url' => $this->generateUrl('planmensualarea_show', ['id' => $plantrabajo->getId()]),
                ]);
            } else {
                $page = $this->renderView('actividadarea/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadarea' => $actividadarea,
                    'plantrabajo' => $plantrabajo->getId(),
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadarea/_new.html.twig', [
            'actividadarea' => $actividadarea,
            'form' => $form->createView(),
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'plantrabajo' => $plantrabajo->getId(),
            'esDirectivo'=>$this->getUser()->esDirectivo()
        ]);
    }

    /**
     * @Route("/{id}/show", name="actividadarea_show",options={"expose"=true}, methods="GET")
     */
    public function show(Request $request, ActividadArea $actividadarea): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $actividadarea->getPlanMensualArea());
        return $this->render('actividadarea/show.html.twig', ['actividad' => $actividadarea]);
    }

    /**
     * @Route("/{id}/edit", name="actividadarea_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, ActividadArea $actividadarea): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $actividadarea->getPlanMensualArea());
        $form = $this->createForm(ActividadAreaType::class, $actividadarea, array('action' => $this->generateUrl('actividadarea_edit', array('id' => $actividadarea->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if (!$request->isXmlHttpRequest())
                throw $this->createAccessDeniedException();
            elseif ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividadarea);
                $em->flush();
                $this->addFlash('success', "La actividad area fue actualizada satisfactoriamente");
                return $this->json(['url' => $this->generateUrl('planmensualarea_show', ['id' => $actividadarea->getPlanMensualArea()->getId()])]);
            } else {
                $page = $this->renderView('actividadarea/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividadarea' => $actividadarea,
                    'action' => 'Actualizar',
                    'form_id' => 'actividadarea_edit',
                    'plantrabajo' => $actividadarea->getPlanMensualArea()->getId()
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('actividadarea/_new.html.twig', [
            'actividadarea' => $actividadarea,
            'title' => 'Editar actividad area',
            'action' => 'Actualizar',
            'form_id' => 'actividadarea_edit',
            'form' => $form->createView(),
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'plantrabajo' => $actividadarea->getPlanMensualArea()->getId(),
            'esDirectivo'=>$this->getUser()->esDirectivo()
        ]);
    }


    /**
     * @Route("/{id}/delete", name="actividadarea_delete",options={"expose"=true})
     */
    public function delete(Request $request, ActividadArea $actividadarea): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $actividadarea->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $actividadarea->getPlanMensualArea());
        $em = $this->getDoctrine()->getManager();
        $em->remove($actividadarea);
        $em->flush();
        return $this->json(array('mensaje' => "La actividad area fue eliminada satisfactoriamente"));
    }

    //ajax

    /**
     * @Route("/{id}/ajax", name="actividadarea_planmensual", methods="GET",options={"expose"=true})
     * Funcionalidad que devuelve el listado de actividades del actual plan mensual, se utiliza en la clonacion de las mismas
     */
    public function actividadesAjax(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            $this->createAccessDeniedException();

        $anno = $plantrabajo->getAnno();
        $mes = $plantrabajo->getMes();
        $parameters = ['mes' => $mes, 'anno' => $anno, 'actividades' => []];

        $planmensualarea = $this->getDoctrine()->getRepository(PlanMensualArea::class)->findOneBy([
            'mes' => $mes, 'anno' => $anno, 'area' => $plantrabajo->getUsuario()->getArea()
        ]);

        if (null != $planmensualarea) {
            $actividades = $this->getDoctrine()
                ->getRepository(ActividadArea::class)
                ->findBy(array('planmensualarea' => $planmensualarea), array('fecha' => 'DESC'));

            $parameters['actividades'] = $actividades;
        }

        return $this->render('actividadarea/ajax/_actividadesajax.html.twig', $parameters);
    }

    /**
     * @Route("/{id}/areapadre", name="actividadareapadre_planmensual", methods="GET",options={"expose"=true})
     * Funcionalidad que devuelve el listado de actividades del actual plan mensual en el area padre de mi area
     */
    public function actividadesAreaPadre(Request $request, PlanMensualArea $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || $plantrabajo->getArea()->getPadre()==null)
            $this->createAccessDeniedException();

        $anno = $plantrabajo->getAnno();
        $mes = $plantrabajo->getMes();
        $parameters = ['mes' => $mes, 'anno' => $anno, 'actividades' => []];


        $planmensualarea = $this->getDoctrine()->getRepository(PlanMensualArea::class)->findOneBy([
            'mes' => $mes, 'anno' => $anno, 'area' => $plantrabajo->getArea()->getPadre()
        ]);

        if (null != $planmensualarea) {
            $actividades = $this->getDoctrine()
                ->getRepository(ActividadArea::class)
                ->findBy(array('planmensualarea' => $planmensualarea), array('fecha' => 'DESC'));

            $parameters['actividades'] = $actividades;
        }

        return $this->render('actividadarea/ajax/_actividadesareapadreajax.html.twig', $parameters);
    }

    /**
     * @Route("/clonar", name="actividadarea_clonar",options={"expose"=true}, methods="POST")
     * Funcionalidad que realiza la clonacion de las actividades del plan mensual seleccionadas
     */
    public function clonar(Request $request, ValidatorInterface $validator): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        if (!$request->request->has('array'))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $array = json_decode($request->request->get('array'));
        if (empty($array))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades = $em->createQuery('SELECT a FROM App:ActividadArea a WHERE a.id IN (:lista)')->setParameter('lista', $array)->getResult();
        $errores = [];
        $contador = 0;

        $anno = $request->get('anno');
        $mes = $request->get('mes');

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
            $activity->setPlantrabajo($plantrabajo);

            $errors = $validator->validate($activity);
            if (count($errors) == 0) {
                $em->persist($activity);
                $contador++;
            } else {
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
            else {
                $array['warning'] = 'Algunas actividades no pudieron ser clonadas';
                $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig', ['actividades' => $errores]);
            }
        } else {
            $array['error'] = 'Las actividades no pudieron ser clonadas';
            $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig', ['actividades' => $errores]);
        }


        return $this->json($array);
    }

    /**
     * @Route("/areapadreclonar", name="actividadareapadre_clonar",options={"expose"=true}, methods="POST")
     * Funcionalidad que realiza la clonacion de las actividades del plan mensual seleccionadas
     */
    public function areaPadreClonar(Request $request, ValidatorInterface $validator): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        if (!$request->request->has('array'))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $array = json_decode($request->request->get('array'));
        if (empty($array))
            return $this->json(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades = $em->createQuery('SELECT a FROM App:ActividadArea a WHERE a.id IN (:lista)')->setParameter('lista', $array)->getResult();
        $errores = [];
        $contador = 0;

        $anno = $request->get('anno');
        $mes = $request->get('mes');

        $plantrabajo = $em->getRepository(PlanMensualArea::class)
            ->findOneBy(array('area' => $this->getUser()->getArea(), 'mes' => $mes, 'anno' => $anno));

        foreach ($actividades as $actividad) {
            $activity = new ActividadArea();
            $activity->setUsuario($this->getUser());
            $activity->setNombre($actividad->getNombre());
            $activity->setAseguramiento($actividad->getAseguramiento());
            $activity->setDirigen($actividad->getDirigen());
            $activity->setParticipan($actividad->getParticipan());
            $activity->setLugar($actividad->getLugar());
            $activity->setNombre($actividad->getNombre());

            $activity->setFecha($actividad->getFecha());
            $activity->setFechaF($actividad->getFechaF());
            $activity->getFecha()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFecha()->format('d'));
            $activity->getFechaF()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFechaF()->format('d'));
            $activity->setPlanMensualArea($plantrabajo);


            $errors = $validator->validate($activity);
            if (count($errors) == 0) {
                $em->persist($activity);
                $contador++;
            } else {
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
            else {
                $array['warning'] = 'Algunas actividades no pudieron ser clonadas';
                $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig', ['actividades' => $errores]);
            }
        } else {
            $array['error'] = 'Las actividades no pudieron ser clonadas';
            $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig', ['actividades' => $errores]);
        }


        return $this->json($array);
    }
}
