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
        $id=$options['data']->getId()!=null ? $options['data']->getUsuario()->getId() : null;
        $builder
            ->add('usuario',null,[
                'required'=>true,
                'auto_initialize'=>false,
                'class'         =>'App:Usuario',
                'query_builder'=>function(EntityRepository $repository) use($id){

                    $res = $repository->createQueryBuilder('usuario');
                    $res->distinct(true);
                    $res->select('u.id')->from('App:MiembroConsejoDireccion', 'cd');
                    $res->join('cd.usuario', 'u');
                    $usuarios = $res->getQuery()->getResult();

                    $qb = $repository->createQueryBuilder('usuario');
                    $qb->innerJoin('usuario.idrol', 'r')
                        ->where('r.nombre IN  (:roles)')
                        ->setParameter('roles', ['ROLE_USER','ROLE_DIRECTIVOINSTITUCIONAL','ROLE_DIRECTIVO','ROLE_COORDINADORINSTITUCIONAL','ROLE_COORDINADORAREA']);
                    if (!empty($usuarios)) {
                        $qb->andWhere('usuario.id NOT IN (:listado)')->setParameter('listado', $usuarios);
                    }
                    if (null != $id) {
                        $qb->orWhere('usuario.id= :id')->setParameter('id', $id);
                    }

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
