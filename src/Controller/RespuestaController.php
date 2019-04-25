<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\Respuesta;
use App\Entity\Plantrabajo;
use App\Form\RespuestaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\Transformer\DateTimetoStringTransformer;
use App\Entity\Fichero;
use App\Entity\Actividad;

/**
 * @Route("/respuesta")
 */
class RespuestaController extends Controller
{
    /**
     * @Route("/{id}/new", name="respuesta_new", methods="GET|POST")
     */
    public function new(Request $request, Actividad $actividad): Response
    {
        $respuesta = new Respuesta();
        $respuesta->setId($actividad);
//        $this->denyAccessUnlessGranted('NEW', $respuesta);
        $form = $this->createForm(RespuestaType::class, $respuesta, array('action' => $this->generateUrl('respuesta_new', array('id' => $actividad->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');
                $em = $this->getDoctrine()->getManager();
                foreach ($form->getData()->getFicheros() as $value) {
                    $value->setRespuesta($respuesta);
                    $value->subirArchivo($ruta);
                    $value->setNombre($value->getFile()->getClientOriginalName());
                    $em->persist($value);
                }
                $em->persist($respuesta);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La respuesta fue registrada satisfactoriamente"));
            } else {
                $page = $this->renderView('respuesta/_form.html.twig', array(
                    'form' => $form->createView(),
                    'respuesta' => $respuesta,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('respuesta/_new.html.twig', [
            'respuesta' => $respuesta,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="respuesta_show", methods="GET")
     */
    public function show(Respuesta $respuesta): Response
    {
        //$this->denyAccessUnlessGranted('VIEW', $respuesta);
        return $this->render('respuesta/show.html.twig', ['respuesta' => $respuesta]);
    }

    /**
     * @Route("/{id}/edit", name="respuesta_edit", methods="GET|POST")
     */
    public function edit(Request $request, Respuesta $respuesta): Response
    {
      //  $this->denyAccessUnlessGranted('EDIT', $respuesta);
        $form = $this->createForm(RespuestaType::class, $respuesta, array('action' => $this->generateUrl('respuesta_edit', array('id' => $respuesta->getId()->getId()))));
        $ficherosIniciales = $respuesta->getFicheros()->toArray();
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');
                $em = $this->getDoctrine()->getManager();
                foreach ($form->getData()->getFicheros() as $value) {
                    $value->setRespuesta($respuesta);
                    $value->subirArchivo($ruta);
                    $value->setNombre($value->getFile()->getClientOriginalName());
                    $em->persist($value);
                }
                $em->persist($respuesta);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La respuesta fue actualizada satisfactoriamente"));
            } else {
                $page = $this->renderView('respuesta/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'Actualizar',
                    'form_id' => 'respuesta_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('respuesta/_new.html.twig', [
            'respuesta' => $respuesta,
            'title' => 'Editar respuesta',
            'action' => 'Actualizar',
            'form_id' => 'respuesta_edit',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/delete", name="respuesta_delete")
     */
    public function delete(Request $request, Respuesta $respuesta): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $respuesta->getId()->getId(), $request->query->get('_token'))) {
            //  $this->denyAccessUnlessGranted('DELETE', $respuesta);
            $em = $this->getDoctrine()->getManager();
            $em->remove($respuesta);
            $em->flush();
            return new JsonResponse(array('mensaje' => "La respuesta fue eliminada satisfactoriamente"));
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
}
