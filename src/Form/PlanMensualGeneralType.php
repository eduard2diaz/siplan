<?php

namespace App\Form;

use App\Entity\PlanMensualGeneral;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PlanMensualGeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $current_year=date('Y');
        $choices=array($current_year=>$current_year);
        if (date('m') ==12){
            $choices[$current_year+1]=$current_year+1;
        }

        $builder
            ->add('mes',ChoiceType::class,array(
                'choices'=>array(
                    'Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,
                    'Mayo'=>5,'Junio'=>6,'Julio'=>7,'Agosto'=>8,
                    'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12,

                ),'attr'=>array('class'=>'form-control input-medium')    ))
            ->add('anno',ChoiceType::class,array(
                'label'=>'Año',
                'choices'=>$choices
                ,'attr'=>array('class'=>'form-control input-medium')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlanMensualGeneral::class,
        ]);
    }
}
