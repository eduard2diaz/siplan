<?php

namespace App\Form;

use App\Entity\Actividad;
use App\Form\Transformer\DateTimetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ActividadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled=$options['disab'];
        $builder
            ->add('fecha', TextType::class,array('disabled' => $disabled,'label'=>'Fecha de inicio', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('fechaf', TextType::class,array('disabled' => $disabled,'label'=>'Fecha de fin', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('nombre', TextType::class,array('disabled' => $disabled,'attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('descripcion', TextareaType::class,array('disabled' => $disabled,'required'=>false,'label'=>'Descripción','attr'=>array('class'=>'form-control')))

            ->add('esobjetivo',CheckboxType::class,array('disabled' => $disabled,'label'=>'Marcar como objetivo del plan.','required'=>false,
                'attr'=>array('class'=>'pull-left margin-right-10')
            ))
            ->add('esexterna',CheckboxType::class,array('disabled' => $disabled,'label'=>'Externa','required'=>false,
                'attr'=>array('class'=>'pull-left margin-right-10')
            ))
            ->add('areaconocimiento',null,array('disabled' => $disabled,'label'=>'Área del conocimiento','required'=>false,
                'attr'=>array('class'=>'form-control input-large')
            ))
        ;


        $builder->get('fecha')
            ->addModelTransformer(new DateTimetoStringTransformer());
        $builder->get('fechaf')
            ->addModelTransformer(new DateTimetoStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Actividad::class,
        ]);
        $resolver->setRequired(['disab']);
    }
}
