<?php

namespace App\Form;

use App\Entity\MiembroConsejoDireccion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
class MiembroConsejoDireccionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('usuario',null,[
                'auto_initialize'=>false,
                'class'         =>'App:Usuario',
                'query_builder'=>function(EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('usuario')
                        ->innerJoin('usuario.idrol', 'r')
                        ->where('r.nombre IN  (:roles)')
                        ->setParameter('roles', ['ROLE_USER','ROLE_DIRECTIVO','ROLE_COORDINADOR']);

                    return $qb;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MiembroConsejoDireccion::class,
        ]);
    }
}
