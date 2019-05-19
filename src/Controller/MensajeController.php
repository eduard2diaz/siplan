<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Entity\Mensaje;
use App\Form\MensajeType;
use App\Form\MensajeUsuarioType;
use App\Services\EmailService;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mensaje")
 */
class MensajeController extends AbstractController
{
    /**
     * @Route("/", name="mensaje_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        $mensaje_inbox = 'Bandeja de entrada';
        $mensajes = $this->getDoctrine()
            ->getRepository(Mensaje::class)
            ->findBy(array('bandeja' => 0, 'propietario' => $this->getUser()), array('fecha' => 'DESC'));

        if ($request->isXmlHttpRequest())
            return $this->json(array(
                'messages' => $this->renderView('mensaje/_table.html.twig', [
                    'mensajes' => $mensajes
                ]),
                'message_inbox' => $mensaje_inbox
            ));


        return $this->render('mensaje/index.html.twig', ['mensajes' => $mensajes,
            'user_id' => $this->getUser()->getId(),
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'esDirectivo'=>$this->getUser()->esDirectivo(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'message_inbox' => $mensaje_inbox]);
    }

    /**
     * @Route("/sended", name="mensaje_sended", methods="GET")
     */
    public function sended(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $mensaje_inbox = 'Bandeja de salida';
        $mensajes = $this->getDoctrine()
            ->getRepository(Mensaje::class)
            ->findBy(array('bandeja' => 1, 'remitente' => $this->getUser()), array('fecha' => 'DESC'));

        return $this->json(array(
            'messages' => $this->renderView('mensaje/_table.html.twig', [
                'mensajes' => $mensajes
            ]),
            'message_inbox' => $mensaje_inbox
        ));

    }

    /**
     * @Route("/recent",options={"expose"=true}, name="mensaje_recent",  methods="GET")
     */
    public function recent(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        if ($this->getUser()->getUltimologout() != null)
            $mensajes = $this->getDoctrine()->getManager()
                ->createQuery('SELECT m FROM App:Mensaje m JOIN m.propietario p WHERE m.fecha > :fecha AND p.id= :id AND m.bandeja = 0 AND m.leida= FALSE ORDER By m.fecha DESC')
                ->setParameters(array('id' => $this->getUser()->getId(), 'fecha' => $this->getUser()->getUltimologout()))
                ->setMaxResults(5)
                ->getResult();
        else
            $mensajes = $this->getDoctrine()->getManager()
                ->createQuery('SELECT m FROM App:Mensaje m JOIN m.propietario p WHERE p.id= :id AND m.bandeja = 0 AND m.leida= FALSE ORDER By m.fecha DESC')
                ->setParameters(array('id' => $this->getUser()->getId()))
                ->setMaxResults(5)
                ->getResult();


        $count = count($mensajes);
        if ($count > 50)
            $count = '+50';

        return $this->json(array(
            'html' => $this->renderView('mensaje/_notify.html.twig', [
                'mensajes' => $mensajes
            ]),
            'contador' => $count,
        ));

    }

    /**
     * @Route("/new", name="mensaje_new", methods="GET|POST")
     */
    public function new(Request $request, EmailService $emailService): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $parameters = $request->request->all();
        $listado = [];
        if (isset($parameters["mensaje"])) {
            foreach ($parameters["mensaje"]['iddestinatario'] as $value) {
                $it = explode('grupo-', $value);
                if (count($it) == 1) {
                    if (false===array_search($it[0],$listado))
                        $listado[] = $it[0];
                } else {
                    $grupo = $em->getRepository(Grupo::class)->find($it[1]);
                    if (null != $grupo) {
                        $creador=strval($grupo->getCreador()->getId());
                        if (false===array_search($creador,$listado))
                                $listado[] = $creador;
                        foreach ($grupo->getIdMiembro() as $val){
                            $miembro = strval($val->getId());
                            if (false===array_search($miembro,$listado))
                                $listado[] = $miembro;
                        }
                    }
                }
            }
            $parameters["mensaje"]['iddestinatario'] = $listado;
            $request->request->replace($parameters);
        }
        $mensaje = new Mensaje();
        $form = $this->createForm(MensajeType::class, $mensaje, array('action' => $this->generateUrl('mensaje_new')));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $mensaje->setRemitente($this->getUser());
                $mensaje->setPropietario($this->getUser());

                $em->persist($mensaje);
                foreach ($mensaje->getIddestinatario() as $value) {
                    $clone = clone $mensaje;
                    $clone->setPropietario($value);
                    $clone->setBandeja(0);
                    $em->persist($clone);
                    $emailService->sendEmail($this->getUser()->getCorreo(), $value->getCorreo(), $clone->getAsunto(), $clone->getDescripcion());
                }
                $em->flush();
                return $this->json(['mensaje' => 'El mensaje fue registrado satisfactoriamente',
                    'descripcion' => $mensaje->getDescripcion(),
                    'fecha' => $mensaje->getFecha()->format('d-m-Y H:i'),
                    'id' => $mensaje->getId()
                ]);
            } else {
                $page = $this->renderView('mensaje/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                //   dump($form->getErrors());
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('mensaje/_new.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="mensaje_new_usuario", methods="GET|POST")
     */
    public function newUsuario(Request $request,Usuario $usuario, EmailService $email): Response
    {
        if (!$request->isXmlHttpRequest() || $usuario->getId()==$this->getUser()->getId())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $mensaje = new Mensaje();
        $mensaje->setRemitente($this->getUser());
        $mensaje->setPropietario($this->getUser());
        $mensaje->addIddestinatario($usuario);
        $form = $this->createForm(MensajeUsuarioType::class, $mensaje, array('action' => $this->generateUrl('mensaje_new_usuario',['id'=>$usuario->getId()])));
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($mensaje);
                foreach ($mensaje->getIddestinatario() as $value) {
                    $clone = clone $mensaje;
                    $clone->setPropietario($value);
                    $clone->setBandeja(0);
                    $em->persist($clone);
                    $email->sendEmail($this->getUser()->getCorreo(), $value->getCorreo(), $clone->getAsunto(), $clone->getDescripcion());
                }
                $em->flush();
                return $this->json(['mensaje' => 'El mensaje fue registrado satisfactoriamente',
                    'descripcion' => $mensaje->getDescripcion(),
                    'fecha' => $mensaje->getFecha()->format('d-m-Y H:i'),
                    'id' => $mensaje->getId()
                ]);
            } else {
                $page = $this->renderView('mensaje/_formautor.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('mensaje/_newautor.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="mensaje_show", methods="GET")
     */
    public function show(Request $request, Mensaje $mensaje): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $mensaje);
        if (!$mensaje->getLeida()) {
            $em = $this->getDoctrine()->getManager();
            $mensaje->setLeida(true);
            $em->persist($mensaje);
            $em->flush();
        }
        return $this->render('mensaje/_show.html.twig', ['mensaje' => $mensaje]);
    }


    /**
     * @Route("/{id}/delete", name="mensaje_delete")
     */
    public function delete(Request $request, Mensaje $mensaje): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $mensaje);
        $em = $this->getDoctrine()->getManager();
        $em->remove($mensaje);
        $em->flush();
        return $this->json(array('mensaje' => 'El mensaje fue eliminado satisfactoriamente'));
    }
}
