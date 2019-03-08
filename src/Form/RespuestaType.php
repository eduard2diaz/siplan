<?php

namespace App\Form;

use App\Entity\Respuesta;
use App\Entity\Fichero;
use App\Form\Transformer\DateTimetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RespuestaType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('descripcion', TextareaType::class, array('required' => false, 'label' => 'Descripción', 'attr' => array('class' => 'form-control')))
            ->add('ficheros', CollectionType::class, array(
                'entry_type' => FicheroType::class,
                'allow_add' => true,#para poder a;adir elementos con js
                'allow_delete' => true,
                'by_reference' => true,
                'prototype_data' => new Fichero(),
                'label' => ' ',
                'data'=>[]
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Respuesta::class,
        ]);
    }
}
