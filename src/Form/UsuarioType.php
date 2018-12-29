<?php

namespace App\Form;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Entity\Cargo;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Form\Subscriber\AddCargoFieldSubscriber;
use Symfony\Component\Validator\Constraints as Assert;

class UsuarioType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $esAdmin = $options['parameters']['esAdmin'];
        $disabled=$options['parameters']['disab'];
        $area=$options['parameters']['area'];
        $auxdisabled=$options['parameters']['disab'];
        if($esAdmin)
            $auxdisabled=false;

        $builder
            ->add('nombre', TextType::class, array('attr' => array('autocomplete'=>'off','class' => 'form-control input-xlarge')))
            ->add('correo', EmailType::class, array('disabled' => $disabled,'attr' => array('autocomplete'=>'off','class' => 'form-control input-medium')))
            ->add('usuario', TextType::class, array('disabled' => $disabled,'attr' => array('autocomplete'=>'off','class' => 'form-control input-medium')))
            ->add('activo',null,array('disabled' => $disabled,'required'=>false,'attr'=>array('data-on-text'=>'Si','data-off-text'=>'No')))
            ->add('area', null, array('choices'=>$area,'disabled' => $auxdisabled,'label' => 'area',  'placeholder'=>'select_areaplaceholder', 'required' => true, 'attr' => array('class' => 'form-control input-medium')))
            /*    ->add('cargo',null,array('required'=>true,'attr'=>array('class'=>'form-control input-medium')));*/
            ->add('cargo', EntityType::class, array('disabled' => $disabled,
                'required' => true,
                'class' => Cargo::class,
                'choices' => array()
            , 'attr' => array(
                    'class' => 'form form-control input-medium'
                )));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $obj) {
            $form = $obj->getForm();
            $data = $obj->getData();
            $required = false;
            $constraint=array();
            if (null == $data->getId()){
                $required = true;
                $constraint[]=new Assert\NotBlank();
            }

            $form->add('password', RepeatedType::class, array('required' => $required,
                'type' => PasswordType::class,
                'constraints' => $constraint,
                'invalid_message' => 'confirm_password_field_error',
                'first_options' => array('label' => 'password_field'
                , 'attr' => array('class' => 'form-control input-medium')),
                'second_options' => array('label' => 'confirm_password_field', 'attr' => array('class' => 'form-control input-medium'))
            ));
        });

        if ($esAdmin) {
            $builder->add('idrol', null, array('disabled' => $disabled,'label' => 'Rol', 'required' => true, 'attr' => array('class' => 'form-control input-medium')));
            $builder->add('jefe', null, array('choices'=>$options['parameters']['directivos'],'disabled' =>  $disabled,'label' => 'Jefe',  'placeholder'=>'Seleccione un directivo', 'required' => false, 'attr' => array('class' => 'form-control input-medium')));
        } else
            $builder->add('idrol', null, array(
                'disabled' => $disabled,
                'class' => Rol::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.nombre IN (:roles)')
                        ->setParameter('roles', ['ROLE_DIRECTIVO', 'ROLE_USER']);
                },
                //'choice_label' => 'u.nombre',
                'label' => 'Rol','disabled' => $disabled, 'required' => true, 'attr' => array('class' => 'form-control input-medium')
            ));;


        $factory=$builder->getFormFactory();
        $builder->addEventSubscriber(new AddCargoFieldSubscriber($factory));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
        $resolver->setRequired(['parameters']);
    }
}
