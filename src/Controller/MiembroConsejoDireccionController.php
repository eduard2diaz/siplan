<?php

namespace App\Controller;

use App\Entity\MiembroConsejoDireccion;
use App\Form\MiembroConsejoDireccionType;
use App\Repository\MiembroConsejoDireccionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/miembroconsejodireccion")
 */
class MiembroConsejoDireccionController extends AbstractController
{
    /**
     * @Route("/", name="miembro_consejo_direccion_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $em=$this->getDoctrine()->getManager();
        $miembros = $em->getRepository(MiembroConsejoDireccion::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('miembro_consejo_direccion/_table.html.twig', [
                'miembro_consejo_direccions' => $miembros,
            ]);

        return $this->render('miembro_consejo_direccion/index.html.twig', ['miembro_consejo_direccions' => $miembros,
            'user_id' => $this->getUser()->getId(),
            'user_foto'=>null!=$this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
            ]);
    }

    /**
     * @Route("/new", name="miembro_consejo_direccion_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $miembroConsejoDireccion = new MiembroConsejoDireccion();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(MiembroConsejoDireccionType::class, $miembroConsejoDireccion, array('action' => $this->generateUrl('miembro_consejo_direccion_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($miembroConsejoDireccion);
                $em->flush();
                return new JsonResponse(array('mensaje' =>'El miembro fue registrado satisfactoriamente',
                    'nombre' => $miembroConsejoDireccion->getUsuario()->getNombre(),
                    'csrf'=>$this->get('security.csrf.token_manager')->getToken('delete'.$miembroConsejoDireccion->getId())->getValue(),
                    'id' => $miembroConsejoDireccion->getId(),
                ));
            } else {
                $page = $this->renderView('miembro_consejo_direccion/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('miembro_consejo_direccion/_new.html.twig', [
            'miembroConsejoDireccion' => $miembroConsejoDireccion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="miembro_consejo_direccion_edit", methods={"GET","POST"},options={"expose"=true})
     */
    public function edit(Request $request, MiembroConsejoDireccion $miembroConsejoDireccion): Response
    {
        $form = $this->createForm(MiembroConsejoDireccionType::class, $miembroConsejoDireccion,
            array('action' => $this->generateUrl('miembro_consejo_direccion_edit', array('id' => $miembroConsejoDireccion->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($miembroConsejoDireccion);
                $em->flush();
                return new JsonResponse(array('mensaje' =>'El miembro fue actualizado satisfactoriamente',
                    'nombre' => $miembroConsejoDireccion->getUsuario()->getNombre(),
                ));
            } else {
                $page = $this->renderView('miembro_consejo_direccion/_form.html.twig', array(
                    'form' => $form->createView(),
                    'form_id' => 'miembroconsejo_edit',
                    'action' => 'Actualizar',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('miembro_consejo_direccion/_new.html.twig', [
            'miembroConsejoDireccion' => $miembroConsejoDireccion,
            'title' => 'Editar miembro',
            'action' => 'Actualizar',
            'form_id' => 'miembroconsejo_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="miembro_consejo_direccion_delete", options={"expose"=true})
     */
    public function delete(Request $request, MiembroConsejoDireccion $miembroConsejoDireccion): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete'.$miembroConsejoDireccion->getId(), $request->query->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($miembroConsejoDireccion);
            $em->flush();
            return new JsonResponse(array('mensaje' =>'El miembro fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }
}
