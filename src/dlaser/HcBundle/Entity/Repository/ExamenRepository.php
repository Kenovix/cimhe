<?php
namespace dlaser\HcBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ExamenRepository extends EntityRepository{
	
	function findHcExamPresent($hcAntId){
		
		$em = $this->getEntityManager();
				
		$dql = $em->createQuery('SELECT
										he.id,
										he.fecha,
										he.resultado,
										he.fecha_r,
										he.estado,
										e.nombre,
										e.codigo
									FROM
										HcBundle:HcExamen he
									JOIN
										he.examen e
									WHERE
										he.hc = :hc
									ORDER BY
										he.fecha DESC');
		
		$dql->setParameter('hc', $hcAntId);
		return $dql->getResult();
	}
	
	function findHcExamPresentPriVez($hcId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT
					he.id,
					he.fecha,
					he.resultado,
					he.fecha_r,
					he.estado,
					e.nombre,
					e.codigo
				FROM
					HcBundle:HcExamen he
				JOIN
					he.examen e
				WHERE
					he.hc = :hc AND
					he.resultado != :resultado');
			
		$dql->setParameter('hc', $hcId);
		$dql->setParameter('resultado', 'NULL');
		return $dql->getResult();
	}
	
	function findHcExaSolicitado($hcId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT
					he.fecha,
					he.fecha_r,
					he.resultado,
					he.id,
					he.estado,
					e.nombre,
					e.codigo
				FROM
					HcBundle:HcExamen he
				JOIN
					he.examen e
				WHERE
					he.estado = :estado AND
					he.hc = :id');
		
		$dql->setParameter('estado', 'P');
		$dql->setParameter('id', $hcId);		
		return $dql->getResult();
	}
}