<?php

namespace App\Form;

use App\Entity\Actividad;
use App\Entity\Fichero;
use App\Form\Transformer\DateTimetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ActividadGrupoType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actividad=$options['data'];
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
            ->add('lugar', TextType::class, array('required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge')))
            ->add('descripcion', TextareaType::class, array('required' => false, 'label' => 'Descripción', 'attr' => array('class' => 'form-control')))
            ->add('dirigen', TextareaType::class, array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('participan', TextareaType::class, array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('aseguramiento', TextareaType::class, array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('areaconocimiento', null, array('label' => 'Área del conocimiento', 'required' => true,
                'attr' => array('class' => 'form-control input-large')
            ))
            ->add('ficheros', CollectionType::class, array(
                'entry_type' => FicheroType::class,
                'allow_add' => true,#para poder a;adir elementos con js
                'allow_delete' => true,
                'by_reference' => true,
                'prototype_data' => new Fichero(),
                'label' => ' ',
                'data'=>[]
            ));

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
    }
}