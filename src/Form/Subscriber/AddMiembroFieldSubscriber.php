<?php

namespace App\Form\Subscriber;

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

class AddMiembroFieldSubscriber  implements EventSubscriberInterface{

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

        if(isset($data['idmiembro']))
            $this->addElements($event->getForm(), $data['idmiembro']);

    }

    protected function addElements($form, $miembro) {
        $em=$this->em;
        $usuarios=$this->em->createQuery('SELECT u FROM App:Usuario u JOIN u.idrol r WHERE u.id IN (:id) AND r.nombre IN (:roles)')
                       ->setParameters(['id'=>$miembro,'roles' => ['ROLE_DIRECTIVO', 'ROLE_USER','ROLE_COORDINADOR','ROLE_ADMIN']])
                        ->getResult();
       $form->add('idmiembro',null,array('choices'=>$usuarios,'required'=>true,'label'=>'miembros','attr'=>array('placeholder'=>'Escriba los miembros',)));

    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();

       if(null==$data->getId()){
           $form->add('idmiembro',null,array('choices'=>array(),'required'=>false,'label'=>'Miembros','attr'=>array('placeholder'=>'Escriba los miembros',)));
        }

    }





}
