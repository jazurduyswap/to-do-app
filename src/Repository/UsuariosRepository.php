<?php

namespace App\Repository;

use App\Entity\Usuarios;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Usuarios>
 */
class UsuariosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuarios::class);
    }

    /**
     * @return Usuarios[] Returns an array of Usuarios objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOneBySomeField($value): ?Usuarios
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    function findById($id): ?Usuarios
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Encuentra un usuario por ID con contraseña limpia para visualización
     */
    function findByIdForDisplay($id): ?Usuarios
    {
        $usuario = $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        
        // Crear una copia para visualización
        if ($usuario) {
            $displayUser = clone $usuario;
            $displayUser->setPassword(''); // Limpiar contraseña en la copia
            return $displayUser;
        }
        
        return null;
    }

    function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra todos los usuarios con contraseñas limpias para visualización
     */
    function findAllForDisplay(): array
    {
        $usuarios = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Crear copias para visualización
        $displayUsers = [];
        foreach ($usuarios as $usuario) {
            $displayUser = clone $usuario;
            $displayUser->setPassword(''); // Limpiar contraseña en la copia
            $displayUsers[] = $displayUser;
        }
        
        return $displayUsers;
    }

    /**
     * Encuentra un usuario con todos sus datos (incluyendo password)
     * Solo para uso interno (autenticación, etc.)
     */
    function findByIdComplete($id): ?Usuarios
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Encuentra un usuario por email con todos sus datos (para autenticación)
     */
    public function findByEmailComplete(string $email): ?Usuarios
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Encuentra un usuario por email SIN contraseña (para mostrar datos)
     */
    public function findByEmailSafe(string $email): ?Usuarios
    {
        $usuario = $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
        
        // Limpiar la contraseña por seguridad
        if ($usuario) {
            $usuario->setPassword('');
        }
        
        return $usuario;
    }

    /**
     * Busca usuarios por nombre SIN contraseña
     */
    public function findByNameSafe(string $nombre): array
    {
        $usuarios = $this->createQueryBuilder('u')
            ->andWhere('u.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Limpiar contraseñas por seguridad
        foreach ($usuarios as $usuario) {
            $usuario->setPassword('');
        }
        
        return $usuarios;
    }

}
