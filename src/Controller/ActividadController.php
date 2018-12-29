<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\Actividad;
use App\Entity\Plantrabajo;
use App\Form\ActividadType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\Transformer\DateTimetoStringTransformer;

/**
 * @Route({
 *     "en": "/activity",
 *     "es": "/actividad",
 *     "fr": "/activitie",
 * })
 */
class ActividadController extends Controller
{


    /**
     * @Route("/{id}/new", name="actividad_new", methods="GET|POST")
     */
    public function new(Request $request, Plantrabajo $plantrabajo): Response
    {
        $actividad = new Actividad();
        $actividad->setPlantrabajo($plantrabajo);
        $actividad->setResponsable($plantrabajo->getUsuario());
        $actividad->setAsignadapor($this->getUser());
        $this->denyAccessUnlessGranted('NEW', $actividad);
        $form = $this->createForm(ActividadType::class, $actividad, array('disab'=>false,'action' => $this->generateUrl('actividad_new', array('id' => $plantrabajo->getId()))));

        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividad);
                $em->flush();
                return new JsonResponse(array('mensaje' => "La actividad fue registrada satisfactoriamente",
                    'nombre' => $actividad->getNombre(),
                    'esobjetivo' => $actividad->getEsobjetivo(),
                    'fecha' => $actividad->getFecha()->format('d-m-Y H:i'),
                    'fechaf' => $actividad->getFecha()->format('d-m-Y') == $actividad->getFechaF()->format('d-m-Y') ? $actividad->getFechaF()->format('H:i') : $actividad->getFechaF()->format('d-m-Y H:i'),
                    'estadocolor' => $actividad->getEstadoColor(),
                    'estado' => $actividad->getEstadoString(),
                    'id' => $actividad->getId(),
                ));
            } else {
                $page = $this->renderView('actividad/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('actividad/_new.html.twig', [
            'actividad' => $actividad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/clonar", name="actividad_clonar",options={"expose"=true}, methods="POST")
     */
    public function clonar(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();


        if (!$request->request->has('array'))
            return new JsonResponse(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $array=json_decode($request->request->get('array'));
        if (empty($array))
            return new JsonResponse(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades=$em->createQuery('SELECT a FROM App:Actividad a WHERE a.id IN (:lista)')->setParameter('lista',$array)->getResult();

        foreach ($actividades as $actividad) {
            $activity = new Actividad();
            $activity->setResponsable($actividad->getResponsable());
            $activity->setNombre($actividad->getNombre());
            $activity->setFecha($actividad->getFecha());
            $activity->setFechaF($actividad->getFechaF());
            $activity->getFecha()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFecha()->format('d'));
            $activity->getFechaF()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFechaF()->format('d'));
            $activity->setEsexterna($actividad->getEsexterna());
            $activity->setEsobjetivo($actividad->getEsobjetivo());
            $activity->setAsignadapor($actividad->getAsignadapor());
            $activity->setPlantrabajo($plantrabajo);
            $em->persist($activity);
        }
        $em->flush();

        return new JsonResponse(array('mensaje' => 'Las actividades fueron clonadas satisfactoriamente.'));
    }

    /**
     * @Route("/{id}/show", name="actividad_show",options={"expose"=true}, methods="GET")
     */
    public function show(Actividad $actividad): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $actividad);
        return $this->render('actividad/show.html.twig', ['actividad' => $actividad]);
    }

    /**
     * @Route("/{id}/edit", name="actividad_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Actividad $actividad): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $actividad);
        $disabled=$this->getUser()->getId() != $actividad->getAsignadapor()->getId() && (null!=$actividad->getResponsable()->getJefe() && $this->getUser()->getId()!=$actividad->getResponsable()->getJefe()->getId()) ? true : false;
        $form = $this->createForm(ActividadType::class, $actividad, array('disab'=>$disabled,'action' => $this->generateUrl('actividad_edit', array('id' => $actividad->getId()))));

        $choices = ['Registrada' => 1, 'En proceso' => 2, 'Culminada' => 3];

        if ($this->getUser()->getId() == $actividad->getAsignadapor()->getId() || ($actividad->getResponsable()->getJefe()!=null && $actividad->getResponsable()->getJefe()->getId()==$this->getUser()->getId())) {
            $choices['Cumplida'] = 4;
            $choices['Incumplida'] = 5;
        }
     

        $form->add('estado', ChoiceType::class, array(
            'choices' => $choices, 'attr' => array('class' => 'form-control input-medium')));

        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($actividad);
                $em->flush();
                return new JsonResponse(array('mensaje' => "La actividad fue actualizada satisfactoriamente",
                    'nombre' => $actividad->getNombre(),
                    'fecha' => $actividad->getFecha()->format('d-m-Y H:i'),
                    'fechaf' => $actividad->getFecha()->format('d-m-Y') == $actividad->getFechaF()->format('d-m-Y') ? $actividad->getFechaF()->format('H:i') : $actividad->getFechaF()->format('d-m-Y H:i'),
                    'esobjetivo' => $actividad->getEsobjetivo(),
                    'estadocolor' => $actividad->getEstadoColor(),
                    'estado' => $actividad->getEstadoString(),
                ));
            } else {
                $page = $this->renderView('actividad/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'Actualizar',
                      'form_id' => 'actividad_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('actividad/_edit.html.twig', [
            'actividad' => $actividad,
            'title' => 'Editar actividad',
            'action' => 'Actualizar',
            'form_id' => 'actividad_edit',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/delete", name="actividad_delete",options={"expose"=true})
     */
    public function delete(Request $request, Actividad $actividad): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $actividad);

        $em = $this->getDoctrine()->getManager();
        $em->remove($actividad);
        $em->flush();
        return new JsonResponse(array('mensaje' => "La actividad fue eliminada satisfactoriamente"));
    }

    //FUNCIONES AJAX

    /**
     * @Route("/pendientes", name="actividades_pendientes",options={"expose"=true})
     */
    public function pendiente(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $conn = $this->getDoctrine()->getManager()->getConnection();
        //PARA HACER CONSULTAS EN SQL EN CASO DE QUE NO EXISTAN LAS MISMAS PALABRAS RESERVADAS DE SQL EN DQL , PODEMOS UTILIZAR:
        $sql = 'SELECT a.id, a.nombre, a.fecha,a.esobjetivo FROM actividad a join usuario r on(a.responsable=r.id) WHERE r.id=:id AND  DATE(fecha)<=(CURRENT_DATE + INTERVAL  \'3 day\' )AND a.estado!=3 AND a.estado!=4 ORDER BY a.fecha ASC, a.estado DESC LIMIT 5';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $this->getUser()->getId()]);
        $actividades=$stmt->fetchAll();
        $total=count($actividades);
        return new JsonResponse(array('total' => $total,'html'=>$this->renderView('actividad/ajax/_notificaciones.html.twig',['actividades'=>$actividades])));
    }



    /**
     * @Route("/{id}/ajax", name="plantrabajo_actividadesajax", methods="GET",options={"expose"=true})
     */
    public function actividadesAjax(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            $this->createAccessDeniedException();

        $actividades = $this->getDoctrine()
            ->getRepository(Actividad::class)
            ->findBy(array('plantrabajo' => $plantrabajo, 'asignadapor' => $this->getUser()), array('fecha' => 'DESC'));

        return $this->render('actividad/ajax/_actividadesajax.html.twig', [
            'actividades' => $actividades,
        ]);
    }


}
