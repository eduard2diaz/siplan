<?php

namespace App\Controller;

use App\Form\NotificacionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Notificacion;
use App\Entity\Usuario;

/**
 * @Route("/notificacion")
 */
class NotificacionController extends Controller
{

    /**
     * @Route("/", name="notificacion_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->query->get('_format') == 'json') {

                if (null == $this->getUser()->getUltimologout()) {
                    $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()->getId()], ['fecha' => 'DESC'], 5);
                }
                else {
                    $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT n FROM App:Notificacion n JOIN n.destinatario u WHERE u.id= :usuario AND n.fecha>= :fecha');
                    $consulta->setParameters(['usuario' => $this->getUser()->getId(), 'fecha' => $this->getUser()->getUltimologout()]);
                    $consulta->setMaxResults(5);
                    $notificacions = $consulta->getResult();
                }

                return new JsonResponse([
                    'contador' => count($notificacions),
                    'html' => $this->renderView('notificacion/ajax/_json.html.twig', ['notificaciones' => $notificacions])
                ]);
            }

            $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()], ['fecha' => 'DESC']);
            return $this->render('notificacion/_table.html.twig', [
                'notificacions' => $notificacions,
            ]);

        }

        $notificacions = $this->getDoctrine()->getRepository(Notificacion::class)->findBy(['destinatario' => $this->getUser()], ['fecha' => 'DESC']);
        return $this->render('notificacion/index.html.twig', [
            'user_id' => $this->getUser()->getId(),
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
            'user_foto'=>null!=$this->getUser()->getFicheroFoto() ? $this->getUser()->getFicheroFoto()->getRuta() : null,
            'notificacions' => $notificacions]);
    }

    /**
     * @Route("/{id}/show", name="notificacion_show", methods="GET")
     */
    public function show(Request $request, Notificacion $notificacion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW',$notificacion);
        return $this->render('notificacion/_show.html.twig', [
            'notificacion' => $notificacion,
        ]);
    }

    /**
     * @Route("/{id}/delete",name="notificacion_delete")
     */
    public function delete(Request $request, Notificacion $notificacion): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $notificacion->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE',$notificacion);
            $em = $this->getDoctrine()->getManager();
            $em->remove($notificacion);
            $em->flush();
            return new JsonResponse(array('mensaje' => 'La notificación fue eliminada satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

}
