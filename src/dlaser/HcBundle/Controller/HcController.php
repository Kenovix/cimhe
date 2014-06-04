<?php

namespace dlaser\HcBundle\Controller;


use dlaser\InformeBundle\Entity\Mapa;
use dlaser\HcBundle\Entity\HcMedicamento;
use dlaser\HcBundle\Entity\HcExamen;
use dlaser\ParametrizarBundle\Entity\Factura;
use dlaser\ParametrizarBundle\Entity\Paciente;
use dlaser\HcBundle\Entity\Hc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use dlaser\HcBundle\Form\MedicamentoType;
use dlaser\InformeBundle\Form\ThType;
use dlaser\HcBundle\Form\HcType;
use dlaser\HcBundle\Form\searchType;
use dlaser\HcBundle\Form\HcExamenType;
use dlaser\InformeBundle\Form\TeType;


class HcController extends Controller{
	
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();		
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
		
		if(!$factura)
		{
			throw $this->createNotFoundException('La factura no existe');
		}
		
		$hc = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $id));
		
		if(!$hc)
		{
			throw $this->createNotFoundException('La historia clinica no existe');
		}

		$paciente = $factura->getPaciente();
		$cargo = $factura->getCargo();
	
		//-----------------------consultas de usuario con su respectiva relacion de sus signos -----------------------------
		$usuario = $this->get('security.context')->getToken()->getUser();	

		$signos = $em->getRepository('HcBundle:Hc')->findSignos($paciente->getId());
			
		//------------------------------------- MEDICAMENTO relacionados con el usuario--------------------------------------------------------
		
		$medicamento = $em->getRepository('HcBundle:Medicamento')->findMedicamento($usuario->getId());
		
		$hcMe = $em->getRepository('HcBundle:HcMedicamento')->findHcMedicamento($hc->getId());	
		
		//------------------------------------- END MEDICAMENTO-----------------------------------------------------
	
		//-------------------------------------DIAGNOSTICOS---------------------------------------------------------
		
		$cie = $em->getRepository('HcBundle:Cie')->findCie($usuario->getId(),$hc->getId());		
			
		$hcCie = $hc->getCie();
		
		$list_dx = $em->createQuery('SELECT cie FROM HcBundle:Cie cie ORDER BY cie.nombre ASC')->getResult(); // listar todos los cie para q el usuario los registre con su perfil	
		
		//-------------------------------------END DIAGNOSTICOS-----------------------------------------------------
	
		//-------------------------------------EXAMENES---------------------------------------------------------
		
		$cxAnt = $em->getRepository('ParametrizarBundle:Factura')->findCxAnt($paciente,$cargo);
						
		if(count($cxAnt) > 1){
			
			$hc_ant = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $cxAnt[1]['id']));

			if($hc_ant){
				$exaPresentados = $em->getRepository('HcBundle:HcExamen')->findHcExamPresent($hc_ant->getId());
			}else{
				$exaPresentados = null;
			}
		}else {
			$exaPresentados = null;
		}
		
		// examenes presentados por primer vez
		$exaPresenPrimerVez = $em->getRepository('HcBundle:HcExamen')->findHcExamPresentPriVez($hc->getId());
		
		if(!$exaPresenPrimerVez){
			$exaPresenPrimerVez = null;		
		}
		
		// examenes solicitados
		$exa_solicitado = $em->getRepository('HcBundle:HcExamen')->findHcExaSolicitado($hc->getId());			
		$examenes = $usuario->getExamen(); // examenes del usuario		
		$exaGeneral = $em->getRepository('HcBundle:Examen')->findAll();
		
		//-------------------------------------END EXAMENES---------------------------------------------------------
		
		//-------------------------------------HC Estetica----------------------------------------------------------
		
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->findOneBy(array('hc' => $hc->getId()));

		//-------------------------------------End HC Estetica------------------------------------------------------
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Modificar HC");
		
		
		$editform = $this->createForm(new HcType(), $hc);
		$editexform = $this->createForm(new HcExamenType());
		$medform = $this->createForm(new MedicamentoType());
	
		return $this->render('HcBundle:HistoriaClinica:edit.html.twig', array(
				'entity' => $hc,
				'medicamentos' => $medicamento,
				'perHcMe' => $hcMe,
				'examenes' => $examenes,
				'exaPresentados' => $exaPresentados,
				'exaPrePrimerVez' => $exaPresenPrimerVez,
				'list_ex' => $exaGeneral,
				'exa_solicitado' => $exa_solicitado,
				'cies' => $cie,
				'list_dx' => $list_dx,
				'perHcCie' => $hcCie,
				'signos' => $signos,
				'factura' => $factura,
				'paciente' => $paciente,
				'hcEstetica' =>	$hcEstetica,	
				'edit_form'   => $editform->createView(),
				'ex_form'   => $editexform->createView(),
				'med_form' => $medform->createView()
		));
	}
	
	
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		
		$hc = $em->getRepository('HcBundle:Hc')->find($id);
	
		if(!$hc)
		{
			throw $this->createNotFoundException('La historia clinica no existe.');
		}
			
		$editform = $this->createForm(new HcType(), $hc);
		$editexform = $this->createForm(new HcExamenType());
		$medform = $this->createForm(new MedicamentoType());
		
		$request = $this->getRequest();		
		$editform->bindRequest($request);
	
		if ($editform->isValid()) {

			$factura = $hc->getFactura();
			$em->persist($hc);
			$em->flush();

			$factura->setEstado('I');
			$em->persist($factura);
			$em->flush();
				
			$this->get('session')->setFlash('ok', 'La historia clinica ha sido modificada éxitosamente.');			
			return $this->redirect($this->generateUrl('hc_edit', array('id' => $factura->getId())));
		}
		
		$factura = $hc->getFactura();
		$paciente = $factura->getPaciente();		
		
		$usuario = $this->get('security.context')->getToken()->getUser();
		
		$medicamento = $em->getRepository('HcBundle:Medicamento')->findMedicamento($usuario->getId());
		
		$hcMe = $em->getRepository('HcBundle:HcMedicamento')->findHcMedicamento($hc->getId());
		
		//------------------------------------- END MEDICAMENTO-----------------------------------------------------
		
		//-------------------------------------DIAGNOSTICOS---------------------------------------------------------
		
		$cie = $em->getRepository('HcBundle:Cie')->findCie($usuario->getId(),$hc->getId());			
		$hcCie = $hc->getCie();		
		$list_dx = $em->getRepository("HcBundle:Cie")->findAll();
		
		//-------------------------------------END DIAGNOSTICOS-----------------------------------------------------
		
		//-------------------------------------EXAMENES---------------------------------------------------------
		
		// examenes de la ultima consulta
		$cxAnt = $em->getRepository('ParametrizarBundle:Factura')->findCheckExm($paciente,$cargo);
		
		
		if(count($cxAnt) > 1){
			$hc_ant = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $cxAnt[1]['id']));
			
			$exaPresentados = $em->getRepository('HcBundle:HcExamen')->findHcExamPresent($hc_ant->getId());
			
		}else {
			$exaPresentados = null;
		}
		
		// examenes presentados por primera vez
		$exaPresenPrimerVez = $em->getRepository('HcBundle:HcExamen')->findHcExamPresentPriVez($hc->getId());
				
		if(!$exaPresenPrimerVez){
			$exaPresenPrimerVez = null;
		}		
		$exaGeneral = $em->getRepository('HcBundle:Examen')->findAll();
		
		//-------------------------------------END EXAMENES---------------------------------------------------------
		
		//-------------------------------------HC Estetica----------------------------------------------------------
		
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->findOneBy(array('hc' => $hc->getId()));
		
		//-------------------------------------End HC Estetica------------------------------------------------------
		
		$examenes = $usuario->getExamen();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Modificar HC");
		
		return $this->render('HcBundle:HistoriaClinica:edit.html.twig', array(
				'entity' => $hc,
				'medicamentos' => $medicamento,
				'perHcMe' => $hcMe,
				'examenes' => $examenes,
				'exaPresentados' => $exaPresentados,
				'exaPrePrimerVez' => $exaPresenPrimerVez,
				'list_ex' => $exaGeneral,
				'cies' => $cie,
				'list_dx' => $list_dx,
				'perHcCie' => $hcCie,
				'factura' => $factura,
				'paciente' => $paciente,
				'hcEstetica' =>	$hcEstetica,
				'edit_form'   => $editform->createView(),
				'ex_form'   => $editexform->createView(),
				'med_form' => $medform->createView()
		));
	}
	
	/* buscar la historia clinica de un paciente con su respectivo id tal como la cedula 
	 * 
	 */
	public function searchAction()
	{
		$form   = $this->createForm(new searchType());	

		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Buscar");

			return $this->render('HcBundle:HistoriaClinica:search.html.twig', array(
					'form'   => $form->createView()
			));		
	}	
	
	public function listAction()
	{
		$request = $this->getRequest();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);
		
		$form   = $this->createForm(new searchType());
		$form->bindRequest($request);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Listar");
		
		if($form->isValid())
		{
			$idP = $form->get('paciente')->getData();
			$tipoid = $form->get('tipoid')->getData();
			
			$em = $this->getDoctrine()->getEntityManager();
			
			$dql = $em->createQuery("SELECT p.id FROM ParametrizarBundle:Paciente p WHERE p.identificacion = :id");
			$dql->setParameter('id', $idP);
			$identifi = $dql->getResult();
			
			
			if($identifi)
			{
				$identifi = $dql->getSingleResult();				
				$paciente = $em->getRepository('ParametrizarBundle:Paciente')->find($identifi['id']);

				$HC = $paginador->paginate($em->getRepository('HcBundle:Hc')->findListHc($idP,$tipoid))->getResult();
									
				if($HC)
				{						
					return $this->render('HcBundle:HistoriaClinica:list.html.twig', array(
							'factura' => $HC,
							'paciente' => $paciente,
							'form'   => $form->createView()
					));
				
				}else{
					$this->get('session')->setFlash('info','El paciente no tiene una historia clinica disponible.');
					return $this->redirect($this->generateUrl('hc_search'));
				}								
				
			}else{
				$this->get('session')->setFlash('error','Verifique que la cedula del paciente este correcta.');
				return $this->redirect($this->generateUrl('hc_search'));
			}
							
			
		}else{
			
			return $this->redirect($this->generateUrl('hc_search'));
		}
	}
	
	public function paginatorAction($id)
	{		
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Listar");
		
				
		$form   = $this->createForm(new searchType());		
		$paciente = $em->getRepository('ParametrizarBundle:Paciente')->find($id);
			
			if(!$paciente)
			{
				throw $this->createNotFoundException('No hay pacientes disponibles.');
			}
			
			$HC = $paginador->paginate($em->getRepository('HcBundle:Hc')->findPaginadorHc($paciente->getId()))->getResult();
				
										
			if(!$HC)
			{
				$this->get('session')->setFlash('warning','No hay informacion disponible');
		
				return $this->render('HcBundle:HistoriaClinica:list.html.twig', array(
						'factura' => $HC,
						'paciente' => $paciente,						
						'form'   => $form->createView()
				));		
			}
		
			return $this->render('HcBundle:HistoriaClinica:list.html.twig', array(
					'factura' => $HC,
					'paciente' => $paciente,					
					'form'   => $form->createView()
			));		
	}
	
		
	
	/* el id que llega a este contrlador proviene del bundle genda este id hace 
	 * referencia al id de la factura q a su ves esta relacionada con el paciente
	 * 
	 * para crear una nueva HC solo la puede hacer el medico.
	 */	
	public function newAction($id)
	{
		
		$em = $this->getDoctrine()->getEntityManager();				
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
			
		$existe = $em->getRepository('HcBundle:Hc')->findByFactura($id);
		if(!$factura || $existe)
		{
			throw $this->createNotFoundException('La operacion realizada es incorrecta');
		}
		
		$entity  = new Hc();
		$entity->setFecha(new \DateTime('now'));
		$form    = $this->createForm(new HcType(), $entity);	

		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Nueva HC");
		
		return $this->render('HcBundle:HistoriaClinica:new.html.twig', array(								
				'factura' => $factura,
				'form'   => $form->createView()
		));		
	}
	
	public function saveAction($id)
	{		
		$entity  = new Hc();	
		$request = $this->getRequest();
		$form    = $this->createForm(new HcType(), $entity);
		
		$em = $this->getDoctrine()->getEntityManager();		
		$form->bindRequest($request);
		
		
						
			if(!$form->isValid())
			{
				$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
				
				if(!$factura)
				{
					throw $this->createNotFoundException('No hay facturas disponibles.');
				}
				
				$entity->setFactura($factura);
				$em->persist($entity);
				$em->flush();
				
				//-------- se matiene el estado de la factura a N para que no pueda ser impresa
				$factura->setEstado('N');
				$em->persist($factura);
				$em->flush();
												
				$this->get('session')->setFlash('info',
						'¡Enhorabuena! La historia clinica se ha registrado correctamente ');
					
				return $this->redirect($this->generateUrl('hc_search'));
			}		

					
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Nueva HC");
			
		return $this->render('HcBundle:HistoriaClinica:new.html.twig', array(								
				'factura' => $factura,
				'form'   => $form->createView()
		));

	}	
	
	

	public function imprimirAction($factura)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$hc = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $factura));
		
		if(!$hc)
		{
			throw $this->createNotFoundException('La informacion de la historia no esta disponible.');
		}
		$factura = $hc->getFactura();		
		$cliente = $factura->getCliente();
		$sede = $factura->getSede();
		$paciente = $factura->getPaciente();
		$cargo = $factura->getCargo();
		
		//-----------------------consultas de usuario con su respectiva relacion -----------------------------

		$usuario = $this->get('security.context')->getToken()->getUser();
		
		//------------------------------------- MEDICAMENTO --------------------------------------------------------
		$medicamento = $em->getRepository('HcBundle:Medicamento')->findByUsuario($usuario->getId());
		
		$hcMe = $em->getRepository('HcBundle:HcMedicamento')->findHcMedicamento($hc->getId());
		
		//------------------------------------- END MEDICAMENTO-----------------------------------------------------
		
		//-------------------------------------DIAGNOSTICOS---------------------------------------------------------
		
		$cie = $em->getRepository('HcBundle:HcMedicamento')->findHcMedicamento($usuario->getId(),$hc->getId());			
		$hcCie = $hc->getCie();
		//-------------------------------------END DIAGNOSTICOS-----------------------------------------------------
		
		//-------------------------------------EXAMENES---------------------------------------------------------
		
		$cxAnt = $em->getRepository('ParametrizarBundle:Factura')->findCheckExm($paciente,$cargo);		
		
		if(count($cxAnt) > 1){
			$hc_ant = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $cxAnt[1]['id']));
			
			if($hc_ant){
				$exa_presentado = $em->getRepository('HcBundle:HcExamen')->findHcExamPresent($hc_ant->getId());
			}else{
				$exa_presentado = null;
			}
		}else {
			$exa_presentado = null;
		}
		
		$exaPresenPrimerVez = $em->getRepository('HcBundle:HcExamen')->findHcExamPresentPriVez($hc->getId());	
		
		if(!$exaPresenPrimerVez){
			$exaPresenPrimerVez = null;
		}
		
		$exa_solicitado = $em->getRepository('HcBundle:HcExamen')->findHcExaSolicitado($hc->getId());
		
				
		$date = new \DateTime();
		
		$html = $this->renderView('HcBundle:HistoriaClinica:imprimir.pdf.twig', array(
				'entity' => $hc,
				'factura' => $factura,
				'paciente' => $paciente,
				'cliente'	=> $cliente,
				'sede'=>$sede,
				'medicamentos' => $medicamento,
				'perHcMe' => $hcMe,
				'exa_presentado' => $exa_presentado,
				'exaPrePrimerVez' => $exaPresenPrimerVez,
				'exa_solicitado' => $exa_solicitado,
				'cies' => $cie,
				'perHcCie' => $hcCie
		));
		
		$this->get('io_tcpdf')->dir = $sede->getDireccion();
		$this->get('io_tcpdf')->ciudad = $sede->getCiudad();
		$this->get('io_tcpdf')->tel = $sede->getTelefono();
		$this->get('io_tcpdf')->mov = $sede->getMovil();
		$this->get('io_tcpdf')->mail = $sede->getEmail();
		$this->get('io_tcpdf')->sede = $sede->getnombre();
		$this->get('io_tcpdf')->empresa = $sede->getEmpresa()->getNombre();
    	
    	return $this->get('io_tcpdf')->quick_pdf($html, 'informe.pdf', 'I');
	}
}
