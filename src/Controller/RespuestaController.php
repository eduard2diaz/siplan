<?php

namespace App\Controller;

use App\Entity\Respuesta;
use App\Form\RespuestaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Fichero;
use App\Entity\Actividad;

/**
 * @Route("/respuesta")
 */
class RespuestaController extends AbstractController
{
    /**
     * @Route("/{id}/new", name="respuesta_new", methods="GET|POST")
     */
    public function new(Request $request, Actividad $actividad): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $existeRespuesta = $em->getRepository('App:Respuesta')->find($actividad) != null;
        if ($existeRespuesta == true)
            throw $this->createAccessDeniedException();

        $respuesta = new Respuesta();
        $respuesta->setId($actividad);
        $this->denyAccessUnlessGranted('NEW', $respuesta);
        $form = $this->createForm(RespuestaType::class, $respuesta, array('action' => $this->generateUrl('respuesta_new', array('id' => $actividad->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $ruta = $this->getParameter('storage_directory');

                foreach ($form->getData()->getFicheros() as $value) {
                    $value->setRespuesta($respuesta);
                    $value->subirArchivo($ruta);
                    $value->setNombre($value->getFile()->getClientOriginalName());
                    $em->persist($value);
                }
                $em->persist($respuesta);
                $em->flush();

                return new JsonResponse(array('mensaje' => "La respuesta fue registrada satisfactoriamente",
                    'href' => $this->generateUrl('respuesta_edit', ['id' => $respuesta->getId()->getId()])
                ));
            } else {
                $page = $this->renderView('respuesta/_form.html.twig', array(
                    'form' => $form->createView(),
                    'respuesta' => $respuesta,
                    'existeRespuesta' => $existeRespuesta
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('respuesta/_new.html.twig', [
            'respuesta' => $respuesta,
            'form' => $form->createView(),
            'existeRespuesta' => $existeRespuesta
        ]);
    }

    /**
     * @Route("/{id}/show", name="respuesta_show", methods="GET")
     */
    public function show(Request $request, Respuesta $respuesta): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $respuesta);
        return $this->render('respuesta/show.html.twig', ['respuesta' => $respuesta]);
    }

    /**
     * @Route("/{id}/edit", name="respuesta_edit", methods="GET|POST")
     */
    public function edit(Request $request, Respuesta $respuesta): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT', $respuesta);
        $form = $this->createForm(RespuestaType::class, $respuesta, array('action' => $this->generateUrl('respuesta_edit', array('id' => $respuesta->getId()->getId()))));
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
                    'existeRespuesta' => true
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('respuesta/_new.html.twig', [
            'respuesta' => $respuesta,
            'title' => 'Editar respuesta',
            'action' => 'Actualizar',
            'form_id' => 'respuesta_edit',
            'form' => $form->createView(),
            'existeRespuesta' => true
        ]);
    }


    /**
     * @Route("/{id}/delete", name="respuesta_delete")
     */
    public function delete(Request $request, Respuesta $respuesta): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $respuesta->getId()->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $respuesta);
        $actividad_id = $respuesta->getId()->getId();
        $em = $this->getDoctrine()->getManager();
        $em->remove($respuesta);
        $em->flush();
        return new JsonResponse(array('mensaje' => "La respuesta fue eliminada satisfactoriamente",
            'href' => $this->generateUrl('respuesta_new', ['id' => $actividad_id])
        ));


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
