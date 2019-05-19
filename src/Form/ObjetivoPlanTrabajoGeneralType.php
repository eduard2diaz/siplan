<?php

namespace App\Form;

use App\Entity\ObjetivoPlanMensualGeneral;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjetivoPlanTrabajoGeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numero',IntegerType::class,['label'=>'Número','attr'=>['class'=>'form-control']])
            ->add('descripcion',TextareaType::class,['label' => 'Descripción','attr'=>['class'=>'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ObjetivoPlanMensualGeneral::class,
        ]);
    }
}
