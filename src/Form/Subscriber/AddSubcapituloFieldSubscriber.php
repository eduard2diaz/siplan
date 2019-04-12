<?php

namespace App\Form\Subscriber;

use App\Entity\Capitulo;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Entity\Area;
use App\Entity\Subcapitulo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Description of AddSubcapituloFieldSubscriber
 *
 * @author eduardo
 */
class AddSubcapituloFieldSubscriber  implements EventSubscriberInterface{

    private $factory;
    private $required=true;

    public function __construct(FormFactoryInterface $factory,$required=true)
    {
        $this->factory = $factory;
        $this->required=$required;
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

        if(isset($data['capitulo']))
            $this->addElements($event->getForm(), $data['capitulo']);
    }

    protected function addElements($form, $capitulo) {
        $form->add($this->factory->createNamed('subcapitulo',EntityType::class,null,array(
            'auto_initialize'=>false,
            'class'         =>'App:Subcapitulo',
            'label'=>'Subcapítulo',
            'required'=>$this->required,
            'query_builder'=>function(EntityRepository $repository)use($capitulo){
                $qb=$repository->createQueryBuilder('subcapitulo')
                    ->innerJoin('subcapitulo.capitulo','p');
                if($capitulo instanceof Capitulo){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$capitulo);
                }elseif(is_numeric($capitulo)){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$capitulo);
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
           $form->add('subcapitulo',null,array('required'=>$this->required,'label'=>'Subcapítulo','choices'=>array()));
        }else
       {

           $capitulo= is_array($data) ? $data['capitulo'] : $data->getCapitulo();
           $this->addElements($event->getForm(), $capitulo);
       }

    }





}
