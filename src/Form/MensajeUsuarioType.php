<?php

namespace App\Form;

use App\Entity\Mensaje;
use App\Form\Subscriber\AddDestinatarioFieldSubscriber;
use App\Form\Transformer\DestinatarioTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\Common\Persistence\ObjectManager;

class MensajeUsuarioType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('asunto', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control',)))
        ->add('descripcion',TextareaType::class,array('required'=>true,'label'=>'Contenido','attr'=>array('rows'=>5,'autocomplete'=>'off','placeholder'=>'Escriba el contenido del mensaje','class'=>'form-control input-xxlarge')))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mensaje::class,
        ]);
    }
}
