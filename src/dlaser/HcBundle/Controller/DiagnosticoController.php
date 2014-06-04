<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\Cie;
use dlaser\UsuarioBundle\Entity\Usuario;
use dlaser\HcBundle\Form\DiagnosticoType;
use dlaser\HcBundle\Form\DxSearchType;

class DiagnosticoController extends Controller
{
	/* El admin es el encargado de crear los diagnosticos
	 * modificar editar eliminar, el usuario solo puede asignarce asi mismo 
	 * los diagnosticos.
	 */
	public function listDxAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);
		$form   = $this->createForm(new DxSearchType());
						
		$Cie = $paginador->paginate($em->getRepository('HcBundle:Cie')->findAll())->getResult();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Diagnostico",$this->get("router")->generate("dx_list"));
		$breadcrumbs->addItem("Listar");		
					
		return $this->render('HcBundle:Diagnostico:listDx.html.twig', array(
				'entities' => $Cie,		
				'form'   => $form->createView()
		));
	}
	
	public function searchDxAction()
	{
		$request = $this->getRequest();
		$form   = $this->createForm(new DxSearchType());
		$form->bindRequest($request);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Diagnostico",$this->get("router")->generate("dx_list"));
		$breadcrumbs->addItem("Listar");
			
		if($form->isValid())
		{
			// de acuerdo a la opcion ingresada por el usuario se establece la consulta a la base de datos
			
			$nombre = $form->get('nombre')->getData();
			$option = $form->get('option')->getData();
									
			$em = $this->getDoctrine()->getEntityManager();
			
			if($option == 'nombre')
			{
				$dql = $em->createQuery("SELECT c FROM HcBundle:Cie c
						WHERE c.nombre LIKE :nombre ");
				
				$dql->setParameter('nombre', $nombre.'%');
				$respusta = $dql->getResult();
				
			}else if($option == 'codigo')
			{								
				$dql = $em->createQuery("SELECT c FROM HcBundle:Cie c
						WHERE c.codigo LIKE :codigo ");
				
				$dql->setParameter('codigo', $nombre.'%');
				$respusta = $dql->getResult();
			}
		
			if(!$respusta)
			{
				return $this->redirect($this->generateUrl('dx_list'));
			}				
			return $this->render('HcBundle:Diagnostico:listDx.html.twig', array(					
					'entities' => $respusta,					
					'form'   => $form->createView()
			));
	
		}		
		
		return $this->render('HcBundle:Diagnostico:listDx.html.twig', array(				
				'entities' => null,				
				'form'   => $form->createView()
		));
	}
	
	public function newDxAction()
	{
		$entity = new Cie();		
		$form   = $this->createForm(new DiagnosticoType(), $entity);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Diagnostico",$this->get("router")->generate("dx_list"));		
		$breadcrumbs->addItem("Nuevo");
		
		return $this->render('HcBundle:Diagnostico:newDx.html.twig', array(
				'entity' => $entity,				
				'form'   => $form->createView()
		));		
	}
	
	public function saveDxAction()
	{
		$entity  = new Cie();
		$request = $this->getRequest();
		$form    = $this->createForm(new DiagnosticoType(), $entity);
		$form->bindRequest($request);
			
		if ($form->isValid()) {
				
			$em = $this->getDoctrine()->getEntityManager();
			$codigo = $form->get('codigo')->getData();
			$cie = $em->getRepository('HcBundle:Cie')->findByCodigo($codigo);
		
			if(!$cie)
			{				
				$em->persist($entity);
				$em->flush();
				
				$this->get('session')->setFlash('ok','El diagnostico se ha registrado correctamente ');				
				return $this->redirect($this->generateUrl('dx_new'));
			}

			throw $this->createNotFoundException('El diganostico ya se encuentra creado.');			
		}	
		else{
					
			return $this->render('HcBundle:Diagnostico:newDx.html.twig', array(
				'entity' => $entity,							
				'form'   => $form->createView()
		));	
		}	
	}
	
	public function editDxAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$editCie = $em->getRepository('HcBundle:Cie')->find($id);
		
		if(!$editCie)
		{
			throw $this->createNotFoundException('La historia clinica no existe');
		}
		
		$editform   = $this->createForm(new DiagnosticoType(), $editCie);
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Diagnostico",$this->get("router")->generate("dx_list"));		
		$breadcrumbs->addItem("Modificar");
		
		return $this->render('HcBundle:Diagnostico:editDx.html.twig', array(
				'entity' => $editCie,
				'cie' => $id,
				'edit_form'   => $editform->createView()
		));
	
	}
	
	public function updateDxAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$update = $em->getRepository('HcBundle:Cie')->find($id);
		
		if(!$update)
		{
			throw $this->createNotFoundException('La historia clinica no existe.');
		}
			
		$upForm = $this->createForm(new DiagnosticoType(), $update);
		$request = $this->getRequest();
		$upForm->bindRequest($request);
			
		if ($upForm->isValid())
		{
			$em->persist($update);
			$em->flush();
			$this->get('session')->setFlash('ok', 'El diagnostico ah sido modificada éxitosamente.');
			return $this->redirect($this->generateUrl('dx_edit', array('id' => $id)));
		}
			
		return $this->render('HcBundle:Diagnostico:editDx.html.twig', array(
				'entity'      => $update,
				'edit_form'   => $upForm->createView(),
		));	
	}
	
	public function userDxAction()
	{
		$em = $this->getDoctrine()->getEntityManager();		
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);
		
		$user = $this->get('security.context')->getToken()->getUser();
		$id = $user->getId();
		$usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
			
		if (!$usuario){
			throw $this->createNotFoundException('El usuario solicitado no existe');
		}

		/* se realiza la consulta a los diagnosticos para que genere los diagnosticos que el usuario tiene
		*  relacionados, tambien se puede consultar de la siguiente forma $permisos = $usuario->getCie(); 
		*  pero no se puede ordenar ASC por eso se realiza la siguiente consulta.
		*/
		$dql = $em->createQuery('SELECT c FROM HcBundle:Cie c JOIN c.usuario u
					WHERE u.id = :id ORDER BY c.nombre ASC');
		
		$dql->setParameter('id', $usuario->getId());		
		$permisos = $paginador->paginate($dql->getResult())->getResult();		
		
			
		if($permisos){	
						
			$dql = $em->createQuery('SELECT c FROM HcBundle:Cie c
					WHERE c.id NOT IN (SELECT C FROM HcBundle:Cie C JOIN C.usuario u JOIN u.sede s WHERE u.id = :id )ORDER BY c.nombre ASC');
		
			$dql->setParameter('id', $id);
			$consulta = $dql->getResult();			
					
		}else{
			$consulta = $em->getRepository('HcBundle:Cie')->findAll(array('nombre'=>'ASC'));			
			$permisos = 0;
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Diagnostico",$this->get("router")->generate("dx_list"));
		$breadcrumbs->addItem("Relacionar");
		
		return $this->render('HcBundle:Diagnostico:userDx.html.twig', array(
				'entity' => $usuario,
				'cies'   => $consulta,
				'permisos' => $permisos,
		));
	}
	
	public function saveUserDxAction($usuario, $cie)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('UsuarioBundle:Usuario')->find($usuario);
		$entityCi = $em->getRepository('HcBundle:Cie')->find($cie);
			
		if(!$entity || !$entityCi)
		{
			throw $this->createNotFoundException('La informacion solicitada no existe');
		}
		
		if($entity->addCie($entityCi)){
		
			$em->persist($entity);
			$em->flush();
		}
		
		return $this->redirect($this->generateUrl('dx_userDx', array('id' => $usuario)));
	}
	
	public function deleteUserDxAction($usuario, $cie)
	{
		$em = $this->getDoctrine()->getEntityManager();
		 
		$usuarios = $em->getRepository("UsuarioBundle:Usuario")->findOneById($usuario);
		$cies = $em->getRepository("HcBundle:Cie")->findOneById($cie);
			
		if(!$usuarios || !$usuarios)
		{
			throw $this->createNotFoundException('La informacion solicitada no existe');
		}
		
		$key = $usuarios->getCie()->indexOf($cies);		 
		$usuarios->getCie()->remove($key);		
		$em->flush();
		
		return $this->redirect($this->generateUrl('dx_userDx', array('id' => $usuario)));
	}
	
	
	public function ajaxUserDxAction()
	{
		$request = $this->get('request');
		$dx = $request->request->get('dx');
	
		if(is_numeric($dx)){
				
			$em = $this->getDoctrine()->getEntityManager();
				
			$dx = $em->getRepository('HcBundle:Cie')->find($dx);
			$usuario = $this->get('security.context')->getToken()->getUser();
	
			if(!$dx || !$usuario)
			{
				$response=array("responseCode"=>400, "msg"=>"La información del diagnostico o del usuario no esta disponible.");
			}else {
				if($usuario->addCie($dx)){
	
					$em->persist($usuario);
					$em->flush();
						
					$response=array("responseCode"=>200, "msg"=>"Diagnostico agregado éxitosamente.");
						
					$response['id'] = $dx->getId();
					$response['codigo'] = $dx->getCodigo();
					$response['nombre'] = $dx->getNombre();
						
				}else {
					$response=array("responseCode"=>400, "msg"=>"El diagnostico ya ha sido agregado anteriormente.");
				}
			}
		}else {
			$response=array("responseCode"=>400, "msg"=>"El diagnostico seleccionado es incorrecto.");
		}
	
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
	
}
