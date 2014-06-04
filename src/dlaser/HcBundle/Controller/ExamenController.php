<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use dlaser\HcBundle\Entity\Examen;
use dlaser\HcBundle\Form\ExamenType;
use dlaser\HcBundle\Form\searchType;

class ExamenController extends Controller
{

	/* teniendo en cuenta q el usuario no puede modificar la informacion del examen
	 * yA q esta action solo la puede hacer el admin entonces vistas simples no se van a establecer
	 * tales como el show, el admin tendra una lista de examenes o consulta de examenes por codigo para 
	 * editarlos o actualizarlos. el show comunica con el edit ambas plantillas estan creadas, el $id que 
	 * llega al show es el id del examen al q el usuario (admin) selecciona. 
	 * 
	 */
	
	/* los examenes son creados por los administradores por eso no se establece la relacion directa con los usuarios
	 * ademas estas relaciones son ManyToMany
	*/
	public function newAction()
	{
		$entity = new Examen();
		$form   = $this->createForm(new ExamenType(), $entity);
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen", $this->get("router")->generate("examen_examen"));
		$breadcrumbs->addItem("Nuevo");
		
		$user = $this->get('security.context')->getToken()->getUser();
		
		if ($user->getPerfil() != 'ROLE_ADMIN'){			
			$this->get('session')->setFlash('error','El usuario no tiene permisos, pongase en contacto con el administrador para crear nuevos examenes.');
			return $this->redirect($this->generateUrl('hc_search'));			
		}else{
			return $this->render('HcBundle:Examen:new.html.twig', array(
					'entity' => $entity,
					'form'   => $form->createView()
			));
		}	
	}
	
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$examen = $em->getRepository('HcBundle:Examen')->find($id);
		
		if(!$examen)
		{
			throw $this->createNotFoundException('El examen no existe.');
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen",$this->get("router")->generate("examen_examen"));		
		$breadcrumbs->addItem("Detalle");				
				
		return $this->render('HcBundle:Examen:show.html.twig', array(
				'entity'  => $examen,
		));
	}
	
	public function examenAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(15);
		$user = $this->get('security.context')->getToken()->getUser();
		$rolle = $user->getPerfil();

		
		if ($rolle != 'ROLE_ADMIN'){
			throw $this->createNotFoundException('El usuario no tiene permisos');
		}		
					
		$examenes = $paginador->paginate($em->getRepository('HcBundle:Examen')->findAll())->getResult();
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen", $this->get("router")->generate("examen_examen"));
		$breadcrumbs->addItem("Listar");
	
