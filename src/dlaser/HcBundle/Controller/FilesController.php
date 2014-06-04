<?php
namespace dlaser\HcBundle\Controller;

use dlaser\HcBundle\Entity\HcEstetica;
use dlaser\HcBundle\Form\FileType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use dlaser\HcBundle\Entity\Files;

class FilesController extends Controller{
	
	public function newAction($estetica)
	{
		$file = new Files();		 
		$em = $this->getDoctrine()->getEntityManager();
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->find($estetica);		
		$hc = $hcEstetica->getHc();
		if($hcEstetica and $hc){
			$form   = $this->createForm(new FileType(), $file);
					
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_edit",array('id'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("Historia Estetica", $this->get("router")->generate("HcEstetica_edit",array('hc'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("Subir imagen");
			
			return $this->render('HcBundle:Files:new.html.twig', array(
					'estetica' => $hcEstetica,
					'hc' => $hc,
					'form'   => $form->createView()
			));
		}else{
			$this->get('session')->setFlash('error', 'La historia de estetica no existe.');
			return $this->redirect($this->generateUrl('hc_search'));
		}	
	}
	
	function uploadAction($estetica){		
		$file = new Files();		
		$em = $this->getDoctrine()->getEntityManager();		
		$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->find($estetica);
		
		if($hcEstetica){
			
			$request = $this->getRequest();
			$form   = $this->createForm(new FileType(), $file);
			$form->bindRequest($request);
			
			if ($form->isValid()) {
				
				$file->setHcEstetica($hcEstetica);	
				$file->saveDataFile($this->container->getParameter('dlaser.directorio.imagenes'));
				$em->persist($file);
				$em->flush();
				
				$this->get('session')->setFlash('ok', 'La información ha sido guardada éxitosamente.');				
				return $this->redirect($this->generateUrl('file_edit',array('file'=>$file->getId())));
			}else{
			
				return $this->render('HcBundle:Files:new.html.twig', array(
					'estetica' => $hcEstetica,
					'hc' => $hc,
					'form'   => $form->createView()
				));		
			}			
		}else{
			$this->get('session')->setFlash('error', 'La historia de estetica no existe.');
			return $this->redirect($this->generateUrl('hc_search'));
		}				
	}
	
	function editAction($file){
				
		$em = $this->getDoctrine()->getEntityManager();
		$files = $em->getRepository('HcBundle:Files')->find($file);
		$hcEstetica = $files->getHcEstetica();
		
		if($files and $hcEstetica){			
			$form   = $this->createForm(new FileType(), $files);
				
			$hc = $hcEstetica->getHc();
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Historia Clinica", $this->get("router")->generate("hc_edit",array('id'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("Historia Estetica", $this->get("router")->generate("HcEstetica_edit",array('hc'=>$hc->getFactura()->getId())));
			$breadcrumbs->addItem("Subir imagen");
			
			return $this->render('HcBundle:Files:edit.html.twig', array(	
					'file' => $files,
					'hc' => $hc,					
					'form'   => $form->createView()
			));
		}else{
			throw $this->createNotFoundException('El archivo no existe');
		}
	}
	
	function updateAction($file){
		$em = $this->getDoctrine()->getEntityManager();
		$archivo = $em->getRepository('HcBundle:Files')->find($file);
		
		if($archivo){
				
			$hcEstetica = $archivo->getHcEstetica();
			if(!$hcEstetica){
				throw $this->createNotFoundException('El hc_estetica no existe');
			}
			
			$request = $this->getRequest();
			$form   = $this->createForm(new FileType(), $archivo);
			$imagen = $form->getData()->getImg();			
			$form->bindRequest($request);
				
			
			if($form->isValid()) {
				
				
				if(null == $archivo->getImg()){
					$archivo->setImg($imagen);	
				}else{
					$folder = $this->container->getParameter('dlaser.directorio.imagenes');
					$archivo->saveDataFile($folder);
					if($imagen){
					unlink($folder.$imagen);
					}
				}
				
				$archivo->setHcEstetica($hcEstetica);				
				$em->persist($archivo);
				$em->flush();
		
				$this->get('session')->setFlash('ok', 'La información ha sido guardada éxitosamente.');
				return $this->redirect($this->generateUrl('file_edit',array('file'=>$archivo->getId())));
			}else{
				return $this->render('HcBundle:Files:edit.html.twig', array(	
					'file' => $archivo,				
					'form'   => $form->createView()
			));
			}
		}else{
			throw $this->createNotFoundException('El archivo no existe');
		}
	}
	
	function showImagensAction($estetica,$estado){

		if($estado == 'A' || $estado=='D'){
			$em = $this->getDoctrine()->getEntityManager();
			$hcEstetica = $em->getRepository('HcBundle:HcEstetica')->find($estetica);
			
			if($hcEstetica){
					
				$dql = $em->createQuery('SELECT f FROM HcBundle:Files f WHERE f.hcEstetica = :id AND f.estado = :estado');
				$dql->setParameter('id', $hcEstetica->getId());
				$dql->setParameter('estado', $estado);
				$archivo = $dql->getResult();
					
					
				if($archivo){
					$paciente = $hcEstetica->getHc()->getFactura()->getPaciente();
					return $this->render('HcBundle:Files:showImagens.html.twig', array(
							'file' => $archivo,
							'paciente' => $paciente,
							'estado' => $estado,
							'estetica' => $hcEstetica,
					));
				}else{
					$this->get('session')->setFlash('error', 'No hay imagenes cargadas. Por favor cargue las imagenes necesarias.');
					return $this->redirect($this->generateUrl('file_new',array('estetica'=>$hcEstetica->getId())));
				}
			}else{
				throw $this->createNotFoundException('La historia de estetica no existe.');
			}
		}else{
			throw $this->createNotFoundException('La historia de estetica no existe.');
		}		
	}
	
	/*
	 * upImagenAction
	 * 
	 * Racibe la imagen via ajax POST en base64
	 */
	
	function upImagenAction(){
		
		$request = $this->get('request');
		
		$estetica=$request->request->get('id');
		$img=$request->request->get('img');
				
		$em = $this->getDoctrine()->getEntityManager();
		$hcEstetica = $em->getRepository('HcBundle:Hc')->find($estetica);
		
				
		if($hcEstetica){		
			$ruta = $this->container->getParameter('dlaser.directorio.imagenes');
			
			if ($img) {
				
				$imgData = base64_decode(substr($img,22));
				
				$file = $ruta.'grafico_'.$estetica.'.png';
				
				if (file_exists($file)) { unlink($file); }
				$fp = fopen($file, 'w');
				fwrite($fp, $imgData);
				fclose($fp);
				
				$response=array("responseCode"=>200, "msg"=>"La operación ha sido exitosa.");
			}
		}else{
			$response=array("responseCode"=>400, "msg"=>"Ha ocurrido un error al crear la imagen.");
		}
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
}