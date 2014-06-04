<?php
namespace dlaser\HcBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class MedicamentoRepository extends EntityRepository{
	
	function findExamPresent($usuarioId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT m
								 FROM
									HcBundle:Medicamento m
								 WHERE
									m.usuario = :usuario AND
									m.estado = :estado
								 ORDER BY
									m.principioActivo,
									m.concentracion,
									m.tiempo ASC');
		
		$dql->setParameter('usuario', $usuarioId);
		$dql->setParameter('estado', 'A');
		return $dql->getResult();
	}
	
	function findHcMedicamento($hcId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT
									hm.id,
									hm.estado,
									m.principioActivo,
									m.presentacion,
									m.concentracion,
									m.dosisDia,
									m.tiempo,
									m.diasTratamiento,
									m.pos
								FROM
									HcBundle:HcMedicamento hm
								JOIN
									hm.medicamento m
								WHERE
									hm.hc = :id');
		
		$dql->setParameter('id', $hcId);
		return $dql->getResult();
	}
	
	function findMedicamento($usuarioId){
		
		$em = $this->getEntityManager();
		$dql = $em->createQuery('SELECT m
				FROM
					HcBundle:Medicamento m
				WHERE
					m.usuario = :usuario
				ORDER BY
					m.principioActivo ASC');
		
		$dql->setParameter('usuario', $usuarioId);
		return $dql->getResult();
	}
}