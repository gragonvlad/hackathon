<?php

namespace Cibum\ConcursoBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProyectoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProyectoRepository extends EntityRepository
{

    /**
     * returns the list of projects
     *
     * @param array $list
     * @return array
     */
    public function findNew(array $list)
    {
        return $this->_em->createQuery('SELECT p.snip FROM CibumConcursoBundle:Proyecto p WHERE p.snip IN :list')
            ->setParameter('list', $list)
            ->getArrayResult();
    }


    /**
     * Returns all snip codes in the db
     *
     * @return array
     */
    public function getAllQuick()
    {
        return $this->_em->createQuery("SELECT CONCAT(CONCAT(p.snip, ':'), a.anho) FROM CibumConcursoBundle:Proyecto p JOIN p.anuales a")
            ->getArrayResult();
    }
}