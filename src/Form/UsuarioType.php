<?php

namespace App\Form;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Entity\Cargo;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
use App\Services\AreaService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UsuarioType extends AbstractType
{
    private $token;
    private $authorizationChecker;
    private $areaService;

    public function __construct(TokenStorageInterface $token, AuthorizationCheckerInterface $authorizationChecker, AreaService $areaService)
    {
        $this->token = $token;
        $this->authorizationChecker = $authorizationChecker;
        $this->areaService = $areaService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $esAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
        $area = $this->areaService->areasHijas($this->token->getToken()->getUser()->getArea(), $esAdmin);
        $disabled = $options['data']->getId() == $this->token->getToken()->getUser()->getId();

        $builder
            ->add('ficheroFoto', FotoType::class)
            ->add('nombre', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control input-xlarge', 'pattern' => '^([A-Za-záéíóúñ]{2,}((\s[A-Za-záéíóúñ]{2,})*))*$')))
            ->add('correo', EmailType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control input-medium', 'pattern' => '^[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$')))
            ->add('usuario', TextType::class, array('attr' => array('autocomplete' => 'off', 'class' => 'form-control input-medium')))
            ->add('activo', null, array('disabled' => $disabled, 'required' => false, 'attr' => array('data-on-text' => 'Si', 'data-off-text' => 'No')))
            ->add('area', null, array('choices' => $area, 'disabled' => $disabled, 'label' => 'Área', 'placeholder' => 'Seleccione un área', 'required' => true, 'attr' => array('class' => 'form-control input-medium')));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $obj) {
            $form = $obj->getForm();
            $data = $obj->getData();
            $required = false;
            $constraint = array();
            if (null == $data->getId()) {
                $required = true;
                $constraint[] = new Assert\NotBlank();
            }

            $form->add('password', RepeatedType::class, array('required' => $required,
                'type' => PasswordType::class,
                'constraints' => $constraint,
                'invalid_message' => 'Ambas contraseñas deben coincidir',
                'first_options' => array('label' => 'Contraseña'
                , 'attr' => array('class' => 'form-control input-medium')),
                'second_options' => array('label' => 'Confirmar contraseña', 'attr' => array('class' => 'form-control input-medium'))
            ));
        });

        if ($esAdmin == true) {
            $builder->add('idrol', null, array('disabled' => $disabled, 'label' => 'Rol', 'required' => true, 'attr' => array('class' => 'form-control input-medium')));
            if (null == $options['data']->getId())
                $builder->add('jefe', null, array('choices' => $this->areaService->obtenerDirectivos(), 'label' => 'Jefe', 'placeholder' => 'Seleccione un directivo', 'required' => false, 'attr' => array('class' => 'form-control input-medium')));
            else {
                $subordinados = $this->areaService->subordinadosKey($options['data']);
                $id = $options['data']->getId();
                $builder->add('jefe', null, array(
                    'disabled' => $disabled,
                    'required' => false,
                    'class' => Usuario::class,
                    'query_builder' => function (EntityRepository $er) use ($subordinados, $id) {
                        $qb = $er->createQueryBuilder('u')
                            ->join('u.idrol', 'r')
                            ->where('r.nombre= :role AND u.id!= :id')
                            ->setParameters(['role' => 'ROLE_DIRECTIVO', 'id' => $id]);
                        if (!empty($subordinados)) {
                            $qb->andWhere('u.id NOT IN (:subordinados)')->setParameter('subordinados', $subordinados);
                        }
                        return $qb;
                    },
                    'placeholder' => 'Seleccione un directivo', 'attr' => array('class' => 'form-control input-medium')
                ));
            }

        } else
            $builder->add('idrol', null, array(
                'disabled' => $disabled,
                'class' => Rol::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.nombre IN (:roles)')
                        ->setParameter('roles', ['ROLE_DIRECTIVO', 'ROLE_USER']);
                },
                'label' => 'Rol', 'disabled' => $disabled, 'required' => true, 'attr' => array('class' => 'form-control input-medium')
            ));

            $factory = $builder->getFormFactory();
            $builder->addEventSubscriber(new AddCargoFieldSubscriber($factory, $disabled));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
