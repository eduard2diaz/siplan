<?php

namespace App\Form\Subscriber;

use App\Entity\Grupo;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Description of AddCargoFieldSubscriber
 *
 * @author eduardo
 */

class AddDestinatarioFieldSubscriber  implements EventSubscriberInterface{

    private $factory;
    private $em;

    /**
     * AddTarjetaFieldSubscriber constructor.
     */
    public function __construct(FormFactoryInterface $factory, ObjectManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',

        );
    }

    /**
     * Cuando el usuario llene los datos del formulario y haga el envío del mismo,
     * este método será ejecutado.
     */
    public function preSubmit(FormEvent $event) {
        $data = $event->getData();
        if(null===$data){
            return;
        }
        $listado=[];
        $destinatario= is_array($data['iddestinatario']) ? $data['iddestinatario'] : [$data['iddestinatario']];

        foreach ($destinatario as $value){
            $it=explode('grupo-',$value);
            if(count($it)==1)
                $listado[]=$it[0];
            else
            {
                $grupo=$this->em->getRepository(Grupo::class)->find($it[1]);
                if(null!=$grupo) {

                    $listado[] = strval($grupo->getCreador()->getId());
                   foreach ($grupo->getIdMiembro() as $val)
                        $listado[]=strval($val->getId());
                }
            }
        }
        $this->addElements($event->getForm(), $listado);
    }

    protected function addElements($form, $destinatario) {
     /*   dump('destinatario');
        dump($destinatario);*/
        $em=$this->em;
        $usuarios=$this->em->createQuery('SELECT u FROM App:Usuario u WHERE u.id IN (:id)')
                       ->setParameter('id',$destinatario)
                        ->getResult();
     //  dump($usuarios);

        $form->add('iddestinatario',null,array('choices'=>$usuarios,'required'=>true,'label'=>'Destinatario(s)','attr'=>array('placeholder'=>'Escriba los destinatarios',)));
    }

    public function preSetData(FormEvent $event) {

        $data = $event->getData();
        $form = $event->getForm();

       if(null==$data->getId()){
           $form->add('iddestinatario',null,array('choices'=>array(),'required'=>true,'label'=>'Destinatario(s)','attr'=>array('placeholder'=>'Escriba los destinatarios',)));
        }

    }





}
