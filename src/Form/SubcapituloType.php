<?php

namespace App\Form;

use App\Entity\Subcapitulo;
use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SubcapituloType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class,array('attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('capitulo', null,array('label'=>'Capítulo','required'=>true,'attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('numero',IntegerType::class,['label'=>'Número','attr'=>['class'=>'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subcapitulo::class,
        ]);
    }
}
