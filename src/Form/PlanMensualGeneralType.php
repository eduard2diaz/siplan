<?php

namespace App\Form;

use App\Entity\PlanMensualGeneral;
use App\Form\Transformer\DatetoStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PlanMensualGeneralType extends AbstractType
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $current_year = date('Y');
        $choices = array($current_year => $current_year);
        if (date('m') == 12) {
            $choices[$current_year + 1] = $current_year + 1;
        }

        $builder
            ->add('mes', ChoiceType::class, array(
                'choices' => array(
                    'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                    'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                    'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12,

                ), 'attr' => array('class' => 'form-control input-medium')))
            ->add('anno', ChoiceType::class, array(
                'label' => 'Año',
                'choices' => $choices
            , 'attr' => array('class' => 'form-control input-medium')
            ))
            ->add('edicionfechainicio', TextType::class, array('label' => 'Fecha de inicio', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )))
            ->add('edicionfechafin', TextType::class, array('label' => 'Fecha de fin', 'attr' => array(
                'autocomplete' => 'off',
                'class' => 'form-control input-small'
            )));

        if ($this->authorizationChecker->isGranted('ROLE_DIRECTIVOINSTITUCIONAL'))
            $builder->add('aprobado');

        $builder->get('edicionfechainicio')
            ->addModelTransformer(new DatetoStringTransformer());
        $builder->get('edicionfechafin')
            ->addModelTransformer(new DatetoStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlanMensualGeneral::class,
        ]);
    }
}
