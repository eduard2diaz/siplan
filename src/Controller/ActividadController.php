<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\Plantrabajo;
use App\Form\ActividadGrupoType;
use App\Form\ActividadType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Fichero;

/**
 * @Route("/actividad")
 */
class ActividadController extends Controller
{

    /**
     * @Route("/nueva", name="actividad_nueva", methods="GET|POST")
     */
    public function nueva(Request $request): Response
    {
        $actividad = new Actividad();
        $actividad->setAsignadapor($this->getUser());
        $ruta = $this->getParameter('storage_directory');
        $em = $this->getDoctrine()->getManager();
        $validator = $this->get('validator');

        $fs = new Filesystem();
        $form = $this->createForm(ActividadGrupoType::class, $actividad, array('action' => $this->generateUrl('actividad_nueva')));
        $form->handleRequest($request);
        $registrado = 0;
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $mes = $actividad->getFecha()->format('m');
                $anno = $actividad->getFecha()->format('Y');
                $iteracion = 1;
                $nombreficheros = [];
                foreach ($request->request->get('actividad_grupo')['iddestinatario'] as $destinatario) {
                    $activity = clone $actividad;
                    $destinatario = $em->getRepository('App:Usuario')->find($destinatario);
                    $activity->setResponsable($destinatario);
                    $plantrabajo = $em->getRepository(Plantrabajo::class)->findOneBy(['mes' => $mes, 'anno' => $anno, 'usuario' => $destinatario]);
                    if (null == $plantrabajo) {
                        $plantrabajo = new Plantrabajo();
                        $plantrabajo->setMes($mes);
                        $plantrabajo->setAnno($anno);
                        $plantrabajo->setUsuario($destinatario);
                    }

//                    if (count($validator->validate($activity)) > 0)
                    //                      break;

                    $em->persist($plantrabajo);
                    $em->persist($activity);
                    $activity->setPlantrabajo($plantrabajo);
                    $em->flush();

                    if ($iteracion == 1)
                        foreach ($form->getData()->getFicheros() as $value) {
                            $value->subirArchivo($ruta);
                            $nombreficheros[] = ['nombre' => $value->getNombre(), 'ruta' => $value->getRuta()];
                            $value->setActividad($activity);
                            $em->persist($value);
                        }
                    else {
                        foreach ($nombreficheros as $value) {
                            $fichero = new Fichero();
                            $fichero->setActividad($activity);
                            $fichero->setNombre($value['nombre']);
                            $rutaArchivo = uniqid('siplan') . $value['nombre'];
                            $fichero->setRuta($rutaArchivo);
                            $fs->copy($ruta . DIRECTORY_SEPARATOR . $value['ruta'], $ruta . DIRECTORY_SEPARATOR . $rutaArchivo);
                            $em->persist($fichero);
                        }


                    }

                    $iteracion++;
                }

                $em->flush();
                $message = "La actividad fue registrada satisfactoriamente";
                if ($iteracion == 0)
                    $message = "La actividad no pudo ser asignada a los usuarios, comprueba los planes de trabajo";
                elseif ($iteracion < count($request->request->get('actividad_grupo')['iddestinatario']))
                    $message = "Algunas actividades no pudieron ser asignadas a los usuarios, comprueba los planes de trabajo";


                return new JsonResponse(array('mensaje' => $message));
            } else {
                $page = $this->renderView('actividad/_form2.html.twig', array(
                    'form' => $form->createView(),
                    'actividad' => $actividad,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }


        return $this->render('actividad/_new2.html.twig', [
            'actividad' => $actividad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="actividad_new", methods="GET|POST")
     * Se encarga del registro de una actividad por un usuario en el plan de trabajo(SOLO PARA UN USUARIO)
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
                    $value->subirArchivo($ruta);
                    $value->setActividad($actividad);
                    $em->persist($value);
                }
                $em->persist($actividad);
                $em->flush();
                $this->addFlash('success', "La actividad fue registrada satisfactoriamente");
                return new JsonResponse(['url' => $this->generateUrl('plantrabajo_show', ['id' => $plantrabajo->getId()])]);
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
            'user_id' => $plantrabajo->getUsuario()->getId(),
            'user_foto' => null != $plantrabajo->getUsuario()->getRutaFoto() ? $plantrabajo->getUsuario()->getRutaFoto() : null,
            'user_nombre' => $plantrabajo->getUsuario()->getNombre(),
            'user_correo' => $plantrabajo->getUsuario()->getCorreo(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="actividad_show",options={"expose"=true}, methods="GET")
     */
    public function show(Actividad $actividad): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $actividad);

        $em = $this->getDoctrine()->getManager();
        $respuesta = $em->getRepository('App:Respuesta')->find($actividad);
        return $this->render('actividad/show.html.twig', ['actividad' => $actividad, 'existeRespuesta' => null != $respuesta]);
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
                    $value->subirArchivo($ruta);
                    $em->persist($value);
                }

