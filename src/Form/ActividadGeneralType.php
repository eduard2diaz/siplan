<?php

namespace App\Form;

use App\Entity\ActividadGeneral;
use App\Form\Transformer\DateTimetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActividadGeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha', TextType::class, array('label' => 'Fecha de inicio', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('fechaf', TextType::class, array('label' => 'Fecha de fin', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('nombre', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge')))
            ->add('lugar', TextType::class, array('required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge')))
            ->add('descripcion', TextareaType::class, array('required' => false, 'label' => 'Descripción', 'attr' => array('class' => 'form-control')))
            ->add('dirigen', TextareaType::class, array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('participan', TextareaType::class, array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('aseguramiento', TextareaType::class, array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('areaconocimiento', null, array('label' => 'Área del conocimiento', 'required' => true,
                'attr' => array('class' => 'form-control input-large')
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
            'data_class' => ActividadGeneral::class,
        ]);
    }
}
