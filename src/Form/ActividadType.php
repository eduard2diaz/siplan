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


class ActividadType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actividad=$options['data'];
        $user_id=$this->token->getToken()->getUser()->getId();
        $disabled=$user_id != $actividad->getAsignadapor()->getId() && (null!=$actividad->getResponsable()->getJefe() && $user_id!=$actividad->getResponsable()->getJefe()->getId()) ? true : false;
        $builder
            ->add('fecha', TextType::class, array('disabled' => $disabled, 'label' => 'Fecha de inicio', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('fechaf', TextType::class, array('disabled' => $disabled, 'label' => 'Fecha de fin', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('nombre', TextType::class, array('disabled' => $disabled, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge')))
            ->add('descripcion', TextareaType::class, array('disabled' => $disabled, 'required' => false, 'label' => 'Descripción', 'attr' => array('class' => 'form-control')))
            ->add('esobjetivo', CheckboxType::class, array('disabled' => $disabled, 'label' => 'Marcar como objetivo del plan.', 'required' => false,
                'attr' => array('class' => 'pull-left margin-right-10')
            ))
            ->add('esexterna', CheckboxType::class, array('disabled' => $disabled, 'label' => 'Externa', 'required' => false,
                'attr' => array('class' => 'pull-left margin-right-10')
            ))
            ->add('areaconocimiento', null, array('disabled' => $disabled, 'label' => 'Área del conocimiento', 'required' => true,
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

        if (null != $options['data']->getId()) {
            $user_id = $this->token->getToken()->getUser()->getId();
            $actividad = $options['data'];
            $choices = ['Registrada' => 1, 'En proceso' => 2, 'Culminada' => 3];

            if ($user_id == $actividad->getAsignadapor()->getId() || ($actividad->getResponsable()->getJefe() != null && $actividad->getResponsable()->getJefe()->getId() == $user_id)) {
                $choices['Cumplida'] = 4;
                $choices['Incumplida'] = 5;
            }

            $builder->add('estado', ChoiceType::class, array(
                'choices' => $choices, 'attr' => array('class' => 'form-control input-medium')));
        }

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
