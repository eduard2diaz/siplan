<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UsuarioRepository extends ServiceEntityRepository implements UserLoaderInterface {

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /*
     * Funcionalidad que permite autenticar un usuario a partir del username o por el correo
     */

    public function loadUserByUsername($username) {
        $q = $this->createQueryBuilder('u')
                ->where('u.usuario = :username OR u.correo = :correo')
                ->setParameter('username', $username)
                ->setParameter('correo', $username)
                ->getQuery();
        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $username);
            throw new UsernameNotFoundException($message, 0, $e);
        }
        return $user;
    }

    public function refreshUser(UserInterface $user) {//Dependencia de la funcionalidad anterior
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }
        return $this->find($user->getId());
    }

    public function supportsClass($class) {//Dependencia de la funcionalidad anterior
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

   

}
?>
