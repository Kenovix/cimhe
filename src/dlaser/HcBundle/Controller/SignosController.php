<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\Hc;
use dlaser\HcBundle\Form\HcAuxType;

class SignosController extends Controller
{	
	public function listAction($id)
	{
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(10);
		
		$em = $this->getDoctrine()->getEntityManager();
		$dql = $em->createQuery("SELECT hc FROM HcBundle:Hc hc JOIN hc.factura f JOIN f.paciente p
					WHERE  p.id = :id ORDER BY hc.fecha DESC ");				
		
		$dql->setParameter('id', $id);
		$HC = $paginador->paginate($dql->getResult())->getResult();			
		$paciente = $em->getRepository('ParametrizarBundle:Paciente')->find($id);
		
		if(!$HC )
		{
			throw $this->createNotFoundException('La informacion solicitada no existe');
		}		
		
			
		return $this->render('HcBundle:Signos:list.html.twig', array('entities' => $HC, 'paciente'=>$paciente));
	}
	
	public function signosAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();

		$dql = $em->createQuery("SELECT hc FROM HcBundle:Hc hc WHERE  hc.factura = :factura");
	
		$dql->setParameter('factura', $id);
		$signos = $dql->getResult();
		
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
		if(!$factura)
		{
			throw $this->createNotFoundException('La factura no existe');
		}
		$paciente = $factura->getPaciente();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Historia",$this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Signos");
					
		
		return $this->render('HcBundle:Signos:signos.html.twig', 
				array('entities' => $signos,
					  'paciente' => $paciente,
					  'factura' => $factura ));
	}
	
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $id));
		
		if(!$entity)
		{
			throw $this->createNotFoundException('La informacion no existe');
		}
		
		$factura = $entity->getFactura();
		$paciente = $entity->getFactura()->getPaciente();
		$form   = $this->createForm(new HcAuxType(), $entity);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Historia",$this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Signos ",$this->get("router")->generate("signos_signos",array("id" => $id)));
		$breadcrumbs->addItem("Modificar");
		
		return $this->render('HcBundle:Signos:edit.html.twig', array(
				'paciente' => $paciente,
				'factura' => $factura,
				'entity' => $entity,				
				'edit_form'   => $form->createView()
		));
	}
	
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:Hc')->find($id);
		
		if(!$entity)
		{
			throw $this->createNotFoundException('La informacion no existe');
		}
		
		$factura = $entity->getFactura();
		$paciente = $entity->getFactura()->getPaciente();		
		$form = $this->createForm(new HcAuxType(), $entity);
		
		$request = $this->getRequest();
		$form->bindRequest($request);
			
		if ($form->isValid())
		{
			$factura->setEstado('PI');
			
			$em->persist($factura);
			$em->persist($entity);
			$em->flush();
			
			$this->get('session')->setFlash('info', 'Los signos se han guardado éxitosamente.');
			
			return $this->redirect($this->generateUrl('signos_edit', array('id' => $entity->getFactura()->getId())));
			
		}
		
		return $this->render('HcBundle:Signos:edit.html.twig', array(
				'paciente' => $paciente,
				'factura' => $factura,
				'entity' => $entity,				
				'edit_form'   => $form->createView()
		));
	}
	
	public function listExamenesAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
		
		if(!$factura)
		{
			throw $this->createNotFoundException('La factura no existe');
		}
		
		$paciente = $factura->getPaciente();
		$cargo = $factura->getCargo();
		
		$ultimaCx = $em->createQuery('SELECT
										f.id,
										f.fecha
									 FROM
										ParametrizarBundle:Factura f
									 WHERE 
										f.paciente = :paciente AND
										f.cargo = :cargo
									 ORDER BY f.fecha DESC');
		
		$ultimaCx->setParameter('paciente', $paciente);
		$ultimaCx->setParameter('cargo', $cargo);
		
		$cxAnt = $ultimaCx->getArrayResult();
		
		if(count($cxAnt) > 1){
			$hc = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $cxAnt[1]['id']));
			
			$dql = $em->createQuery('SELECT
										he.fecha,
										he.resultado,
										he.id,
										he.estado,
										e.nombre
									FROM
										HcBundle:HcExamen he
									JOIN
										he.examen e
									WHERE
										he.hc = :hc AND
										he.estado = :estado');
			
			$dql->setParameter('hc', $hc->getId());
			$dql->setParameter('estado', 'P');
			
			$examen = $dql->getResult();
		}else {
			$examen = null;
		}

		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Historia",$this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Signos ",$this->get("router")->generate("signos_signos",array("id" => $id)));
		$breadcrumbs->addItem("Listar examen");

		return $this->render('HcBundle:Signos:listExamenes.html.twig', array(
				'examenes' => $examen,
				'factura' => $factura,
				'paciente' => $paciente,
		));
	}
}
