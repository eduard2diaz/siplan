<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Entity\Mensaje;
use App\Form\MensajeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @Route("/mensaje")
 */
class MensajeController extends Controller
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
            return new JsonResponse(array(
                'messages' => $this->renderView('mensaje/_table.html.twig', [
                    'mensajes' => $mensajes
                ]),
                'message_inbox' => $mensaje_inbox
            ));


        return $this->render('mensaje/index.html.twig', ['mensajes' => $mensajes,
            'user_id' => $this->getUser()->getId(),
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
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

        return new JsonResponse(array(
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

        $mensajes = $this->getDoctrine()->getManager()
            ->createQuery('SELECT m FROM App:Mensaje m JOIN m.propietario p WHERE m.fecha > :fecha AND p.id= :id AND m.bandeja = 0 ORDER By m.fecha DESC')
            ->setParameters(array('id' => $this->getUser()->getId(), 'fecha' => $this->getUser()->getUltimologout()))
            ->setMaxResults(5)
            ->getResult();

        $count = count($mensajes);
        if ($count > 50)
            $count = '+50';

        return new JsonResponse(array(
            'html' => $this->renderView('mensaje/_notify.html.twig', [
                'mensajes' => $mensajes
            ]),
            'contador' => $count,
        ));

    }

    /**
     * @Route("/new", name="mensaje_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();
        $em = $this->getDoctrine()->getManager();
        $parameters = $request->request->all();
        $listado = [];
        if (isset($parameters["mensaje"])) {
            foreach ($parameters["mensaje"]['iddestinatario'] as $value) {
                $it = explode('grupo-', $value);
                if (count($it) == 1)
                    $listado[] = $it[0];
                else {
                    $grupo = $em->getRepository(Grupo::class)->find($it[1]);
                    if (null != $grupo) {
                        $listado[] = strval($grupo->getCreador()->getId());
                        foreach ($grupo->getIdMiembro() as $val)
                            $listado[] = strval($val->getId());
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
                    $this->get('app.email_service')->sendEmail($this->getUser()->getCorreo(), $value->getCorreo(), $clone->getAsunto(), $clone->getDescripcion());
                }
                $em->flush();
                return new JsonResponse(['mensaje' => 'El mensaje fue registrado satisfactoriamente',
                    'descripcion' => $mensaje->getDescripcion(),
                    'fecha' => $mensaje->getFecha()->format('d-m-Y H:i'),
                    'id' => $mensaje->getId()
                ]);
            } else {
                $page = $this->renderView('mensaje/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                //   dump($form->getErrors());
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }

        return $this->render('mensaje/_new.html.twig', [
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
        return new JsonResponse(array('mensaje' => 'El mensaje fue elminado satisfactoriamente'));
    }
}
