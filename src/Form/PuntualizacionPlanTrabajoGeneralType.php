<?php

namespace App\Form;

use App\Entity\PuntualizacionPlanMensualGeneral;
use App\Form\Transformer\DateTimetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PuntualizacionPlanTrabajoGeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('actividad',TextType::class,['attr'=>['class'=>'form-control']])
            ->add('tipo',ChoiceType::class,['choices'=>[
                'Registrada'=>'0',
                'Actualizada'=>'1',
                'Eliminada'=>'2',
            ],'attr'=>['class'=>'form-control']])
            ->add('descripcion',TextareaType::class,['label' => 'Descripción','attr'=>['class'=>'form-control']])
            ->add('fechacreacion', TextType::class, array('label' => 'Fecha de creación', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
        ;

        $builder->get('fechacreacion')
            ->addModelTransformer(new DateTimetoStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PuntualizacionPlanMensualGeneral::class,
        ]);
    }
}
