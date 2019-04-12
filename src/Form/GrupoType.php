<?php

namespace App\Form;

use App\Entity\Grupo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use App\Form\Subscriber\AddMiembroFieldSubscriber;

class GrupoType extends AbstractType
{
    private $token;
    private $em;

    public function __construct(TokenStorageInterface $token, ObjectManager $em)
    {
        $this->token = $token;
        $this->em=$em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $id = $this->token->getToken()->getUser()->getId();
        $choices=[];
        if(null!=$data->getId())
            $choices=$data->getIdmiembro();

        $builder
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control']])

            ->add('idmiembro',null,array('choices'=>$choices,'required'=>false,'label'=>'Miembros','attr'=>array('placeholder'=>'Escriba el/ los miembros',)))
            ;
        if (null != $data->getId()) {
            $grupo = $data->getId();
            $id = $data->getCreador()->getId();
            $builder->add('creador', null, ['label'=>'Responsable',
                'required'=>true,
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
                        ->setParameter('roles', ['ROLE_DIRECTIVO', 'ROLE_USER','ROLE_COORDINADOR']);
                }

            ]);
        }

        $factory=$builder->getFormFactory();
        $builder->addEventSubscriber(new AddMiembroFieldSubscriber($factory,$this->em));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Grupo::class,
        ]);
    }
}
