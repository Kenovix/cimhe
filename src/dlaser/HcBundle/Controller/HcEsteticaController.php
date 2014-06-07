<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\HcEstetica;
use dlaser\ParametrizarBundle\Entity\Factura;

use dlaser\HcBundle\Form\HcEsteticaType;

class HcEsteticaController extends Controller{
	
	function newAction($hc){
		
		$em = $this->getDoctrine()->getEntityManager();
		$hc = $em->getRepository('HcBundle:Hc')->find($hc);
		
		$estetica = $hc->getHcEstetica();
		
		if($hc and !$estetica){
			
			$factura = $hc->getFactura();
			
			$HcEstetica = new HcEstetica();
			$HcEstetica->setFecha(new \DateTime('now'));
			$HcEstetica->setEdadCrono($factura->getPaciente()->getEdad());
			
			$form = $this->createForm(new HcEsteticaType(), $HcEstetica);				
			
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_edit",array('id'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("HCEstetica Nueva");
			
			
			
			return $this->render("HcBundle:HcEstetica:new.html.twig", array(
					'entity' => $HcEstetica,
					'factura' => $factura,
					'hc' => $hc,
					'form'   => $form->createView()
			));			
		}else{
			$this->get('session')->setFlash('error', 'La historia clinica no existe o la hc-estetica ya se encuentra creada.'.
					'Porfavor consulte el usuario con su respectiva identificasion.');
			
			return $this->redirect($this->generateUrl('hc_search'));
		}
		
		
	}
	
	public function saveAction($hc)
	{
		$HcEstetica = new HcEstetica();		
		$request = $this->getRequest();
		$form   = $this->createForm(new HcEsteticaType(), $HcEstetica);				
		$form->bindRequest($request);		
	
		if ($form->isValid()) {
			
			$HcEstetica->serialize();
			
			$em = $this->getDoctrine()->getEntityManager();
			$hc = $em->getRepository('HcBundle:Hc')->find($hc);
			$factura = $hc->getFactura();
			
			if($hc){
				
				$HcEstetica->setHc($hc);				
				$em->persist($HcEstetica);
				$em->flush();
				
				$this->get('session')->setFlash('ok', 'La historia de estetica ha sido creado éxitosamente.');
				return $this->redirect($this->generateUrl('HcEstetica_edit',array('hc'=>$hc->getId())));				
			}else{
				$this->get('session')->setFlash('error', 'La historia clinica no existe.');
				return $this->redirect($this->generateUrl('hc_search'));
			}	
		}else{
			$em = $this->getDoctrine()->getEntityManager();
			$hc = $em->getRepository('HcBundle:Hc')->find($hc);
			$factura = $hc->getFactura();
			
			$this->get('session')->setFlash('error', 'Los campos de historia de estetica no son correctos.');
			return $this->render("HcBundle:HcEstetica:new.html.twig", array(
					'entity' => $HcEstetica,
					'factura' => $factura,
					'hc' => $hc,
					'form'   => $form->createView()
			));				
		}	
	}
	
	public function editAction($hc)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->findOneBy(array('hc' => $hc));		
		$hc = $em->getRepository('HcBundle:Hc')->find($hc);
				
