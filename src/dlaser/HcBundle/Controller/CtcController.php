<?php
namespace dlaser\HcBundle\Controller;

use dlaser\HcBundle\Entity\Medicamento;

use dlaser\HcBundle\Form\MedicamentoType;

use dlaser\HcBundle\Form\CtcType;
use dlaser\HcBundle\Form\searchType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\Ctc;

class CtcController extends Controller
{
	
	public function noPosAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		
		$dql = $em->createQuery('SELECT hm.estado,hm.id,m.principioActivo,m.concentracion,m.dosisDia,m.tiempo,m.pos
				FROM HcBundle:HcMedicamento hm JOIN hm.medicamento m
				WHERE hm.hc = :id');
		$dql->setParameter('id', $id);
		$hcMe = $dql->getResult();
		
		if(!$hcMe)
		{
			throw $this->createNotFoundException('informacion solicitada no existe');
		}
		
		return $this->render('HcBundle:Ctc:noPos.html.twig', array('perHcMe' => $hcMe));
	}
	
	public function listAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($id);
		
		if(!$factura)
		{
			throw $this->createNotFoundException('informacion solicitada no existe');
		}

		$dql = $em->createQuery("SELECT
										ctc
									FROM
										HcBundle:Ctc ctc
									JOIN
										ctc.hc hc
									JOIN
										hc.factura f
									JOIN
										f.paciente p
									WHERE
										p.identificacion = :id AND
										p.tipoId = :tipoid");
			
		$dql->setParameter('id', $factura->getPaciente()->getIdentificacion());
		$dql->setParameter('tipoid', $factura->getPaciente()->getTipoId());
			
		$ctc = $dql->getResult();
		
		if(!$ctc){
			$this->get('session')->setFlash('error','No hay CTCs para el paciente consultado. ');
			return $this->redirect($this->generateUrl('hc_edit',array('id' => $factura->getId())));
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Listar");
		
		return $this->render('HcBundle:Ctc:list.html.twig', array(
				'entity' => $ctc
		));
	}
	
	
	public function searchAction()
	{
		$form   = $this->createForm(new searchType());
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Listar");
		
		return $this->render('HcBundle:Ctc:search.html.twig', array(
				'form'   => $form->createView()
		));
	}
	
	public function busquedaAction()
	{
		$request = $this->getRequest();
	
		$form   = $this->createForm(new searchType());
		$form->bindRequest($request);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Listar");
	
		if($form->isValid())
		{
			$identificacion = $form->get('paciente')->getData();
			$tipoid = $form->get('tipoid')->getData();
				
			$em = $this->getDoctrine()->getEntityManager();
				
			$dql = $em->createQuery("SELECT 
										ctc
									FROM 
										HcBundle:Ctc ctc 
									JOIN 
										ctc.hc hc
									JOIN
										hc.factura f
									JOIN
										f.paciente p
									WHERE 
										p.identificacion = :id AND
										p.tipoId = :tipoid");
			
			$dql->setParameter('id', $identificacion);
			$dql->setParameter('tipoid', $tipoid);
			
			$ctc = $dql->getResult();
			
			if(!$ctc){
				throw $this->createNotFoundException('No hay CTCs para el paciente consultado.');
			}
			
	
			return $this->render('HcBundle:Ctc:list.html.twig', array(
					'entity' => $ctc
			));
			
		}else{				
			return $this->render('HcBundle:Ctc:search.html.twig', array(
					'form'   => $form->createView()
			));
		}
	}
	
	/* este metodo se ejecuta justo despues de realizar el proceso de la HC
	 * teniendo en cuenta si el pos del medicamento se cuencuentra en estado 0 
	 * que se entiende como no activo
	*/
	public function newAction($id)
	{	
		$em = $this->getDoctrine()->getEntityManager();
		$hcMedicamento = $em->getRepository('HcBundle:HcMedicamento')->find($id);
				
		if(!$hcMedicamento)
		{
			throw $this->createNotFoundException('información solicitada no existe');
		}

		$hc = $hcMedicamento->getHc();
		$md = $hcMedicamento->getMedicamento();
				
		$factura = $hc->getFactura();
		$hmId = $hc->getId();
		
		$ctc = $em->getRepository('HcBundle:Ctc')->findOneBy(array('hc' => $hmId, 'medicamento' => $md->getId()));
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Nuevo");
		
		
		if(!$ctc){
				
			$entity = new Ctc();
			$entity->setFecha(new \DateTime('now'));
			$entity->setResumenHc($hc->getEnfermedad()." ".$hc->getManejo());
			$entity->setNoposEfectos($md->getEfectos());
			$entity->setNoposNota($md->getJustificacion());
			$form   = $this->createForm(new CtcType(array('cie' => $hmId)), $entity);
		
			return $this->render('HcBundle:Ctc:new.html.twig', array(
				'entity' => $entity,
				'hm' => $hcMedicamento,
				'factura' => $factura,
				'hc' => $hc,
				'md' => $md,
				'form'   => $form->createView()
			));
		}else{			
			$this->get('session')->setFlash('info','El CTC ya ha sido creado anteriormente.');				
			return $this->redirect($this->generateUrl('ctc_edit',array('id' => $ctc->getId())));
		}
	}
	
	public function saveAction($hm)
	{
		$request = $this->getRequest();
		$em = $this->getDoctrine()->getEntityManager();
		$hcMedicamento = $em->getRepository('HcBundle:HcMedicamento')->find($hm);
		
		if(!$hcMedicamento)
		{
			throw $this->createNotFoundException('informacion solicitada no existe');
		}
		
		$entity  = new Ctc();
		$hmId = $hcMedicamento->getHc()->getId();
		$form    = $this->createForm(new CtcType(array('cie' => $hmId)), $entity);

		$form->bindRequest($request);
		$factura = $hcMedicamento->getHc()->getFactura();
				
		if ($form->isValid()) {
			
			$hc = $hcMedicamento->getHc();
			$md = $hcMedicamento->getMedicamento();
			
			$information = $request->get($form->getName());
			$cie = $em->getRepository('HcBundle:Cie')->find($information['cie']);
								
			if(!$hc || !$md || ! $cie)
			{
				throw $this->createNotFoundException('Información solicitada no existe');
			}

			$entity->setCie($cie);
			$entity->setHc($hc);
			$entity->setMedicamento($md);
			$entity->setFecha(new \DateTime('now'));
			$em->persist($entity);
			$em->flush();
			
			$this->get('session')->setFlash('info','La Información se ha registrado correctamente');				
			return $this->redirect($this->generateUrl('ctc_edit',array('id' => $entity->getId())));
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Nuevo");
		
		return $this->render('HcBundle:Ctc:new.html.twig', array(
				'entity' => $entity,
				'hm' => $hcMedicamento,
				'factura' => $factura,
				'hc' => $hc,
				'md' => $md,
				'form'   => $form->createView()
		));
	}
	
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();		
		$entity = $em->getRepository('HcBundle:Ctc')->find($id);
			  	
		if(!$entity)
		{
			throw $this->createNotFoundException('La Información solicitada no existe');
		}
		
		$factura = $entity->getHc()->getFactura();
		$hc = $entity->getHc()->getId();

		$editform = $this->createForm(new CtcType(array('cie' => $hc)), $entity);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Detalles");
		
		return $this->render('HcBundle:Ctc:edit.html.twig', array(
				'entity' => $entity,
				'factura' => $factura,
				'edit_form'   => $editform->createView()
		));
	}
	
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:Ctc')->find($id);
						
		if(!$entity)
		{
			throw $this->createNotFoundException('La Información no existe.');
		}
		$factura = $entity->getHc()->getFactura();
		$hc = $entity->getHc()->getId();
			
		$editform = $this->createForm(new CtcType(array('cie' => $hc)), $entity);
		$request = $this->getRequest();
		$editform->bindRequest($request);
			
		if ($editform->isValid())
		{
			$information = $request->get($editform->getName());
			$cie = $em->getRepository('HcBundle:Cie')->find($information['cie']);

			$entity->setCie($cie);
			$em->persist($entity);
			$em->flush();
			$this->get('session')->setFlash('ok', 'La Información ha sido modificada éxitosamente.');
			return $this->redirect($this->generateUrl('ctc_edit', array('id' => $id)));
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("CTC", $this->get("router")->generate("ctc_search"));
		$breadcrumbs->addItem("Detalles");
			
		return $this->render('HcBundle:Ctc:edit.html.twig', array(
				'entity' => $entity,	
				'factura' => $factura,
				'edit_form'   => $editform->createView()
		));
	}
	
	
	public function imprimirAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:Ctc')->find($id);
						
		if(!$entity)
		{
			throw $this->createNotFoundException('La Información no existe.');
		}
		
		$paciente = $entity->getHc()->getFactura()->getPaciente();
		$cliente = $entity->getHc()->getFactura()->getCliente();
		$sede = $entity->getHc()->getFactura()->getSede();

		$html = $this->renderView('HcBundle:Ctc:imprimir.pdf.twig',
				array(	'entity' 	=> $entity,
						'paciente' => $paciente,
						'cliente' => $cliente,
						'sede' => $sede
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
