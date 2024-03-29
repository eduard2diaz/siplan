<?php

namespace App\Form;

use App\Entity\ARC;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\Subscriber\AddSubcapituloFieldSubscriber;

class ARCType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class,array('attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('objetivos', TextareaType::class,array('attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')))
            ->add('capitulo')
        ;

        $factory = $builder->getFormFactory();
        $builder->addEventSubscriber(new AddSubcapituloFieldSubscriber($factory));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ARC::class,
        ]);
    }
}
