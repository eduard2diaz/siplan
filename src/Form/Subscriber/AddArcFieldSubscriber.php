<?php

namespace App\Form\Subscriber;

use App\Entity\Capitulo;
use App\Entity\Subcapitulo;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Entity\Area;
use App\Entity\Arc;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Description of AddArcFieldSubscriber
 *
 * @author eduardo
 */
class AddArcFieldSubscriber  implements EventSubscriberInterface{

    private $factory;

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

        if(isset($data['subcapitulo']))
            $this->addElements($event->getForm(), $data['subcapitulo']);
    }

    protected function addElements($form, $subcapitulo) {
        $form->add($this->factory->createNamed('areaconocimiento',EntityType::class,null,array(
            'auto_initialize'=>false,
            'class'         =>'App:Arc',
            'label'=>'Área de resultados claves',
            'required'=>false,
            'query_builder'=>function(EntityRepository $repository)use($subcapitulo){
                $qb=$repository->createQueryBuilder('arc')
                    ->innerJoin('arc.subcapitulo','p');
                if($subcapitulo instanceof Subcapitulo){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$subcapitulo);
                }elseif(is_numeric($subcapitulo)){
                    $qb->where('p.id = :id')
                        ->setParameter('id',$subcapitulo);
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
           $form->add('areaconocimiento',null,array('required'=>false,'label'=>'Área de resultados claves','choices'=>array()));
        }else
       {

           $subcapitulo= is_array($data) ? $data['subcapitulo'] : $data->getSubcapitulo();
           $this->addElements($event->getForm(), $subcapitulo);
       }

    }





}
