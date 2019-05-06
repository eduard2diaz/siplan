<?php

namespace App\Form;

use App\Entity\Plantrabajo;
use App\Form\template\YearType;
use App\Tool\Util;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PlantrabajoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mes',ChoiceType::class,[
                'choices'=>Util::LISTADO_MESES,'attr'=>['class'=>'form-control input-medium']])
            ->add('anno',YearType::class,['attr'=>['class'=>'form-control input-medium']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Plantrabajo::class,
        ]);
    }
}
