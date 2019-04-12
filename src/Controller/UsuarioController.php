<?php

namespace App\Controller;

use App\Entity\MiembroConsejoDireccion;
use App\Entity\Rol;
use App\Entity\Usuario;
use App\Form\UsuarioType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Plantrabajo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("/usuario")
 */
class UsuarioController extends Controller
{
    /**
     * @Route("/{id}/index", name="usuario_index", methods="GET", options={"expose"=true})
     */
    public function index(Request $request, Usuario $usuario): Response
    {
        if ($this->isGranted('ROLE_ADMIN'))
            $usuarios = $this->getDoctrine()->getManager()->createQuery('SELECT u FROM App:Usuario u WHERE u.id!=:id')->setParameter('id', $this->getUser()->getId())->getResult();
        else
            $usuarios = $this->get('area_service')->subordinados($usuario);

        if ($request->isXmlHttpRequest())
            return $this->render('usuario/_table.html.twig', ['usuarios' => $usuarios]);

        return $this->render('usuario/index.html.twig', [
            'usuarios' => $usuarios,
            'user_id' => $usuario->getId(),
            'user_foto' => null != $usuario->getRutaFoto() ? $usuario->getRutaFoto() : null,
            'user_nombre' => $usuario->getNombre(),
            'user_correo' => $usuario->getCorreo(),
        ]);
    }

    /**
     * @Route("/new", name="usuario_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $usuario = new Usuario();

        if (in_array('ROLE_DIRECTIVO', $this->getUser()->getRoles()))
            $usuario->setJefe($this->getUser());

        $form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($usuario);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El usuario fue registrado satisfactoriamente',
                    'nombre' => $usuario->getNombre(),
                    'area' => $usuario->getArea()->getNombre(),
                    'cargo' => $usuario->getCargo()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $usuario->getId())->getValue(),
                    'id' => $usuario->getId(),
                ));
            } else {
                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                    'usuario' => $usuario,
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
        return $this->render('usuario/_show.html.twig', ['usuario' => $usuario,
            'user_id' => $usuario->getId(),
            'user_foto' => null != $usuario->getRutaFoto() ? $usuario->getRutaFoto() : null,
            'user_nombre' => $usuario->getNombre(),
            'user_correo' => $usuario->getCorreo(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('EDIT', $usuario);
        $form = $this->createForm(UsuarioType::class, $usuario, array('action' => $this->generateUrl('usuario_edit', array('id' => $usuario->getId()))));
        $passwordOriginal = $form->getData()->getPassword();
        $em = $this->getDoctrine()->getManager();
        $tieneFoto = $usuario->getRutaFoto() != null && $usuario->getRutaFoto() != null;

        $esAdmin = in_array('ROLE_ADMIN', $usuario->getRoles());
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                if (null == $usuario->getPassword())
                    $usuario->setPassword($passwordOriginal);
                else
                    $usuario->setPassword($this->get('security.password_encoder')->encodePassword($usuario, $usuario->getPassword()));

                if (null != $usuario->getFile()) {
                    if (true == $tieneFoto)
                        $usuario->actualizarFoto($this->container->getParameter('storage_directory'));
                    else
                        $usuario->Upload($this->container->getParameter('storage_directory'));

                    $usuario->setFile(null);
                }

                $em->persist($usuario);

                if (in_array('ROLE_ADMIN', $usuario->getRoles()) == true && $esAdmin == false)
                    $this->eliminarAtadurasUsuario($usuario);


                $em->flush();
                return new JsonResponse(array('mensaje' => 'El usuario fue actualizado satisfactoriamente',
                    'nombre' => $usuario->getNombre(),
                    'area' => $usuario->getArea()->getNombre(),
                    'cargo' => $usuario->getCargo()->getNombre()));
            } else {
                $page = $this->renderView('usuario/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'Actualizar',
                    'usuario' => $usuario,
                    'form_id' => 'usuario_edit',
                ));
                return new JsonResponse(array('form' => $page, 'error' => true));
            }

        return $this->render('usuario/_new.html.twig', [
            'usuario' => $usuario,
            'title' => 'Editar usuario',
            'action' => 'Actualizar',
            'form_id' => 'usuario_edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="usuario_delete",options={"expose"=true})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $usuario->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE', $usuario);
            $em = $this->getDoctrine()->getManager();
            $em->remove($usuario);
            $em->flush();

            return new JsonResponse(array('mensaje' => 'El usuario fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    //Funcionalidades ajax

    /**
     * @Route("/{id}/organigrama", name="usuario_organigrama", methods="GET")
     */
    public function organigrama(Request $request, Usuario $usuario): Response
    {
        return new JsonResponse([
            'view' => $this->renderView('usuario/_organigrama.html.twig'),
            'data' => $this->obtenerOrganigrama($usuario),
        ]);
    }

