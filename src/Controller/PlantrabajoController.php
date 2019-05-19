<?php

namespace App\Controller;

use App\Entity\ARC;
use App\Entity\Capitulo;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\Plantrabajo;
use App\Entity\PuntualizacionPlanTrabajo;
use App\Entity\Subcapitulo;
use App\Form\PlantrabajoType;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use App\Entity\Actividad;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/plantrabajo")
 */
class PlantrabajoController extends AbstractController
{

    /**
     * @Route("/{id}/index", name="plantrabajo_index", methods="GET")
     */
    public function index(Request $request, Usuario $usuario): Response
    {
        $esSubordinado = $usuario->esSubordinado($this->getUser());
        if ($usuario->getId() != $this->getUser()->getId() && ($esSubordinado == false) && !$this->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException();

        $plantrabajos = $this->getDoctrine()
            ->getRepository(Plantrabajo::class)
            ->findBy(array('usuario' => $usuario), array('anno' => 'DESC', 'mes' => 'DESC'));

        $parameters = [
            'user_id' => $usuario->getId(),
            'user_foto' => null != $usuario->getRutaFoto() ? $usuario->getRutaFoto() : null,
            'user_nombre' => $usuario->getNombre(),
            'user_correo' => $usuario->getCorreo(),
            'esSubordinado' => $esSubordinado,
            'plantrabajos' => $plantrabajos,
            'esDirectivo'=>$usuario->esDirectivo()
        ];

        if ($request->isXmlHttpRequest())
            return $this->render('plantrabajo/_table.html.twig',
                $parameters
            );

        return $this->render('plantrabajo/index.html.twig', $parameters);
    }

    /**
     * @Route("/{id}/new", name="plantrabajo_new", methods="GET|POST")
     */
    public function new(Request $request, Usuario $usuario): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $plantrabajo = new Plantrabajo();
        $plantrabajo->setUsuario($usuario);
        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('NEW', $plantrabajo);

        $form = $this->createForm(PlantrabajoType::class, $plantrabajo, array('action' => $this->generateUrl('plantrabajo_new', array('id' => $usuario->getId()))));
        $form->handleRequest($request);

        if ($form->isSubmitted())
            if ($form->isValid()) {
                $em->persist($plantrabajo);
                $em->flush();
                return $this->json(array('mensaje' => 'El plan de trabajo fue registrado satisfactoriamente',
                    'mes' => $plantrabajo->getMestoString(),
                    'anno' => $plantrabajo->getAnno(),
                    'csrf' => $this->get('security.csrf.token_manager')->getToken('delete' . $plantrabajo->getId())->getValue(),
                    'id' => $plantrabajo->getId(),
                ));
            } else {
                $page = $this->renderView('plantrabajo/_form.html.twig', array(
                    'id' => $usuario->getId(),
                    'form' => $form->createView(),
                ));
                return $this->json(array('form' => $page, 'error' => true,));
            }

        return $this->render('plantrabajo/_new.html.twig', [
            'plantrabajo' => $plantrabajo,
            'id' => $usuario->getId(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="plantrabajo_show", options={"expose"=true},methods="GET")
     */
    public function show(Request $request, Plantrabajo $plantrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);

        //La variable $esmiembroCD permite definir si un usuario puede o no clonar sus actividades del plan general
        $em = $this->getDoctrine()->getManager();
        $actividads = $em->getRepository(Actividad::class)->findBy(array('plantrabajo' => $plantrabajo));
        $puntualizaciones = $em->getRepository(PuntualizacionPlanTrabajo::class)->findBy(array('plantrabajo' => $plantrabajo));

        return $this->render('plantrabajo/show.html.twig', [
            'plantrabajo' => $plantrabajo,
            'actividads' => $actividads,
            'puntualizaciones' => $puntualizaciones,
            'user_id' => $plantrabajo->getUsuario()->getId(),
            'user_foto' => null != $plantrabajo->getUsuario()->getRutaFoto() ? $plantrabajo->getUsuario()->getRutaFoto() : null,
            'user_nombre' => $plantrabajo->getUsuario()->getNombre(),
            'user_correo' => $plantrabajo->getUsuario()->getCorreo(),
            'esDirectivo'=>$plantrabajo->getUsuario()->esDirectivo()
        ]);
    }

    /**
     * @Route("/{id}/filtraractividad", name="plantrabajo_filtraractividad", options={"expose"=true},methods="GET")
     * Permite filtrar las actividades por su estado
     */
    public function filtrarActividad(Request $request, Plantrabajo $plantrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);

        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $parameters = ['plantrabajo' => $plantrabajo];
        $status = ['registradas', 'en proceso', 'culminadas', 'cumplidas', 'incumplidas'];
        if ($request->query->has('filtro') && ($request->query->get('filtro') >= 1 && $request->query->get('filtro') <= 5)) {
            $filtro = $request->get('filtro');
            $parameters['estado'] = $filtro;
            $status = $status[$filtro - 1];
        } else {
            $status = 'todas';
        }

        $em = $this->getDoctrine()->getManager();
        $actividads = $em->getRepository(Actividad::class)->findBy($parameters);

        return $this->json([
            'filtro' => $status,
            'table' => $this->renderView('actividad/_table.html.twig', [
                'actividads' => $actividads,
            ])
        ]);
    }


    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="plantrabajo_delete")
     */
    public function delete(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->isCsrfTokenValid('delete' . $plantrabajo->getId(), $request->query->get('_token')))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $plantrabajo);
        $em = $this->getDoctrine()->getManager();
        $em->remove($plantrabajo);
        $em->flush();
        return $this->json(array('mensaje' => 'El plan de trabajo fue eliminado satisfactoriamente'));
    }

    /**
     * @Route("/{id}/estadistica", name="plantrabajo_estadistica", options={"expose"=true}, methods="GET")
     */
    public function estadistica(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);
        $actividads = $this->getDoctrine()
            ->getRepository(Actividad::class)
            ->findBy(array('plantrabajo' => $plantrabajo));
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $estados = ['Registrada' => 0, 'En proceso' => 0, 'Culminada' => 0, 'Cumplida' => 0, 'Incumplida' => 0];
        $total = 0;
        foreach ($actividads as $activity) {
            $estados[$activity->getEstadoString()]++;
            $total++;
        }

        $result = [];
        foreach ($estados as $key => $value) {
            $result[] = [
                'estado' => $key,
                'cantidad' => (Integer)$value,
            ];
        }

        return $this->json(
            [
                'view' => $this->renderView('plantrabajo/ajax/_estadisticas.html.twig', ['plantrabajo' => $plantrabajo, 'estadisticas' => $estados, 'total' => $total]),
                'data' => json_encode($result)
            ]);
    }

    /**
     * @Route("/{id}/antiguos", name="plantrabajo_antiguos", methods="GET")
     * Funcionalidad que devuelve el listado de planes de trabajo anteriores, solo puede usarlo el usaurio actual sobre su plan de trabajo o
     * el de los subordinados
     */
    public function antiguos(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest() || ($plantrabajo->getUsuario()->getJefe() != null && !$plantrabajo->getUsuario()->esSubordinado($this->getUser()) && $plantrabajo->getUsuario()->getId() != $this->getUser()->getId()))
            throw  $this->createAccessDeniedException();

        $consulta = $this->getDoctrine()->getManager()->createQuery('SELECT p FROM App:PlanTrabajo p join p.usuario u WHERE p!= :id AND u.id=:usuario ORDER BY p.anno, p.mes DESC');
        $consulta->setParameters(array('id' => $plantrabajo->getId(), 'usuario' => $plantrabajo->getUsuario()->getId()));
        $plantrabajos = $consulta->getResult();

        return $this->render('plantrabajo/ajax/_tableantiguos.html.twig', [
            'plantrabajos' => $plantrabajos,
        ]);
    }


    /**
     * @Route("/{id}/exportar", name="plantrabajo_exportar", options={"expose"=true},methods="GET")
     */
    public function exportar(Request $request, Plantrabajo $plantrabajo, Pdf $pdf): Response
    {

        $em = $this->getDoctrine()->getManager();
        $consulta=$em->createQuery('Select a.nombre, a.fecha, a.fechaF, a.dirigen, a.participan,a.lugar from App:Actividad a JOIN a.plantrabajo p WHERE p.id= :id Order by a.fecha ASC');
        $consulta->setParameter('id',$plantrabajo->getId());
        $actividades=$consulta->getResult();
        $result=[];
        foreach ($actividades as $actividad){
            $aux=$actividad["fecha"]->format('d-m-Y');
            $pos=$this->buscarActividad($result,$aux);
            if($pos==-1)
                $result[]=['fecha'=>$aux,'actividad'=>[['nombre'=>$actividad['nombre'],'fecha'=>$actividad['fecha'],'fechaF'=>$actividad['fechaF'],'dirigen'=>$actividad['dirigen'],'participan'=>$actividad['participan'],'lugar'=>$actividad['lugar']]]];
            else
                $result[$pos]['actividad'][]=['nombre'=>$actividad['nombre'],'fecha'=>$actividad['fecha'],'fechaF'=>$actividad['fechaF'],'dirigen'=>$actividad['dirigen'],'participan'=>$actividad['participan'],'lugar'=>$actividad['lugar']];
        }


        $html = $this->renderView('plantrabajo/_pdf.html.twig', ['plan' => $plantrabajo,'actividades'=>$result]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    private function buscarActividad($listado, $fecha){
        $i=0;
        foreach ($listado as $value){
            if($value['fecha']==$fecha)
                return $i;
            $i++;
        }
        return -1;
    }
}
