<?php

namespace dlaser\HcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\Medicamento;
use dlaser\HcBundle\Form\MedicamentoType;
use dlaser\HcBundle\Form\MedicamentoSearchType;
use dlaser\UsuarioBundle\Entity\Usuario;

class MedicamentoController extends Controller
{
	public function listAction()
	{
		$em = $this->getDoctrine()->getEntityManager();

		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);		
		$form   = $this->createForm(new MedicamentoSearchType());		
		$user = $this->get('security.context')->getToken()->getUser();

		$medicamento = $paginador->paginate($em->getRepository('HcBundle:Medicamento')->findBy(array('usuario' =>$user->getId()), array('principioActivo' => 'ASC')))->getResult();	
				
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));
		$breadcrumbs->addItem("Listar");	
		
		if(!$medicamento)
		{
			$this->get('session')->setFlash('info','No tienes medicamentos asignados.');
		}
		
		return $this->render('HcBundle:Medicamento:list.html.twig', array(
				'medicamentos' => $medicamento,
				'usuario' => $user,
				'form'   => $form->createView()
		));
	}
	
	public function searchAction()
	{
		$request = $this->getRequest();
		$form   = $this->createForm(new MedicamentoSearchType());
		$form->bindRequest($request);
			
		$user = $this->get('security.context')->getToken()->getUser();
		$medicamento=null;
		$id = $user->getId();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));
		$breadcrumbs->addItem("Listar");
		
		if($form->isValid())
		{
			$nombre = $form->get('nombre')->getData();							
			$em = $this->getDoctrine()->getEntityManager();
											
			$dql = $em->createQuery("SELECT m FROM HcBundle:Medicamento m JOIN m.usuario u
					WHERE u.id = :id AND m.principioActivo LIKE :nombre ");			
	
			$dql->setParameter('id', $id);			
			$dql->setParameter('nombre', $nombre.'%');
			$medicamento = $dql->getResult();
		
			if(!$medicamento){
				return $this->redirect($this->generateUrl('medicamento_list'));
			}
		}		
		
		return $this->render('HcBundle:Medicamento:list.html.twig', array(
					'medicamentos' => $medicamento,
					'usuario' => $id,
					'form'   => $form->createView()
		));	
	}
	
	
	public function newAction()
	{
		$entity = new Medicamento();		
		$form   = $this->createForm(new MedicamentoType(), $entity);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));
		$breadcrumbs->addItem("Nuevo");
	
		return $this->render('HcBundle:Medicamento:new.html.twig', array(
				'entity' => $entity,				
				'form'   => $form->createView()
		));
	}
	
	public function saveAction()
	{
		$entity  = new Medicamento();
		$request = $this->getRequest();
		$form    = $this->createForm(new MedicamentoType(), $entity);
		$form->bindRequest($request);
			
		if ($form->isValid()) {
				
			$em = $this->getDoctrine()->getEntityManager();
			$user = $this->get('security.context')->getToken()->getUser();
			$id = $user->getId();
			$usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
	
			if(!$usuario)
			{
				throw $this->createNotFoundException('Usuario solicitada no existe');
			}
	
			$entity->setUsuario($usuario);
			$em->persist($entity);
			$em->flush();
	
			$this->get('session')->setFlash('ok','El medicamento se ha registrado correctamente ');				
			return $this->redirect($this->generateUrl('medicamento_list',array('id'=>$id)));
			
		}else{
			$breadcrumbs = $this->get("white_october_breadcrumbs");
			$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
			$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));
			$breadcrumbs->addItem("Nuevo");
			
			return $this->render('HcBundle:Medicamento:new.html.twig', array(
					'entity' => $entity,					
					'form'   => $form->createView()
			));
		}
	
	}
	
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$editMe = $em->getRepository('HcBundle:Medicamento')->find($id);
	
		if(!$editMe)
		{
			throw $this->createNotFoundException('El medicamento no existe');
		}
	
		$editform   = $this->createForm(new MedicamentoType(), $editMe);
		$usuario = $editMe->getUsuario();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));		
		$breadcrumbs->addItem("Modificar");
		
	
		return $this->render('HcBundle:Medicamento:edit.html.twig', array(
				'entity' => $editMe,
				'usuario' => $usuario,
				'edit_form'   => $editform->createView()
		));
	}
	
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$update = $em->getRepository('HcBundle:Medicamento')->find($id);
		if(!$update)
		{
			throw $this->createNotFoundException('El medicamento no existe.');
		}
			
		$upForm = $this->createForm(new MedicamentoType(), $update);
		$request = $this->getRequest();
		$upForm->bindRequest($request);
			
		if ($upForm->isValid())
		{
			$em->persist($update);
			$em->flush();
			$this->get('session')->setFlash('ok', 'El medicamento ha sido modificado éxitosamente.');
			return $this->redirect($this->generateUrl('medicamento_edit', array('id' => $id)));
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Medicamento", $this->get("router")->generate("medicamento_list"));
		$breadcrumbs->addItem("Modificar");
			
		return $this->render('HcBundle:Medicamento:edit.html.twig', array(
				'entity'      => $update,
				'edit_form'   => $upForm->createView(),
		));
	}
	
	public function ajaxUserMxAction()
	{
		$request = $this->get('request');
		
		$principio = $request->request->get('principio');
		$concentracion = $request->request->get('concentracion');
		$presentacion = $request->request->get('presentacion');
		$dosis = $request->request->get('dosis');
		$tiempo = $request->request->get('tiempo');
		$dias = $request->request->get('dias');
		$pos = $request->request->get('pos');
		$invima = $request->request->get('invima');
		$justificacion = $request->request->get('justificacion');
		$efecto = $request->request->get('efecto');
	
		if(trim($principio) && trim($concentracion) && trim($presentacion) && trim($dosis)){
				
			$em = $this->getDoctrine()->getEntityManager();
				
			$medicamento = new Medicamento();
			$usuario = $this->get('security.context')->getToken()->getUser();
	
			if(!$usuario)
			{
				$response=array("responseCode"=>400, "msg"=>"La información del examen o del usuario no esta disponible.");
			}else {
				
				$medicamento->setPrincipioActivo($principio);
				$medicamento->setConcentracion($concentracion);
				$medicamento->setPresentacion($presentacion);
				$medicamento->setDosisDia($dosis);
				$medicamento->setTiempo($tiempo);
				$medicamento->setDiasTratamiento($dias);
				$medicamento->setPos($pos);
				$medicamento->setInvima($invima);
				$medicamento->setJustificacion($justificacion);
				$medicamento->setEfectos($efecto);
				$medicamento->setUsuario($usuario);
				
				$em->persist($medicamento);
				$em->flush();
					
				$response=array("responseCode"=>200, "msg"=>"Medicamento agregado éxitosamente.");
					
				$response['id'] = $medicamento->getId();
				$response['principio'] = $medicamento->getPrincipioActivo();
				$response['concentracion'] = $medicamento->getConcentracion();
				$response['presentacion'] = $medicamento->getPresentacion();
				$response['dosis'] = $medicamento->getDosisDia();
				$response['tiempo'] = $medicamento->getTiempo();
				$response['dias'] = $medicamento->getDiasTratamiento();
			}
		}else {
			$response=array("responseCode"=>400, "msg"=>"El medicamento ingresado no es valido.");
		}
	
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
}
	