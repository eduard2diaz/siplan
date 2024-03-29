<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Usuario;

class UsuarioSubscriber implements EventSubscriber
{
    private $serviceContainer;

    function __construct(ContainerInterface $serviceContainer) {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return mixed
     */
    public function getServiceContainer() {
        return $this->serviceContainer;
    }

    public function prePersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();
        $em=$args->getEntityManager();
        if ($entity instanceof Usuario){
            $entity->setPassword($this->getServiceContainer()->get('security.password_encoder')->encodePassword($entity,$entity->getPassword()));
            //Si estan subiendo una instancia de un fichero y se envió algún archivo
            if (null!=$entity->getFile()) {
                $entity->Upload($this->getServiceContainer()->getParameter('storage_directory'));
                $entity->setFile(null);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }
}
