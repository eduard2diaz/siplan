<?php

namespace App\Controller;

use App\Entity\Plantrabajo;
use App\Form\PlantrabajoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use App\Entity\Actividad;

/*use Docxpresso\CreateDocument as Document;
require_once '../../docxpresso/CreateDocument.inc';
use Docxpresso\HTML2TEXT as Parser;*/

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/plantrabajo")
 */
class PlantrabajoController extends Controller
{

    /**
     * @Route("/{id}/index", name="plantrabajo_index", methods="GET")
     */
    public function index(Request $request, Usuario $usuario): Response
    {
        $plantrabajos = $this->getDoctrine()
            ->getRepository(Plantrabajo::class)
            ->findBy(array('usuario' => $usuario), array('anno' => 'DESC', 'mes' => 'DESC'));

        if ($request->isXmlHttpRequest())
            return $this->render('plantrabajo/_table.html.twig', [
                'plantrabajos' => $plantrabajos,
            ]);

        $parameters = [
            'user_id' => $usuario->getId(),
            'user_foto'=>null!=$usuario->getFicheroFoto() ? $usuario->getFicheroFoto()->getRuta() : null,
            'user_nombre'=>$usuario->getNombre(),
            'user_correo'=>$usuario->getCorreo(),
            'jefe' => $usuario->getJefe(),
            'plantrabajos' => $plantrabajos];

        return $this->render('plantrabajo/index.html.twig', $parameters);
    }

    /**
     * @Route("/{id}/new", name="plantrabajo_new", methods="GET|POST")
     */
    public function new(Request $request, Usuario $usuario): Response
    {
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
                return new JsonResponse(array('mensaje' => 'El plan de trabajo fue registrado satisfactoriamente',
                    'mes' => $plantrabajo->getMestoString(),
                    'anno' => $plantrabajo->getAnno(),
                    'id' => $plantrabajo->getId(),
                ));
            } else {
                $page = $this->renderView('plantrabajo/_form.html.twig', array(
                    'id' => $usuario->getId(),
                    'form' => $form->createView(),
                ));
                return new JsonResponse(array('form' => $page, 'error' => true,));
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

        $actividads = $this->getDoctrine()
            ->getRepository(Actividad::class)
            ->findBy(array('plantrabajo' => $plantrabajo));

        if ($request->isXmlHttpRequest()) {
            $array = [];
            if ($request->query->has('filtro')) {
                $filtro = $request->get('filtro');
                if ($filtro >= 1 && $filtro <= 5) {
                    $result = array();
                    $status = ['registradas', 'en proceso', 'culminadas', 'cumplidas', 'incumplidas'];
                    $array['filtro'] = 'Actividades ' . $status[$filtro - 1];
                    foreach ($actividads as $activity)
                        if ($activity->getEstado() == $filtro)
                            $result[] = $activity;
                    $actividads = $result;
                }
            }
            $array['table'] = $this->renderView('actividad/_table.html.twig', ['actividads' => $actividads, 'plantrabajo' => $plantrabajo]);
            return new JsonResponse($array);
        }

        return $this->render('plantrabajo/show.html.twig', ['plantrabajo' => $plantrabajo,
            'actividads' => $actividads,
            'user_id' => $plantrabajo->getUsuario()->getId(),
            'user_foto'=>null!=$plantrabajo->getUsuario()->getFicheroFoto() ? $plantrabajo->getUsuario()->getFicheroFoto()->getRuta() : null,
            'user_nombre'=>$plantrabajo->getUsuario()->getNombre(),
            'user_correo'=>$plantrabajo->getUsuario()->getCorreo(),
        ]);
    }


    /**
     * @Route("/{id}/delete",options={"expose"=true}, name="plantrabajo_delete")
     */
    public function delete(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('DELETE', $plantrabajo);
        $em = $this->getDoctrine()->getManager();
        $em->remove($plantrabajo);
        $em->flush();
        return new JsonResponse(array('mensaje' => 'El plan de trabajo fue eliminado satisfactoriamente'));
    }

    /**
     * @Route("/{id}/estadistica", name="plantrabajo_estadistica", options={"expose"=true}, methods="GET")
     */
    public function estadistica(Request $request, Plantrabajo $plantrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);

        $actividads = $this->getDoctrine()
            ->getRepository(Actividad::class)
            ->findBy(array('plantrabajo' => $plantrabajo));
        if (!$request->isXmlHttpRequest())
            throw $this->createAccessDeniedException();

        $estados = ['Registrada'=>0, 'En_proceso'=>0, 'Culminada'=>0,'Cumplida'=>0,'Incumplida'=>0];
        $total = 0;
        foreach ($actividads as $activity) {
            $estados[$activity->getEstadoString()]++;
            $total++;
        }

        $result=[];
        foreach ($estados as $key=>$value){
            $result[]=[
                'estado' => $key,
                'cantidad' => (Integer)$value,
            ];
        }

