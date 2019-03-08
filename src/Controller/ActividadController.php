<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Actividad;
use App\Entity\Plantrabajo;
use App\Form\ActividadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Fichero;

/**
 * @Route("/actividad")
 */
class ActividadController extends AbstractController
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
        $form = $this->createForm(ActividadType::class, $actividad, array('action' => $this->generateUrl('actividad_new', array('id' => $plantrabajo->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');
                $em = $this->getDoctrine()->getManager();
                foreach ($form->getData()->getFicheros() as $value) {
                    $value->setActividad($actividad);

                    $em->persist($value);
                }
                $em->persist($actividad);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La actividad fue registrada satisfactoriamente",
                    'nombre' => $actividad->getNombre(),
                    'fecha' => $actividad->getFecha()->format('d-m-Y H:i'),
                    'fechaF' => $actividad->getFechaF()->format('d-m-Y H:i'),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $actividad->getId())->getValue(),
                    'id' => $actividad->getId(),
                ));
            } else {
                $page = $this->renderView('actividad/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividad' => $actividad,
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

        $array = json_decode($request->request->get('array'));
        if (empty($array))
            return new JsonResponse(array('error' => true, 'mensaje' => 'Seleccione las actividades a clonar'));

        $em = $this->getDoctrine()->getManager();

        $actividades = $em->createQuery('SELECT a FROM App:Actividad a WHERE a.id IN (:lista)')->setParameter('lista', $array)->getResult();
        $validator=$this->get('validator');
        $errores=[];
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

            $errors = $validator->validate($actividad);
            if(count($errors)==0)
                $em->persist($activity);
            else
                $errores[]='La actividad '.$activity->getNombre().' no puedo ser clonada';

        }
        dump($errores);
        $em->flush();

        return new JsonResponse(array('mensaje' => 'Las actividades fueron clonadas satisfactoriamente.','errors'=>$errores));
    }

    /**
     * @Route("/{id}/show", name="actividad_show",options={"expose"=true}, methods="GET")
     */
    public function show(Actividad $actividad): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $actividad);

        $em=$this->getDoctrine()->getManager();
        $respuesta=$em->getRepository('App:Respuesta')->find($actividad);
        return $this->render('actividad/show.html.twig', ['actividad' => $actividad,'existeRespuesta'=>null!=$respuesta]);
    }

    /**
     * @Route("/{id}/edit", name="actividad_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Actividad $actividad): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $actividad);
        $form = $this->createForm(ActividadType::class, $actividad, array('action' => $this->generateUrl('actividad_edit', array('id' => $actividad->getId()))));
        $ficherosIniciales = $actividad->getFicheros()->toArray();
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');
                $em = $this->getDoctrine()->getManager();

                foreach ($form->getData()->getFicheros() as $value) {
                    $value->setActividad($actividad);
                    $value->setRuta($value->subirArchivo($ruta));
                    $value->setNombre($value->getFile()->getClientOriginalName());
                    $em->persist($value);
                }

                $em->persist($actividad);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La actividad fue actualizada satisfactoriamente",
                    'nombre' => $actividad->getNombre(),
                    'fecha' => $actividad->getFecha()->format('d-m-Y H:i'),
                    'fechaF' => $actividad->getFechaF()->format('d-m-Y H:i')
                ));
            } else {
                $page = $this->renderView('actividad/_form.html.twig', array(
                    'form' => $form->createView(),
                    'actividad' => $actividad,
                    'action' => 'Actualizar',
                    'form_id' => 'actividad_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('actividad/_new.html.twig', [
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
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $actividad->getId(), $request->query->get('_token'))) {
            //  $this->denyAccessUnlessGranted('DELETE', $actividad);
            $em = $this->getDoctrine()->getManager();
            $em->remove($actividad);
            $em->flush();
            return new JsonResponse(array('mensaje' => "La actividad fue eliminada satisfactoriamente"));
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/{id}/ficherodelete", name="fichero_delete",options={"expose"=true})
     */
    public function ficheroDelete(Request $request, Fichero $fichero): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $fichero->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($fichero);
            $em->flush();
            return new JsonResponse(array('mensaje' => "El fichero fue eliminado satisfactoriamente"));
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/{id}/ficherodownload", name="fichero_download")
     */
    public function downloadAction(Fichero $fichero)
    {
        $ruta = $this->getParameter('storage_directory') . DIRECTORY_SEPARATOR . $fichero->getRuta();

        if (!file_exists($ruta))
            throw $this->createNotFoundException();

        $archivo = file_get_contents($ruta);
        return new Response($archivo, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Transfer-Encoding' => 'binary',
            'Content-length' => strlen($archivo),
            'Pragma' => 'no-cache',
            'Expires' => '0'));
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
        $actividades = $stmt->fetchAll();
        $total = count($actividades);
        return new JsonResponse(array('total' => $total, 'html' => $this->renderView('actividad/ajax/_notificaciones.html.twig', ['actividades' => $actividades])));
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
