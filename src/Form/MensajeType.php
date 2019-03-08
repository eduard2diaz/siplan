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

class MensajeType extends AbstractType
{
    private $em;

    /**
     * MensajeType constructor.
     * @param $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('asunto', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control',)))
        ->add('iddestinatario',null,array('choices'=>array(),'required'=>true,'label'=>'Destinatario(s)','attr'=>array('placeholder'=>'Escriba el/ los destinatarios',)))
        ->add('descripcion',TextareaType::class,array('required'=>true,'label'=>'Contenido','attr'=>array('rows'=>5,'autocomplete'=>'off','placeholder'=>'Escriba el contenido del mensaje','class'=>'form-control input-xxlarge')))
        ;
        $factory=$builder->getFormFactory();
        $builder->addEventSubscriber(new AddDestinatarioFieldSubscriber($factory,$this->em));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mensaje::class,
        ]);
    }
}
