<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Form\GrupoType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use App\Services\AreaService;
use App\Services\NotificacionService;
/**
 * @Route("/grupo")
 */
class GrupoController extends AbstractController
{
    /**
     * Listado de grupos a los que pertence o de los cuales es el creador el usuario pasado por parámetro
     *
     * @Route("/{id}/index", name="grupo_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request, Usuario $usuario,AreaService $areaService): Response
    {
        if ($usuario->getId() != $this->getUser()->getId() && !in_array($usuario->getId(), $areaService->subordinadosKey($this->getUser())))
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $id = $usuario->getId();

        $grupos=$usuario->getGrupos();
        foreach ($usuario->getGrupospertenece() as $value){
            $grupos->add($value);
        }

        if ($request->isXmlHttpRequest())
            return $this->render('grupo/_table.html.twig', [
                'grupos' => $grupos,
            ]);

        return $this->render('grupo/index.html.twig', [
            'grupos' => $grupos,
            'user_id' => $id,
            'user_nombre'=>$usuario->getNombre(),
            'user_correo'=>$usuario->getCorreo(),
            'user_foto'=>null!=$usuario->getRutaFoto() ? $usuario->getRutaFoto() : null,
        ]);
    }

    /**
     * @Route("/new", name="grupo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $grupo = new Grupo();
        $grupo->setCreador($this->getUser());
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(GrupoType::class, $grupo, array('action' => $this->generateUrl('grupo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($grupo);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El grupo fue registrado satisfactoriamente',
                    'nombre' => $grupo->getNombre(),
                    'creador' => $grupo->getCreador()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $grupo->getId())->getValue(),
                    'id' => $grupo->getId(),
                ));
            } else {
                $page = $this->renderView('grupo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'grupo' => $grupo,
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('grupo/_new.html.twig', [
            'grupo' => $grupo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="grupo_show", methods="GET",options={"expose"=true})
     */
    public function show(Request $request, Grupo $grupo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();
        
        $this->denyAccessUnlessGranted('SHOW', $grupo);
        $em = $this->getDoctrine()->getManager();
        $parameters = ['grupo' => $grupo];
        if ($this->getUser()->getId() != $grupo->getCreador()->getId()) {
            $solicitud = $em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario' => $this->getUser(),
                'grupo' => $grupo
            ]);

            if ($solicitud != null)
                if ($solicitud->getEstado() == 0)
                    $parameters['pendiente_confirmacion'] = true;
                else
                    $parameters['confirmacion_aceptada'] = true;
        }

        $result=[];
        foreach ($grupo->getIdmiembro() as $value){
            $solicitud = $em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario' => $value,
                'grupo' => $grupo
            ]);
            if(null!=$solicitud)
                $result[]=['usuario'=>$value->getNombre(),'estado'=>$solicitud->getEstado()];
        }

        $parameters['miembros']=$result;

        return $this->render('grupo/show.html.twig', $parameters);
    }

    /**
     * @Route("/{id}/edit", name="grupo_edit", methods="GET|POST",options={"expose"=true})
     */
    public function edit(Request $request, Grupo $grupo, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT', $grupo);
        $creador = $this->getUser()->getId();
        $creadorNombre = $this->getUser()->getNombre();
        $miembrosOriginales=clone $grupo->getIdmiembro();
        $form = $this->createForm(GrupoType::class, $grupo, array('action' => $this->generateUrl('grupo_edit', array('id' => $grupo->getId()))));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($grupo);

                if ($grupo->getCreador()->getId() != $creador) {
                    $message="El usuario " . $creadorNombre . " lo asignó como responsable del grupo " . $grupo->getNombre();
                    $notificacionService->nuevaNotificacion($grupo->getCreador()->getId(),$message,$grupo->getId());
                }

                $message = "El usuario " . $grupo->getCreador()->getNombre() . " lo agregó al grupo " . $grupo->getNombre();
                foreach ($grupo->getIdmiembro() as $miembro) {
                    if ($miembrosOriginales->contains($miembro)){
                      $miembrosOriginales->removeElement($miembro);
                    }
                    else {
                        $notificacionService->nuevaNotificacion($miembro->getId(), $message);
                    }
                }

                $message = "El usuario " . $grupo->getCreador()->getNombre() . " lo eliminó del grupo " . $grupo->getNombre();
                foreach ($miembrosOriginales as $miembro) {
                    $notificacionService->nuevaNotificacion($miembro->getId(), $message);
                }
                $em->flush();

                return new JsonResponse(array('mensaje' => 'El grupo fue actualizado satisfactoriamente',
                    'nombre' => $grupo->getNombre(),
                    'creador' => $grupo->getCreador()->getNombre(),
                    'escreador' => $this->getUser()->getId() == $grupo->getCreador()->getId(),
                    'id' => $grupo->getId(),
                ));
            } else {
                $page = $this->renderView('grupo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'grupo_edit',
                    'grupo' => $grupo,
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('grupo/_new.html.twig', [
            'grupo' => $grupo,
            'title' => 'Editar grupo',
            'action' => 'Actualizar',
            'form_id' => 'grupo_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="grupo_delete",options={"expose"=true})
     */
    public function delete(Request $request, Grupo $grupo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $grupo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $this->denyAccessUnlessGranted('DELETE', $grupo);
            $em = $this->getDoctrine()->getManager();
            $em->remove($grupo);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'El grupo fue eliminado satisfactoriamente'));
    }

    /**
     * Función que permite confirmar la solicitud de pertenencia a un grupo creada por otro usuario que desea añadirlo
     * a dicho grupo
     *
     * @Route("/{grupo}/confirmarsolicitud", name="grupo_confirmarsolicitud")
     */
    public function confirmarSolicitud(Request $request, Grupo $grupo, NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('confirmar' . $grupo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $em = $this->getDoctrine()->getManager();
            $solicitud = $em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario' => $this->getUser(),
                'grupo' => $grupo
            ]);

            if (!$solicitud)
                throw $this->createAccessDeniedException();
            $solicitud->setEstado(1);
            $em->persist($solicitud);
            $em->flush();
            $message="El usuario " . $this->getUser()->getNombre() . " confirmó ser miembro del grupo " . $grupo->getNombre();
            $notificacionService->nuevaNotificacion($grupo->getCreador()->getId(),$message);
            return new JsonResponse(array('mensaje' => 'Su membresía fue confirmada satisfactoriamente'));
    }

    /**
     * Función que permite rechazar la solicitud de pertenencia a un grupo creada por otro usuario que desea añadirlo
     * a dicho grupo. Además se utiliza cuando un usuario que pertenece a un determinado grupo decide darse baja de este
     *
     * @Route("/{grupo}/rechazarsolicitud", name="grupo_rechazarsolicitud")
     */
    public function rechazarSolicitud(Request $request, Grupo $grupo,NotificacionService $notificacionService): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('rechazar' . $grupo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $em = $this->getDoctrine()->getManager();
            $solicitud = $em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario' => $this->getUser(),
                'grupo' => $grupo
            ]);

            if (!$solicitud)
                throw $this->createAccessDeniedException();

            $grupo->getIdmiembro()->removeElement($this->getUser());
            $em->remove($solicitud);
            $em->persist($grupo);
            $em->flush();

            $message="El usuario " . $this->getUser()->getNombre() . " abandonó el grupo " . $grupo->getNombre();
            $notificacionService->nuevaNotificacion($grupo->getCreador()->getId(),$message);
            return new JsonResponse(array('mensaje' => 'Su membresía fue rechazada satisfactoriamente'));
    }

}