		return $this->render('HcBundle:Examen:examen.html.twig', array(
				'examenes'   => $examenes				
		));
	}
	
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$editEx = $em->getRepository('HcBundle:Examen')->find($id);
	
		if(!$editEx)
		{
			throw $this->createNotFoundException('El examen no existe');
		}
	
		$editform   = $this->createForm(new ExamenType(), $editEx);
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen",$this->get("router")->generate("examen_examen"));		
		$breadcrumbs->addItem("Modificar");
	
		return $this->render('HcBundle:Examen:edit.html.twig', array(
				'entity' => $editEx,
				'edit_form'   => $editform->createView()
		));
	}

	

	public function saveAction()
	{		
		$request = $this->getRequest();
		$em = $this->getDoctrine()->getEntityManager();
		
		$entity  = new Examen();		
		$form    = $this->createForm(new ExamenType(), $entity);
		
		if($request->getMethod() == 'POST')
		{							
			$form->bindRequest($request);
				
			if ($form->isValid()) 
			{						
				$em->persist($entity);
				$em->flush();
	
				$this->get('session')->setFlash('ok','El examen se ha registrado correctamente ');					
				return $this->redirect($this->generateUrl('examen_new'));
			 }
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen", $this->get("router")->generate("examen_examen"));
		$breadcrumbs->addItem("Nuevo");
		
		return $this->render('HcBundle:Examen:new.html.twig', array(
				'entity' => $entity,
				'form'   => $form->createView()
		));
	}
	
	/* la informacion de examen se lista de acuerdo al id del usuario 
	 * es decir que me liste todos los examenes q estan relacionados con el usuario activo
	 */
	public function listAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get('security.context')->getToken()->getUser();
		$id = $user->getId();		
		
		$usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
		 
		if (!$usuario){
			throw $this->createNotFoundException('El usuario solicitado no existe');
		}
		 
				
		$permisos = $usuario->getExamen() ;
		 
		if($permisos){
		
			$dql = $em->createQuery('SELECT e FROM HcBundle:Examen e
					WHERE e.id NOT IN (SELECT E FROM HcBundle:Examen E JOIN E.usuario u JOIN u.sede s WHERE u.id = :id )');
		
			$dql->setParameter('id', $id);
			$consulta = $dql->getResult();
		
		}else{
			$consulta = $em->getRepository('HcBundle:Examen')->findAll();
			$permisos = 0;
		}
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));		
		$breadcrumbs->addItem("Examen",$this->get("router")->generate("examen_list"));
		$breadcrumbs->addItem("Relacionar");
		
		return $this->render('HcBundle:Examen:list.html.twig', array(
				'entity' => $usuario,
				'examenes'   => $consulta,
				'permisos' => $permisos,
		));		
	}
	//-----------------------------------------------
	/* se genera la relacion entre el usuario y el examen de las tablas que estan como manytomany
	 * este metodo lo que hace es asignar la relacion entre estos la que le sigue a este mentodo 
	 * se ecarga de eliminar la relacion entre estos.
	 */
	public function userExamenAction($usuario,$examen)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('UsuarioBundle:Usuario')->find($usuario);
		$entityEx = $em->getRepository('HcBundle:Examen')->find($examen);
			
		if(!$entity || !$entityEx)
		{
			throw $this->createNotFoundException('La informacion solicitada no existe');
		}
		
		if($entity->addExamen($entityEx)){
			 
			$em->persist($entity);
			$em->flush();
		}
		
		return $this->redirect($this->generateUrl('examen_list', array('id' => $usuario)));
	}
	
	public function deleteUserExamenAction($usuario,$examen)
	{
		$em = $this->getDoctrine()->getEntityManager();
		 
		$usuarios = $em->getRepository("UsuarioBundle:Usuario")->findOneById($usuario);
		$examenes = $em->getRepository("HcBundle:Examen")->findOneById($examen);

		if(!$usuarios||!$examenes)
		{
			throw $this->createNotFoundException('La informacion solicitada no existe');			
		}
		
		$key = $usuarios->getExamen()->indexOf($examenes);		 
		$usuarios->getExamen()->remove($key);		
		$em->flush();
		
		return $this->redirect($this->generateUrl('examen_list', array('id' => $usuario)));
	}
	//-----------------------------------------------

	

	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$update = $em->getRepository('HcBundle:Examen')->find($id);
		
		if(!$update)
		{
			throw $this->createNotFoundException('El examen no existe.');
		}
			
		$upForm = $this->createForm(new ExamenType(), $update);
		$request = $this->getRequest();
		$upForm->bindRequest($request);
			
		if ($upForm->isValid())
		{
			$em->persist($update);
			$em->flush();
			$this->get('session')->setFlash('ok', 'El examen ha sido modificada éxitosamente.');
			return $this->redirect($this->generateUrl('examen_edit', array('id' => $id)));
		}
		
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("hc_list"));
		$breadcrumbs->addItem("Examen", $this->get("router")->generate("examen_examen"));
		$breadcrumbs->addItem("Detalle");
			
		return $this->render('HcBundle:Examen:edit.html.twig', array(
				'entity'      => $update,
				'edit_form'   => $upForm->createView(),
		));
	}
	
	public function ajaxUserExamenAction()
	{
		$request = $this->get('request');
		$examen = $request->request->get('examen');
		
		if(is_numeric($examen)){
			
			$em = $this->getDoctrine()->getEntityManager();
			
			$examen = $em->getRepository('HcBundle:Examen')->find($examen);
			$usuario = $this->get('security.context')->getToken()->getUser();
				
			if(!$examen || !$usuario)
			{
				$response=array("responseCode"=>400, "msg"=>"La información del examen o del usuario no esta disponible.");
			}else {
				if($usuario->addExamen($examen)){
						
					$em->persist($usuario);
					$em->flush();					
					
					$response=array("responseCode"=>200, "msg"=>"Examen agregado éxitosamente.");
					
					$response['id'] = $examen->getId();
					$response['codigo'] = $examen->getCodigo();
					$response['nombre'] = $examen->getNombre();
					
				}else {
					$response=array("responseCode"=>400, "msg"=>"El examen ya ha sido agregado anteriormente.");
				}
			}
		}else {
			$response=array("responseCode"=>400, "msg"=>"El examen seleccionado es incorrecto.");
		}
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
}
