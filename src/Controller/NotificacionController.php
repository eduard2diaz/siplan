<?php

namespace App\Controller;

use App\Form\NotificacionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Notificacion;

/**
 * @Route("/notificacion")
 */
class NotificacionController extends AbstractController
{
    /**
     * @Route("/", name="notificacion_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {

            //Obtengo las notificaciones recientes
            if ($request->query->get('_format') == 'json') {

                if (null == $this->getUser()->getUltimologout()) {
                    $notificacions = $this->getDoctrine()->getManager()
                        ->createQuery('SELECT n FROM App:Notificacion n JOIN n.destinatario d WHERE d.id= :id AND n.leida= FALSE ORDER By n.fecha DESC')
                        ->setParameters(array('id' => $this->getUser()->getId()))
                        ->setMaxResults(5)
                        ->getResult();
                } else {
                    $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT n FROM App:Notificacion n JOIN n.destinatario u WHERE u.id= :usuario AND n.fecha>= :fecha AND n.leida= FALSE ORDER By n.fecha DESC');
                    $consulta->setParameters(['usuario' => $this->getUser()->getId(), 'fecha' => $this->getUser()->getUltimologout()]);
                    $consulta->setMaxResults(5);
                    $notificacions = $consulta->getResult();
                }

                return $this->json([
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
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'esDirectivo'=>$this->getUser()->esDirectivo(),
            'notificacions' => $notificacions]);
    }

    /**
     * @Route("/{id}/show", name="notificacion_show", methods="GET")
     */
    public function show(Request $request, Notificacion $notificacion): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $notificacion);

        if (!$notificacion->getLeida()) {
            $em = $this->getDoctrine()->getManager();
            $notificacion->setLeida(true);
            $em->persist($notificacion);
            $em->flush();
        }
        return $this->render('notificacion/_show.html.twig', [
            'notificacion' => $notificacion,
        ]);
    }

    /**
     * @Route("/{id}/delete",name="notificacion_delete")
     */
    public function delete(Request $request, Notificacion $notificacion): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $notificacion->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();
        $this->denyAccessUnlessGranted('DELETE', $notificacion);
        $em = $this->getDoctrine()->getManager();
        $em->remove($notificacion);
        $em->flush();
        return $this->json(array('mensaje' => 'La notificación fue eliminada satisfactoriamente'));
    }

}
