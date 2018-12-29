<?php

namespace App\Controller;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Form\UsuarioType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Plantrabajo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

/**
 * @Route({
 *     "en": "/user",
 *     "es": "/usuario",
 *     "fr": "/usse",
 * })
 */
class UsuarioController extends Controller
{
    /**
     * @Route("/", name="usuario_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        if ($this->isGranted('ROLE_ADMIN'))
            $usuarios = $this->getDoctrine()->getManager()->createQuery('SELECT u FROM App:Usuario u WHERE u.id!=:id')->setParameter('id', $this->getUser()->getId())->getResult();
        else
            $usuarios = $this->get('area_service')->subordinados($this->getUser());

        if ($request->isXmlHttpRequest())
            return $this->render('usuario/_table.html.twig', ['usuarios' => $usuarios]);

        return $this->render('usuario/index.html.twig', ['usuarios' => $usuarios]);
    }

    /**
     * @Route("/new", name="usuario_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $usuario = new Usuario();
        $esAdmin=$this->isGranted('ROLE_ADMIN');
        $areas=$this->get('area_service')->areasHijas($this->getUser()->getArea(),$esAdmin);
        $parameters= ['esAdmin' => $esAdmin, 'disab' => false,'area'=>$areas];

        if($esAdmin)
            $parameters['directivos']=$this->get('area_service')->obtenerDirectivos();

        $form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_new'),'parameters'=>$parameters));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if (in_array('ROLE_DIRECTIVO',$this->getUser()->getRoles()))
                    $usuario->setJefe($this->getUser());
                $em->persist($usuario);
                $em->flush();
                return new JsonResponse(array('mensaje' => $this->get('translator')->trans('worker_register_successfully'),
                    'nombre' => $usuario->getNombre(),
                    'usuario' => $usuario->getUsuario(),
                    'area' => $usuario->getArea()->getNombre(),
                    'cargo' => $usuario->getCargo()->getNombre(),
                    'id' => $usuario->getId(),
                ));
            } else {
				$form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_new'),'parameters'=>$parameters));

                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('usuario/_new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="usuario_show", methods="GET",options={"expose"=true})
     */
    public function show(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        return $this->render('usuario/show_ajax.html.twig', ['usuario' => $usuario]);
    }


    /**
     * @Route("/{id}/edit", name="usuario_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT',$usuario);
        $esAdmin=$this->isGranted('ROLE_ADMIN');
        $areas=$this->get('area_service')->areasHijas($this->getUser()->getArea(),$esAdmin);
        $disabled = $this->getUser()->getId() == $usuario->getId();
        $parameters= ['esAdmin' => $esAdmin, 'disab' => false,'area'=>$areas];

        if($this->isGranted('ROLE_ADMIN'))
            $parameters['directivos']=$this->get('area_service')->obtenerDirectivos($usuario->getId());

        $form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_edit', array('id' => $usuario->getId())),'parameters'=>$parameters));
        $passwordOriginal = $form->getData()->getPassword();
		$em = $this->getDoctrine()->getManager();        
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                if (null == $usuario->getPassword())
                    $usuario->setPassword($passwordOriginal);
                else
                    $usuario->setPassword($this->get('security.password_encoder')->encodePassword($usuario,$usuario->getPassword()));
                $em->persist($usuario);
                $em->flush();
                return new JsonResponse(array('mensaje' => $this->get('translator')->trans('worker_update_successfully'),
                    'nombre' => $usuario->getNombre(),
                    'usuario' => $usuario->getUsuario(),
                    'area' => $usuario->getArea()->getNombre(),
                    'cargo' => $usuario->getCargo()->getNombre()));
            } else {
				$form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_edit', array('id' => $usuario->getId())), 'parameters'=>$parameters));

                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'update_button',
                     'form_id' => 'usuario_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('usuario/_new.html.twig', [
            'usuario' => $usuario,
            'title' => 'edit_workerheader',
            'action' => 'update_button',
            'form_id' => 'usuario_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_delete",options={"expose"=true})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();
        $this->denyAccessUnlessGranted('DELETE',$usuario);
        $em = $this->getDoctrine()->getManager();
        $em->remove($usuario);
        $em->flush();

        return new JsonResponse(array('mensaje' => $this->get('translator')->trans('worker_delete_successfully')));
    }





}
