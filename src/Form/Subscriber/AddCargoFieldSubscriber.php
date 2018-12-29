<?php

namespace App\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Entity\Area;
use App\Entity\Cargo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Description of AddCargoFieldSubscriber
 *
 * @author eduardo
 */
class AddCargoFieldSubscriber  implements EventSubscriberInterface{

    private $factory;
    /**
     * AddTarjetaFieldSubscriber constructor.
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $area= is_array($data) ? $data['area'] : $data->getArea();
        $this->addElements($event->getForm(), $area);
    }

    protected function addElements($form, $area) {
        $form->add($this->factory->createNamed('cargo',EntityType::class,null,array(
            'auto_initialize'=>false,
            'class'         =>'App:Cargo',
            'query_builder'=>function(EntityRepository $repository)use($area){
                $qb=$repository->createQueryBuilder('cargo')
                    ->innerJoin('cargo.area','p');
                if($area instanceof Area){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$area);
                }elseif(is_numeric($area)){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$area);
                }else{
                    $qb->where('p.id = :id')
                        ->setParameter('id',null);
                }
                return $qb;
            }
        )));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();

       if(null==$data->getId()){
           $form->add('cargo',null,array('choices'=>array()));
        }else
       {

           $area= is_array($data) ? $data['area'] : $data->getArea();
           $this->addElements($event->getForm(), $area);
       }

    }





}