		if($hcEstetica)
		{
			$serialize = array(
					'op' => $hcEstetica->getOp(),
					'pigmentacion' => $hcEstetica->getPigmentacion(),
					'arrugas' => $hcEstetica->getArrugas(),
					'flacidez' => $hcEstetica->getFlacidez(),
					'parpado' => $hcEstetica->getParpado(),
					'lesiones_cut' => $hcEstetica->getLesionesCut(),					
					'lipodistrofia' => $hcEstetica->getLipodistrofia(),
					'tatuaje' => $hcEstetica->getTatuaje(),
					'cicatrizes' => $hcEstetica->getCicatrizes(),
					'estrias' => $hcEstetica->getEstrias(),
			);
						
			$hcEstetica->unserialize($serialize);
						
			$editform   = $this->createForm(new HcEsteticaType(), $hcEstetica);		
			
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));			
			$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_edit",array('id'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("HCEstetica Modificar");
			
			$ruta = $this->container->getParameter('dlaser.directorio.imagenes');
			$ruta .= 'grafico_'.$hc->getId().'.png';
			
			if( file_exists ($ruta)){								
			}else{
				$ruta = $this->container->getParameter('dlaser.imagen.grafico');
				$ruta .= 'biotipo.jpg';
			}
			$factura = $hc->getFactura();
			
			$grafico = 'data:image/png;base64,';			
			$grafico .= base64_encode (file_get_contents($ruta));

			return $this->render('HcBundle:HcEstetica:edit.html.twig', array(
					'entity' => $hcEstetica,
					'hc' => $hc,
					'factura' => $factura,
					'grafico' => $grafico,
					'form'   => $editform->createView()
			));			
			
		}else{
				$this->get('session')->setFlash('error', 'La historia clinica no existe.');
				return $this->redirect($this->generateUrl('hc_search'));
		}	
	}
	
	public function updateAction($estetica)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$HcEstetica = $em->getRepository('HcBundle:HcEstetica')->find($estetica);
					
		if($HcEstetica)			
		{		
			$serialize = array(
					'op' => $HcEstetica->getOp(),
					'pigmentacion' => $HcEstetica->getPigmentacion(),
					'arrugas' => $HcEstetica->getArrugas(),
					'flacidez' => $HcEstetica->getFlacidez(),
					'parpado' => $HcEstetica->getParpado(),
					'lesiones_cut' => $HcEstetica->getLesionesCut(),
					'lipodistrofia' => $HcEstetica->getLipodistrofia(),
					'tatuaje' => $HcEstetica->getTatuaje(),
					'cicatrizes' => $HcEstetica->getCicatrizes(),
					'estrias' => $HcEstetica->getEstrias(),
					);			
			$HcEstetica->unserialize($serialize);			
			$request = $this->getRequest();
			$form   = $this->createForm(new HcEsteticaType(), $HcEstetica);			
			$form->bindRequest($request);
						
			$hc = $HcEstetica->getHc();		
			
			if ($form->isValid())
			{
				$HcEstetica->serialize();			
				$em->persist($HcEstetica);
				$em->flush();
				
				$this->get('session')->setFlash('info', 'La historia de estetica ha sido modificada éxitosamente.');
				return $this->redirect($this->generateUrl('HcEstetica_edit',array('hc' => $hc->getId())));				
				
			}else{
				$this->get('session')->setFlash('error', 'La informacion de historia estetica no es validad.');
				$breadcrumbs = $this->get("white_october_breadcrumbs");
				$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
				$breadcrumbs->addItem("Historia Estetica", $this->get("router")->generate("HcEstetica_new",array('hc'=>$hc->getId())));
				$breadcrumbs->addItem("Modificar");
				
				$ruta = $this->container->getParameter('dlaser.directorio.imagenes');
				$ruta .= 'grafico_'.$hc->getId().'.png';
				if( file_exists ($ruta)){
				}else{
					$ruta = $this->container->getParameter('dlaser.imagen.grafico');
					$ruta .= 'biotipo.jpg';
				}					
				$grafico = 'data:image/png;base64,';					
				$grafico .= base64_encode (file_get_contents($ruta));
				
				return $this->render('HcBundle:HcEstetica:edit.html.twig', array(
						'entity' => $HcEstetica,
						'hc' => $hc,
						'grafico' => $grafico,
						'form'   => $form->createView()
				));
			}
		}else{
				$this->get('session')->setFlash('error', 'La historia clinica no existe.');
				return $this->redirect($this->generateUrl('hc_search'));
		}
	}
	
	public function impresoAction($hcEstetica){
		
		$em = $this->getDoctrine()->getEntityManager();
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->find($hcEstetica);
		$hc = $em->getRepository('HcBundle:Hc')->find($hcEstetica->getHc()->getId());
		
		if($hcEstetica)
		{
			$serialize = array(
					'op' => $hcEstetica->getOp(),
					'pigmentacion' => $hcEstetica->getPigmentacion(),
					'arrugas' => $hcEstetica->getArrugas(),
					'flacidez' => $hcEstetica->getFlacidez(),
					'parpado' => $hcEstetica->getParpado(),
					'lesiones_cut' => $hcEstetica->getLesionesCut(),
					'lipodistrofia' => $hcEstetica->getLipodistrofia(),
					'tatuaje' => $hcEstetica->getTatuaje(),
					'cicatrizes' => $hcEstetica->getCicatrizes(),
					'estrias' => $hcEstetica->getEstrias(),
			);
		
			$hcEstetica->unserialize($serialize);
			
			

			$cliente = $hc->getFactura()->getCliente();
			$sede = $hc->getFactura()->getSede();
			$paciente = $hc->getFactura()->getPaciente();
			$user = $this->get('security.context')->getToken()->getUser();
				
			$html = $this->renderView('HcBundle:Impresos:hcEsteticaImpreso.pdf.twig', array(
					'entity' => $hcEstetica,
					'paciente' => $paciente,
					'cliente' => $cliente,					
					'hc' => $hc,					
			));
			
			
			
			$this->get('io_tcpdf')->dir = $sede->getDireccion();
			$this->get('io_tcpdf')->ciudad = $sede->getCiudad();
			$this->get('io_tcpdf')->tel = $sede->getTelefono();
			$this->get('io_tcpdf')->mov = $sede->getMovil();
			$this->get('io_tcpdf')->mail = $sede->getEmail();
			$this->get('io_tcpdf')->sede = $sede->getnombre();
			$this->get('io_tcpdf')->empresa = $sede->getEmpresa()->getNombre();
				
			return $this->get('io_tcpdf')->quick_pdf($html, 'informe.pdf', 'I');
				
		}else{
			$this->get('session')->setFlash('error', 'La historia clinica no existe.');
			return $this->redirect($this->generateUrl('hc_search'));
		}
		
	}
}