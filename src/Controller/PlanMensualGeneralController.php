<?php

namespace App\Controller;

use App\Entity\ActividadGeneral;
use App\Entity\PlanMensualGeneral;
use App\Form\PlanMensualGeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/planmensualgeneral")
 */
class PlanMensualGeneralController extends Controller
{

    /**
     * @Route("/", name="planmensualgeneral_index", methods="GET")
     */
    public function index(Request $request): Response
    {
        $planmensualgenerals = $this->getDoctrine()->getRepository(PlanMensualGeneral::class)->findAll();

        if ($request->isXmlHttpRequest())
            return $this->render('planmensualgeneral/_table.html.twig', [
                'planmensualgenerals' => $planmensualgenerals,
            ]);

        $parameters = [
            'user_id' => $this->getUser()->getId(),
            'user_foto'=>null!=$this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
            'planmensualgenerals' => $planmensualgenerals];

        return $this->render('planmensualgeneral/index.html.twig', $parameters);
    }

    /**
     * @Route("/new", name="planmensualgeneral_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $planmensualgeneral = new PlanMensualGeneral();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PlanMensualGeneralType::class, $planmensualgeneral, array('action' => $this->generateUrl('planmensualgeneral_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($planmensualgeneral);
                $em->flush();
                return new JsonResponse(array('mensaje' => 'El plan mensual fue registrado satisfactoriamente',
                    'mes' => $planmensualgeneral->getMestoString(),
                    'anno' => $planmensualgeneral->getAnno(),
                    'id' => $planmensualgeneral->getId(),
                ));
            } else {
                $page = $this->renderView('planmensualgeneral/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
            }


        return $this->render('planmensualgeneral/_new.html.twig', [
            'planmensualgeneral' => $planmensualgeneral,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="planmensualgeneral_show", options={"expose"=true},methods="GET")
     */
    public function show(Request $request, PlanMensualGeneral $planmensualgeneral): Response
    {
        $actividads = $this->getDoctrine()
            ->getRepository(ActividadGeneral::class)
            ->findBy(array('planmensualgeneral' => $planmensualgeneral));

        return $this->render('planmensualgeneral/show.html.twig', ['planmensualgeneral' => $planmensualgeneral,
            'actividads' => $actividads,
            'user_id' => $this->getUser()->getId(),
            'user_foto'=>null!=$this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre'=>$this->getUser()->getNombre(),
            'user_correo'=>$this->getUser()->getCorreo(),
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="planmensualgeneral_delete")
     */
    public function delete(Request $request, PlanMensualGeneral $planmensualgeneral): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $em->remove($planmensualgeneral);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El plan mensual fue eliminado satisfactoriamente'));
    }
}
