<?php

namespace App\Controller;

use App\Entity\ARC;
use App\Entity\Capitulo;
use App\Entity\MiembroConsejoDireccion;
use App\Form\SubcapituloType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subcapitulo;

/**
 * @Route("/subcapitulo")
 */
class SubcapituloController extends AbstractController
{

    /**
     * @Route("/", name="subcapitulo_index", methods="GET",options={"expose"=true})
     */
    public function index(Request $request): Response
    {
        $subcapitulos = $this->getDoctrine()->getRepository(Subcapitulo::class)->findAll();

        if ($request->isXmlHttpRequest())
                return $this->render('subcapitulo/_table.html.twig', [
                    'subcapitulos' => $subcapitulos,
                ]);

        return $this->render('subcapitulo/index.html.twig', ['subcapitulos' => $subcapitulos]);
    }


    /**
     * @Route("/new", name="subcapitulo_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $subcapitulo = new Subcapitulo();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(SubcapituloType::class, $subcapitulo, array('action' => $this->generateUrl('subcapitulo_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($subcapitulo);
                $em->flush();
                return $this->json(array('mensaje' => 'El subcapítulo fue registrado satisfactoriamente',
                    'nombre' => $subcapitulo->getNombre(),
                    'numero' => $subcapitulo->getNumero(),
                    'capitulo' => $subcapitulo->getCapitulo()->getNombre(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $subcapitulo->getId())->getValue(),
                    'id' => $subcapitulo->getId(),
                ));
            } else {
                $page = $this->renderView('subcapitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'subcapitulo' => $subcapitulo,
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }


        return $this->render('subcapitulo/_new.html.twig', [
            'subcapitulo' => $subcapitulo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="subcapitulo_edit",options={"expose"=true}, methods="GET|POST")
     */
    public function edit(Request $request, Subcapitulo $subcapitulo): Response
    {
        if(!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $form = $this->createForm(SubcapituloType::class, $subcapitulo,
            array('action' => $this->generateUrl('subcapitulo_edit', array('id' => $subcapitulo->getId()))));
        $form->handleRequest($request);

        $eliminable = $this->esEliminable($subcapitulo);
        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($subcapitulo);
                $em->flush();
                return $this->json(array('mensaje' => 'El subcapítulo fue actualizado satisfactoriamente',
                    'nombre' => $subcapitulo->getNombre(),
                    'numero' => $subcapitulo->getNumero(),
                    'capitulo' => $subcapitulo->getCapitulo()->getNombre() ));
            } else {
                $page = $this->renderView('subcapitulo/_form.html.twig', array(
                    'form' => $form->createView(),
                    'subcapitulo' => $subcapitulo,
                    'form_id' => 'subcapitulo_edit',
                    'action' => 'Actualizar',
                    'eliminable' => $eliminable,
                ));
                return $this->json(array('form' => $page, 'error' => true));
            }

        return $this->render('subcapitulo/_new.html.twig', [
            'subcapitulo' => $subcapitulo,
            'title' => 'Editar subcapítulo',
            'action' => 'Actualizar',
            'form_id' => 'subcapitulo_edit',
            'eliminable' => $eliminable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="subcapitulo_delete")
     */
    public function delete(Request $request, Subcapitulo $subcapitulo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->esEliminable($subcapitulo) || !$this->isCsrfTokenValid('delete' . $subcapitulo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

            $em = $this->getDoctrine()->getManager();
            $em->remove($subcapitulo);
            $em->flush();
            return $this->json(array('mensaje' => 'El subcapítulo fue eliminado satisfactoriamente'));
    }


    //Funciones ajax
    /**
     * @Route("/{capitulo}/findbycapitulo", name="subcapitulo_findbycapitulo", options={"expose"=true},methods="GET")
     * Se utiliza en el gestionar de ARC
     */
    public function findbycapitulo(Request $request, Capitulo $capitulo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        //A este metodo solo pueden entrar:
        // los administradores para la gestion de las ARC
        // los coordinadores y miembros del consejo de direccion para la gestion de las Actividades Generales
        if(!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COORDINADORINSTITUCIONAL') && null==$this->getDoctrine()->getManager()->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($this->getUser()))
            throw $this->createAccessDeniedException();

        $consulta=$this->getDoctrine()->getManager()->createQuery('SELECT s.id, s.nombre FROM App:Subcapitulo s JOIN s.capitulo c WHERE c.id= :id');
        $consulta->setParameter('id',$capitulo->getId());
        $subcapitulos=$consulta->getResult();
        return $this->json($subcapitulos);
    }

    /*
     * Funcion que devuelve un boleano indicanso si el subtitulo es o no eliminable teniendo en cuenta las ARC que
     * dependen de el
     */
    private function esEliminable(Subcapitulo $subcapitulo): bool
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository(ARC::class)->findOneBySubcapitulo($subcapitulo) == null;
    }
}
