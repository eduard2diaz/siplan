<?php

namespace App\Controller;

use App\Entity\ActividadArea;
use App\Entity\ARC;
use App\Entity\Area;
use App\Entity\Capitulo;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\PlanMensualArea;
use App\Entity\PuntualizacionPlanMensualArea;
use App\Form\PlanMensualAreaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;

/**
 * @Route("/planmensualarea")
 */
class PlanMensualAreaController extends AbstractController
{
    /**
     * @Route("/{id}/index", name="planmensualarea_index", methods="GET")
     */
    public function index(Request $request, Area $area): Response
    {
        $planmensualareas = $this->getDoctrine()->getRepository(PlanMensualArea::class)->findBy(['area' => $area], ['anno' => 'DESC', 'mes' => 'DESC']);

        if ($request->isXmlHttpRequest())
            return $this->render('planmensualarea/_table.html.twig', [
                'planmensualareas' => $planmensualareas,
            ]);

        return $this->render('planmensualarea/index.html.twig', [
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'planmensualareas' => $planmensualareas,
            'esDirectivo' => $this->getUser()->esDirectivo()
        ]);
    }

    /**
     * @Route("/new", name="planmensualarea_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $planmensualarea = new PlanMensualArea();
        $planmensualarea->setArea($this->getUser()->getArea());
        $planmensualarea->setGestor($this->getUser());
        $form = $this->createForm(PlanMensualAreaType::class, $planmensualarea, array('action' => $this->generateUrl('planmensualarea_new')));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($planmensualarea);
                $em->flush();
                return $this->json(array('mensaje' => 'El plan mensual fue registrado satisfactoriamente',
                    'mes' => $planmensualarea->getMestoString(),
                    'anno' => $planmensualarea->getAnno(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $planmensualarea->getId())->getValue(),
                    'id' => $planmensualarea->getId(),
                ));
            } else {
                $page = $this->renderView('planmensualarea/_form.html.twig', array(
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('planmensualarea/_new.html.twig', [
            'planmensualarea' => $planmensualarea,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="planmensualarea_show", options={"expose"=true},methods="GET")
     */
    public function show(Request $request, PlanMensualArea $planmensualarea): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $planmensualarea);
        $em = $this->getDoctrine()->getManager();
        $actividads = $em->getRepository(ActividadArea::class)->findBy(array('planmensualarea' => $planmensualarea));
        $puntualizaciones = $em->getRepository(PuntualizacionPlanMensualArea::class)->findBy(array('plantrabajo' => $planmensualarea));

        if ($request->isXmlHttpRequest())
            return $this->render('actividadarea/_table.html.twig', [
                'actividads' => $actividads,
            ]);

        return $this->render('planmensualarea/show.html.twig', [
            'planmensualarea' => $planmensualarea,
            'area' => $planmensualarea->getArea(),
            'actividads' => $actividads,
            'puntualizaciones' => $puntualizaciones,
            'user_id' => $this->getUser()->getId(),
            'user_foto' => null != $this->getUser()->getRutaFoto() ? $this->getUser()->getRutaFoto() : null,
            'user_nombre' => $this->getUser()->getNombre(),
            'user_correo' => $this->getUser()->getCorreo(),
            'esDirectivo' => $this->getUser()->esDirectivo()
        ]);
    }

    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="planmensualarea_delete")
     */
    public function delete(Request $request, PlanMensualArea $planmensualarea): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $planmensualarea->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $planmensualarea);
        $em = $this->getDoctrine()->getManager();
        $em->remove($planmensualarea);
        $em->flush();
        return $this->json(array('mensaje' => 'El plan mensual fue eliminado satisfactoriamente'));
    }

    /**
     * @Route("/{id}/exportar", name="planmensualarea_exportar", methods="GET")
     */
    public function exportar(Request $request, PlanMensualArea $planmensualarea, Pdf $pdf): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $planmensualarea);

        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery('Select a FROM App:ActividadArea a JOIN a.planmensualarea p JOIN a.areaconocimiento arc WHERE p.id= :id ORDER BY arc.id ASC, a.fecha ASC');
        $consulta->setParameter('id', $planmensualarea->getId());
        $actividades = $consulta->getResult();
        $result = [];
        $actividads = [];
        $ids = [];
        foreach ($actividades as $value) {
            $ids[] = $value->getId();
            $pos = $this->buscarArc($result, $value->getAreaconocimiento()->getNombre());
            if ($pos == -1)
                $result[] = ['arc' => $value->getAreaconocimiento()->getNombre(), 'actividades' => [
                    ['nombre' => $value->getNombre(), 'lugar' => $value->getLugar(), 'dirigen' => $value->getDirigen(), 'participan' => $value->getParticipan(), 'fecha' => $value->getFecha()->format('d-m-Y H:i'), 'fechaf' => $value->getFechaF()->format('d-m-Y H:i')]
                ]];
            else
                $result[$pos]['actividades'][] = ['nombre' => $value->getNombre(), 'lugar' => $value->getLugar(), 'dirigen' => $value->getDirigen(), 'participan' => $value->getParticipan(), 'fecha' => $value->getFecha()->format('d-m-Y H:i'), 'fechaf' => $value->getFechaF()->format('d-m-Y H:i')];
        }

        if (count($ids) >0){
            $consulta = $em->createQuery('Select a FROM App:ActividadArea a JOIN a.planmensualarea p WHERE p.id= :id AND a.id NOT IN (:lista) ORDER BY a.fecha ASC');
            $consulta->setParameters(['id'=> $planmensualarea->getId(),'lista'=> $ids]);
            $actividades = $consulta->getResult();

            foreach ($actividades as $value) {
                $actividades = [];
                    $actividads[] = ['nombre' => $value->getNombre(), 'lugar' => $value->getLugar(), 'dirigen' => $value->getDirigen(), 'participan' => $value->getParticipan(), 'fecha' => $value->getFecha()->format('d-m-Y H:i'), 'fechaf' => $value->getFechaF()->format('d-m-Y H:i')];
            }
        }


        $html = $this->renderView('planmensualarea/_pdf.html.twig', [
            'actividades' => $actividads,
            'results' => $result,
            'area' => $planmensualarea->getArea()->getNombre(),
            'mes' => $planmensualarea->getMesToString(),
            'anno' => $planmensualarea->getAnno()

        ]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    private function buscarArc($listado, $arc)
    {
        $i = 0;
        foreach ($listado as $value) {
            if ($value['arc'] == $arc)
                return $i;
            $i++;
        }
        return -1;
    }
}