        return new JsonResponse(
            [
                'view'=>$this->renderView('plantrabajo/ajax/_estadisticas.html.twig', ['plantrabajo' => $plantrabajo, 'estadisticas' => $estados, 'total' => $total]),
                'data'=>json_encode($result)
            ]);
    }

    /**
     * @Route("/{id}/antiguos", name="plantrabajo_antiguos", methods="GET")
     */
    public function antiguos(Request $request, Plantrabajo $plantrabajo): Response
    {
        if (!$request->isXmlHttpRequest())
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
    public function exportar(Request $request, Plantrabajo $plantrabajo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $plantrabajo);
        $em = $this->getDoctrine()->getManager();

        //Listado de actividades objetivos del jefe en el mes Actual
        $usuario = $this->getUser()->getId();
        if ($plantrabajo->getUsuario()->getJefe() != null)
            $usuario = $this->getUser()->getJefe()->getId();
        $consulta = $em->createQuery('SELECT a.nombre FROM App:Actividad a join a.plantrabajo p JOIN p.usuario u WHERE u.id=:usuario AND p.mes=:mes AND p.anno=:anno AND a.esobjetivo=1 ORDER BY a.fecha');
        $consulta->setParameters(array('usuario' => $usuario, 'mes' => $plantrabajo->getMes(), 'anno' => $plantrabajo->getAnno()));
        $actividadesObjetivo = $consulta->getResult();

        $actividadesTable = array();


        //Listado de actividades del mes actual
        $consulta = $em->createQuery('SELECT a.fecha,a.fechaF, a.nombre, a.esobjetivo  FROM App:Actividad a join a.plantrabajo p JOIN p.usuario u WHERE u.id=:usuario AND p.mes=:mes AND p.anno=:anno GROUP BY a.fecha, a.id ORDER BY a.fecha ');
        $consulta->setParameters(array('usuario' => $plantrabajo->getUsuario()->getId(), 'mes' => $plantrabajo->getMes(), 'anno' => $plantrabajo->getAnno()));
        $actividades = $consulta->getResult();

        $fistDay = new \DateTime($plantrabajo->getAnno() . '-' . $plantrabajo->getMes() . '-01');
        $ultimodomingo = $fistDay->modify('last Sun');
        $fistDay = new \DateTime($plantrabajo->getAnno() . '-' . $plantrabajo->getMes() . '-01');
        $ultimoDia = $fistDay->modify('last day of this month');
        $fistDay = new \DateTime($plantrabajo->getAnno() . '-' . $plantrabajo->getMes() . '-01');

        $first = $ultimodomingo->add(new \DateInterval('P1D'));
        $ultimodomingo = $fistDay->modify('last Sun');
        while ($ultimodomingo < $fistDay) {
            $actividadesTable[$ultimodomingo->format('Y-m-d')] = array();
            $ultimodomingo->add(new \DateInterval('P1D'));
        }

        while ($fistDay <= $ultimoDia) {
            if (!array_key_exists($fistDay->format('Y-m-d'), $actividadesTable))
                $actividadesTable[$fistDay->format('Y-m-d')] = array();

            foreach ($actividades as $actividad) {
                if ($fistDay->format('Y-m-d') == $actividad["fecha"]->format('Y-m-d'))
                    $actividadesTable[$fistDay->format('Y-m-d')][] = $actividad;
            }
            $fistDay->add(new \DateInterval('P1D'));
        }

        $parameters = ['plantrabajo' => $plantrabajo, 'actividadesObjetivo' => $actividadesObjetivo, 'actividades' => $actividadesTable];

        //Gestion de meses anteriores
        $mesAnterior = $plantrabajo->getMes() - 1;
        $annoAnterior = $plantrabajo->getAnno();
        if ($mesAnterior == 0) {
            $mesAnterior = 12;
            $annoAnterior--;
        }

        $planAnterior = $em->getRepository('App:PlanTrabajo')->findOneBy(array('mes' => $mesAnterior, 'anno' => $annoAnterior, 'usuario' => $plantrabajo->getUsuario()));

        if ($planAnterior != null) {
            $actividadesAnterior = $em->getRepository('App:Actividad')->findBy(array('plantrabajo' => $planAnterior));
            $contadorCumplidas = 0;
            $contadorExternas = 0;
            $contadorTotal = 0;
            $actividadesanterioresObjetivo = array();
            foreach ($actividadesAnterior as $actividad) {
                if ($actividad->getEstado() == 4) {
                    if ($actividad->getEsexterna())
                        $contadorExternas++;
                    $contadorCumplidas++;
                }
                if ($actividad->getEsobjetivo())
                    $actividadesanterioresObjetivo[] = $actividad->getNombre();
                $contadorTotal++;
            }
            $parameters['mesAnterior'] = $planAnterior->getMesToString();
            $parameters['annoAnterior'] = $annoAnterior;
            $parameters['contadorCumplidas'] = $contadorCumplidas;
            $parameters['contadorExternas'] = $contadorExternas;
            $parameters['contadorTotal'] = $contadorTotal;
            $parameters['actividadesanterioresObjetivo'] = $actividadesanterioresObjetivo;

        }
        $parameters['mesActual'] = $plantrabajo->getMes();
        $parameters['first'] = $first;

        $html = $this->renderView('plantrabajo/exportar.html.twig', $parameters);
        // return new Response($html);
        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'file.pdf'
        );
    }


}
