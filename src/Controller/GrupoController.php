<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Form\GrupoType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use App\Entity\Notificacion;

/**
 * @Route("/grupo")
 */
class GrupoController extends Controller
{
    /**
     * @Route("/{id}/index", name="grupo_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request,Usuario $usuario): Response
    {
        $em = $this->getDoctrine()->getManager();
        $id=$usuario->getId();
        $connection = $em->getConnection();
        $query = $connection->prepare("SELECT g1.* FROM grupo as g1 JOIN usuario as u1 on(g1.creador=u1.id) WHERE u1.id= $id UNION
                                SELECT g2.* FROM solicitud_grupo as sg JOIN grupo as g2 on(sg.grupo=g2.id) JOIN usuario u2 on(sg.usuario=u2.id) WHERE u2.id=$id
        ");
        $query->execute();
        $grupos = $query->fetchAll();

        if ($request->isXmlHttpRequest())
            return $this->render('grupo/_table.html.twig', [
                'grupos' => $grupos,
            ]);

        return $this->render('grupo/index.html.twig', ['grupos' => $grupos,'user_id'=>$id]);
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
    public function show(Grupo $grupo): Response
    {
        $em=$this->getDoctrine()->getManager();
        $parameters=['grupo' => $grupo];
        if($this->getUser()->getId()!=$grupo->getCreador()->getId()){
            $solicitud=$em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario'=>$this->getUser(),
                'grupo'=>$grupo
            ]);

            if(!$solicitud)
                throw new AccessDeniedException();

            if($solicitud->getEstado()==0)
                $parameters['pendiente_confirmacion']=true;
            else
                $parameters['confirmacion_aceptada']=true;
        }

        return $this->render('grupo/show.html.twig', $parameters);
    }

    /**
     * @Route("/{id}/edit", name="grupo_edit", methods="GET|POST",options={"expose"=true})
     */
    public function edit(Request $request, Grupo $grupo): Response
    {
        $creador=$this->getUser()->getId();
        $form = $this->createForm(GrupoType::class, $grupo, array('action' => $this->generateUrl('grupo_edit', array('id' => $grupo->getId()))));
        $form->handleRequest($request);


        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($grupo);

                if($grupo->getCreador()->getId()!=$creador){
                    $notificacion=new Notificacion();
                    $notificacion->setFecha(new \DateTime());
                    $notificacion->setDestinatario($grupo->getCreador());
                    $notificacion->setDescripcion("El usuario ".$this->getUser()." lo asignó comoresponsable del grupo ".$grupo->getNombre());
                    $em->persist($notificacion);
                }
                $em->flush();

                return new JsonResponse(array('mensaje' => 'El grupo fue actualizado satisfactoriamente',
                    'nombre' => $grupo->getNombre(),
                    'escreador'=>$this->getUser()->getId()==$grupo->getCreador()->getId(),
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
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $grupo->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($grupo);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'El grupo fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/{grupo}/confirmarsolicitud", name="grupo_confirmarsolicitud")
     */
    public function confirmarSolicitud(Request $request, Grupo $grupo): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('confirmar' . $grupo->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();

            $solicitud=$em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario'=>$this->getUser(),
                'grupo'=>$grupo
            ]);
            $solicitud->setEstado(1);
            $em->persist($solicitud);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'Su membresía fue confirmada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/{grupo}/rechazarsolicitud", name="grupo_rechazarsolicitud")
     */
    public function rechazarSolicitud(Request $request, Grupo $grupo): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('rechazar' . $grupo->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();

            $solicitud=$em->getRepository('App:SolicitudGrupo')->findOneBy([
                'usuario'=>$this->getUser(),
                'grupo'=>$grupo
            ]);
            $grupo->getIdmiembro()->removeElement($this->getUser());
            $em->remove($solicitud);
            $em->persist($grupo);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'Su membresía fue rechazada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

}
