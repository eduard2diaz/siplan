<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Fichero;
use App\Entity\FicheroPerfil;

class FicheroSubscriber implements EventSubscriber
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

    public function preRemove(LifecycleEventArgs $args) {
        $entity = $args->getEntity();
        if ($entity instanceof Fichero){
            $fs=new Filesystem();
            $directory=$this->getServiceContainer()->getParameter('storage_directory');
            $fs->remove($directory.DIRECTORY_SEPARATOR.$entity->getRuta());
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'preRemove',
        ];
    }
}