    private function obtenerOrganigrama(Usuario $usuario)
    {
        $result = ['id' => $usuario->getId(), 'name' => $usuario->getNombre(), 'title' => $usuario->getCargo()->getNombre()];
        $em = $this->getDoctrine()->getManager();
        $subordinadosDirectos = $em->getRepository('App:Usuario')->findByJefe($usuario);
        if (count($subordinadosDirectos) > 0) {
            $result['children'] = [];
            foreach ($subordinadosDirectos as $value) {
                $result['children'][] = $this->obtenerOrganigrama($value);
            }
        }
        return $result;
    }

    /**
     * @Route("/ajax", name="usuario_ajax", options={"expose"=true})
     */
    public function ajax(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $em = $this->getDoctrine()->getManager();
            $parameter = $request->get('q');
            $query = $em->createQuery('SELECT u.id, u.nombre as text FROM App:Usuario u WHERE u.nombre LIKE :nombre ORDER BY u.nombre ASC')
                ->setParameter('nombre', '%' . $parameter . '%');
            $result = $query->getResult();
            return new Response(json_encode($result));
        }
        return new Response(json_encode($result));
    }

    /**
     * @Route("/grupomiembro", name="usuario_grupomiembro", options={"expose"=true})
     * Retorna todos los usuario que el usuario puede asignar como miembros de su grupo
     */
    public function grupoMiembro(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $em = $this->getDoctrine()->getManager();
            $parameter = $request->get('q');
            $query = $em->createQuery('SELECT u.id, u.nombre as text FROM App:Usuario u JOIN u.idrol r WHERE u.id!= :id AND u.nombre LIKE :nombre AND r.nombre IN (:roles) ORDER BY u.nombre ASC')
                ->setParameters(['nombre' => '%' . $parameter . '%', 'id' => $this->getUser()->getId(), 'roles' => ['ROLE_DIRECTIVO', 'ROLE_USER', 'ROLE_COORDINADOR']]);
            $result = $query->getResult();
            return new Response(json_encode($result));
        }
        return new Response(json_encode($result));
    }

    /**
     * @Route("/mensajedestinatario", name="usuario_mensajedestinatario", options={"expose"=true})
     * Define los posibles destinatarios de un mensaje ya sea usuario o grupo
     */
    public function mensajeDestinatario(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $em = $this->getDoctrine()->getManager();
            $parameter = $request->get('q');
            $query = $em->createQuery('SELECT u.id, u.nombre as text FROM App:Usuario u WHERE u.nombre LIKE :nombre ORDER BY u.nombre ASC')
                ->setParameter('nombre', '%' . $parameter . '%');
            $usuarios = $query->getResult();

            foreach ($usuarios as $usuario)
                $result[] = ['id' => $usuario['id'], 'text' => $usuario['text']];

            $query = $em->createQuery('SELECT g FROM App:Grupo g JOIN g.creador c WHERE g.nombre LIKE :nombre AND c.id= :creador ORDER BY g.nombre ASC')
                ->setParameters(['nombre' => '%' . $parameter . '%', 'creador' => $this->getUser()->getId()]);
            $grupos = $query->getResult();

            $query = $em->createQuery('SELECT g FROM App:Grupo g JOIN g.idmiembro m WHERE g.nombre LIKE :nombre AND m.id= :miembro ORDER BY g.nombre ASC')
                ->setParameters(['nombre' => '%' . $parameter . '%', 'miembro' => $this->getUser()->getId()]);
            $grupoMiembro = $query->getResult();

            foreach ($grupoMiembro as $value)
                $grupos[] = $value;

            foreach ($grupos as $grupo) {
                $result[] = ['id' => 'grupo-' . $grupo->getId(), 'text' => $grupo->getNombre()];
            }

        }
        return new Response(json_encode($result));
    }

    /**
     * @Route("/subordinado", name="usuario_subordinado", options={"expose"=true})
     * Retorna todos los subordinados del usuario actual, esto es utilizado para la asignacion de tareas a un grupo de
     * subordinados
     */
    public function subordinado(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $result = [];
        if ($request->get('q') != null) {
            $parameter = $request->get('q');
            $usuarios = $this->get('area_service')->subordinados($this->getUser());
            foreach ($usuarios as $usuario)
                if (false != strstr($usuario->getNombre(), $parameter))
                    $result[] = ['id' => $usuario->getId(), 'text' => $usuario->getNombre()];
        }
        return new Response(json_encode($result));
    }

    private function eliminarAtadurasUsuario(Usuario $usuario)
    {
        $em = $this->getDoctrine()->getManager();
        $gruposPertenece = $usuario->getGrupospertenece();
        $gruposPropietario = $usuario->getGrupos();
        foreach ($gruposPertenece as $grupo) {
            $grupo->removeIdmiembro($usuario);
            $em->persist($grupo);
        }
        foreach ($gruposPropietario as $grupo) {
            $grupo->removeIdmiembro($usuario);
            $em->persist($grupo);
        }
        $miembro = $em->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($usuario);
        if (null != $miembro)
            $em->remove($miembro);

        $em->flush();
    }
}
