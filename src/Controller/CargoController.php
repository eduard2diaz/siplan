<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\CargoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use  App\Entity\Area;
use App\Entity\Cargo;

/**
 * @Route("/cargo")
 */
class CargoController extends AbstractController
{

    /**
     * @Route("/", name="cargo_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $cargos = $this->getDoctrine()->getRepository(Cargo::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('cargo/_table.html.twig', [
                'cargos' => $cargos,
            ]);

        return $this->render('cargo/index.html.twig', ['cargos' => $cargos]);
    }

    /**
     * @Route("/{area}/ajax", name="cargo_ajax", options={"expose"=true},methods="GET")
     * Funcionalidad que devuelve el listado de cargos usando ajax, utilizado para la gestion de usuarios
     */
    public function ajax(Request $request, Area $area): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT c.id, c.nombre FROM App:Cargo c JOIN c.area a WHERE a.id= :id');
        $consulta->setParameter('id', $area->getId());
        $cargos = $consulta->getResult();

        return $this->json($cargos);
    }


    /**
     * @Route("/new", name="cargo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $cargo = new Cargo();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(CargoType::class, $cargo, array('action' => $this->generateUrl('cargo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($cargo);
                $em->flush();
                return $this->json(array('mensaje' => 'El cargo fue registrado satisfactoriamente',
                    'nombre' => $cargo->getNombre(),
                    'area' => $cargo->getArea()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $cargo->getId())->getValue(),
                    'id' => $cargo->getId(),
                ));
            } else {
                $page = $this->renderView('cargo/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('cargo/_new.html.twig', [
            'cargo' => $cargo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="cargo_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Cargo $cargo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(CargoType::class, $cargo,
            array('action' => $this->generateUrl('cargo_edit', array('id' => $cargo->getId()))));

        $eliminable = $this->esEliminable($cargo);
        $form->handleRequest($request);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cargo);
                $em->flush();
                return $this->json(array('mensaje' => 'El cargo fue actualizado satisfactoriamente', 'nombre' => $cargo->getNombre(), 'area' => $cargo->getArea()->getNombre()));
            } else {
                $page = $this->renderView('cargo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'action' => 'Actualizar',
                    'form_id' => 'cargo_edit',
                    'eliminable' => $eliminable,
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('cargo/_new.html.twig', [
            'cargo' => $cargo,
            'title' => 'Editar cargo',
            'action' => 'Actualizar',
            'form_id' => 'cargo_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="cargo_delete")
     */
    public function delete(Request $request, Cargo $cargo): Response
    {
        if ($request->isXmlHttpRequest() && $this->isCsrfTokenValid('delete' . $cargo->getId(), $request->query->get('_token'))) {
            $this->denyAccessUnlessGranted('DELETE', $cargo);
            $em = $this->getDoctrine()->getManager();
            $em->remove($cargo);
            $em->flush();
            return $this->json(array('mensaje' => 'El cargo fue eliminado satisfactoriamente'));
        }

        throw $this->createAccessDeniedException();
    }

    private function esEliminable(Cargo $cargo): bool
    {
        $em = $this->getDoctrine()->getManager();
        return ($em->getRepository(Usuario::class)->findOneByCargo($cargo) == null);
    }
}