                $em->persist($actividad);
                $em->flush();
                $this->addFlash('success', "La actividad fue actualizada satisfactoriamente");
                return new JsonResponse(['url' => $this->generateUrl('plantrabajo_show', ['id' => $actividad->getPlantrabajo()->getId()])]);
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
            'user_id' => $actividad->getPlantrabajo()->getUsuario()->getId(),
            'user_foto' => null != $actividad->getPlantrabajo()->getUsuario()->getRutaFoto() ? $actividad->getPlantrabajo()->getUsuario()->getRutaFoto() : null,
            'user_nombre' => $actividad->getPlantrabajo()->getUsuario()->getNombre(),
            'user_correo' => $actividad->getPlantrabajo()->getUsuario()->getCorreo(),
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
        $errores = [];
        $validator = $this->get('validator');
        $contador = 0;
        foreach ($actividades as $actividad) {
            $activity = new Actividad();
            $activity->setResponsable($actividad->getResponsable());
            $activity->setAseguramiento($actividad->getAseguramiento());
            $activity->setDirigen($actividad->getDirigen());
            $activity->setParticipan($actividad->getParticipan());
            $activity->setLugar($actividad->getLugar());
            $activity->setNombre($actividad->getNombre());
            $activity->setFecha($actividad->getFecha());
            $activity->setFechaF($actividad->getFechaF());
            $activity->getFecha()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFecha()->format('d'));
            $activity->getFechaF()->setDate($plantrabajo->getAnno(), $plantrabajo->getMes(), $actividad->getFechaF()->format('d'));
            $activity->setAsignadapor($actividad->getAsignadapor());
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
                $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig',['actividades'=>$errores]);
            }
        } else {
            $array['error'] = 'Las actividades no pudieron ser clonadas';
            $array['errores'] = $this->renderView('actividad/ajax/_errorclonacion.html.twig',['actividades'=>$errores]);
        }


        return new JsonResponse($array);
    }


    /**
     * @Route("/{id}/delete", name="actividad_delete",options={"expose"=true})
     */
    public function delete(Request $request, Actividad $actividad): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $actividad->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE', $actividad);
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
     * Funcionalidad ajax que retorna el listado de actividades pendientes(NO CUMPLIDAS) 3 dias antes de que ocurran
     */
    public function pendiente(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $conn = $this->getDoctrine()->getManager()->getConnection();
        //PARA HACER CONSULTAS EN SQL EN CASO DE QUE NO EXISTAN LAS MISMAS PALABRAS RESERVADAS DE SQL EN DQL , PODEMOS UTILIZAR:
        $sql = 'SELECT a.id, a.nombre, a.fecha FROM actividad a join usuario r on(a.responsable=r.id) WHERE r.id=:id AND  DATE(fecha)<=(CURRENT_DATE + INTERVAL  \'3 day\' )AND a.estado!=3 AND a.estado!=4 ORDER BY a.fecha ASC, a.estado DESC LIMIT 5';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $this->getUser()->getId()]);
        $actividades = $stmt->fetchAll();
        $total = count($actividades);
        return new JsonResponse(array('total' => $total, 'html' => $this->renderView('actividad/ajax/_notificaciones.html.twig', ['actividades' => $actividades])));
    }


    /**
     * @Route("/{id}/ajax", name="plantrabajo_actividadesajax", methods="GET",options={"expose"=true})
     * Funcionalidad que devuelve el listado de actividades que pertenecen a un determinado plan de trabajo
     */
    public function actividadesAjax(Request $request, Plantrabajo $plantrabajo): Response
    {

        if (!$request->isXmlHttpRequest())
            $this->createAccessDeniedException();

        $actividades = $this->getDoctrine()
            ->getRepository(Actividad::class)
            ->findBy(array('plantrabajo' => $plantrabajo, 'asignadapor' => $this->getUser(), 'actividadGeneral' => null), array('fecha' => 'DESC'));

        return $this->render('actividad/ajax/_actividadesajax.html.twig', [
            'actividades' => $actividades,
        ]);
    }


}
