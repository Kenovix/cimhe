<?php
namespace dlaser\HcBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CieRepository extends EntityRepository{
	
	function findCie($usuarioId,$hcId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT
									c
								 FROM
									HcBundle:Cie c
								JOIN
									c.usuario u
								WHERE
									u.id = :id AND
									c.id NOT IN (
										SELECT
											C
										FROM
											HcBundle:Cie C
										JOIN
											C.hc h
										WHERE
											h.id = :hc)
								ORDER BY
									c.nombre ASC');
		
		$dql->setParameter('id', $usuarioId);
		$dql->setParameter('hc', $hcId);
		return $dql->getResult();
	}
	
}