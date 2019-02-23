<?php

namespace App\Form;

use App\Entity\Grupo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityRepository;


class GrupoType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $id = $this->token->getToken()->getUser()->getId();
        $builder
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('idmiembro', null, ['label' => 'Miembros',
                'query_builder' => function (EntityRepository $er) use ($id) {
                    return $er->createQueryBuilder('u')
                        ->join('u.idrol', 'r')
                        ->where('r.nombre IN (:roles) AND u.id!= :id')
                        ->setParameters(['roles' => ['ROLE_DIRECTIVO', 'ROLE_USER'], 'id' => $id]);
                }
            ]);
        if (null != $data->getId()) {
            $grupo = $data->getId();
            $id = $data->getCreador()->getId();
            $builder->add('creador', null, [
                'query_builder' => function (EntityRepository $er) use ($grupo, $id) {
                    $qb = $er->createQueryBuilder('creador');
                    $qb->distinct(true);
                    $qb->select('u')->from('App:Usuario', 'u');
                    $qb->join('u.solicitudGrupos', 'sg');
                    $qb->join('sg.grupo', 'g');
                    $qb->where('g.id= :grupo AND sg.estado=1')->setParameter('grupo', $grupo);
                    $result = $qb->getQuery()->getResult();

                    $qb = $er->createQueryBuilder('usuario');
                    $qb->where('usuario.id IN (:solicitudesaceptadas)')->setParameter('solicitudesaceptadas', $result);
                    $qb->orWhere('usuario.id =:id')->setParameter('id', $id);
                    return $qb;

                    return $er->createQueryBuilder('u')
                        ->join('u.idrol', 'r')
                        ->where('r.nombre IN (:roles)')
                        ->setParameter('roles', ['ROLE_DIRECTIVO', 'ROLE_USER']);
                }

            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Grupo::class,
        ]);
    }
}
