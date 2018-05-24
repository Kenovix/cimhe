<?php

namespace dlaser\AdminBundle\Controller;;

use Io\TcpdfBundle\Helper\Tcpdf;

use dlaser\AdminBundle\Form\AdmisionAuxType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use dlaser\ParametrizarBundle\Entity\Factura;
use dlaser\ParametrizarBundle\Entity\Facturacion;
use dlaser\AdminBundle\Form\FacturaType;
use dlaser\AdminBundle\Form\FacturacionType;
use dlaser\AdminBundle\Form\AdmisionType;
use dlaser\HcBundle\Entity\Hc;
use dlaser\HcBundle\Entity\HcMedicamento;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\Query;
use ZipArchive;

class FacturaController extends Controller
{
    
	public function listAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(30);
	
		$dql = $em->createQuery("SELECT
						    		f.id,
						    		f.fecha,
						    		p.priNombre,
						    		p.segNombre,
						    		p.priApellido,
						    		p.segApellido,
						    		c.nombre,
									cli.nombre as cliente,
						    		f.valor,
						    		f.copago,
						    		f.estado,
									s.nombre as sede
					    		FROM
					    			ParametrizarBundle:Factura f
					    		JOIN
					    			f.cargo c
					    		JOIN
					    			f.paciente p
					    		JOIN
					    			f.cliente cli
								JOIN
									f.sede s
								WHERE
									f.id <= 16866
					    		ORDER BY
					    			f.id ASC");
		
		$facturas = $paginador->paginate($dql->getResult())->getResult();
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_list"));
		$breadcrumbs->addItem("Listar");
	
		return $this->render('AdminBundle:Factura:list.html.twig', array(
				'entities'  => $facturas
		));
	}
	
	public function listNewAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(20);
	
		$dql = $em->createQuery("SELECT
						    		f
					    		FROM
					    			ParametrizarBundle:Facturacion f
					    		WHERE
									f.final = 1 
					    		ORDER BY
					    			f.fecha DESC");
		
	
		$facturas = $paginador->paginate($dql->getResult())->getResult();
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_list_new"));
		$breadcrumbs->addItem("Listar Nuevas");
	
		return $this->render('AdminBundle:Factura:list_new.html.twig', array(
				'entities'  => $facturas
		));
	}
	
	public function listNewPacAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$paginador = $this->get('ideup.simple_paginator');
		$paginador->setItemsPerPage(20);
	
		$dql = $em->createQuery("SELECT
						    		f.id,
						    		f.fecha,
                                    f.prefijo,
                                    f.consecutivo,
									p.priNombre,
						    		p.segNombre,
						    		p.priApellido,
						    		p.segApellido,
									cli.nombre as cliente,
									f.concepto,
						    		f.subtotal,
									f.copago,
						    		f.iva,
						    		f.estado,
									s.nombre as sede
					    		FROM
					    			ParametrizarBundle:Facturacion f
					    		JOIN
					    			f.cliente cli
								JOIN
					    			f.paciente p
								JOIN
									f.sede s
                                WHERE
                                    f.final = 0
					    		ORDER BY
					    			f.id DESC");
	
	
		$facturas = $paginador->paginate($dql->getResult())->getResult();
	
		$breadcrumbs = $this->get("white_october_breadcrumbs");
		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("empresa_list"));
		$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_list_new"));
		$breadcrumbs->addItem("Listar Nuevas");
	
		return $this->render('AdminBundle:Factura:list_new_pac.html.twig', array(
				'entities'  => $facturas
		));
	}
	
	public function newAction($cupo)
    {
        $entity = new Factura();
        $form   = $this->createForm(new FacturaType(), $entity);
        
        $em = $this->getDoctrine()->getEntityManager();
        $reserva = $em->getRepository('AgendaBundle:Cupo')->find($cupo);
        $contrato = $em->getRepository('ParametrizarBundle:Contrato')->findOneBy(array('sede' => $reserva->getAgenda()->getSede()->getId(), 'cliente' => $reserva->getCliente()));
        $actividad = $em->getRepository('ParametrizarBundle:Actividad')->findOneBy(array('cargo' => $reserva->getCargo()->getId(), 'contrato' => $contrato->getId()));
        $cliente = $em->getRepository('ParametrizarBundle:Cliente')->find($reserva->getCliente());
        $valorFijo = $actividad->getPrecio();

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
        $breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
        $breadcrumbs->addItem("Nueva admisión");
        
        return $this->render('AdminBundle:Factura:new.html.twig', array(
            'cupo' => $reserva,
        	'cliente' => $cliente,
            'actividad' => $valorFijo,
        	'contrato' => $contrato,
            'form'   => $form->createView()
        ));
    }
    
    public function saveAction($cupo)
    {
        $entity  = new Factura();
        
        $request = $this->getRequest();
        $form    = $this->createForm(new FacturaType(), $entity);
        $form->bindRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $reserva = $em->getRepository('AgendaBundle:Cupo')->find($cupo);
            
            if (!$reserva) {
                throw $this->createNotFoundException('La reserva solicitada no existe');
            }
            
            $cliente = $em->getRepository('ParametrizarBundle:Cliente')->find($reserva->getCliente());
            $contrato = $em->getRepository('ParametrizarBundle:Contrato')->findOneBy(array('sede' => $reserva->getAgenda()->getSede()->getId(), 'cliente' => $reserva->getCliente()));
            $actividad = $em->getRepository('ParametrizarBundle:Actividad')->findOneBy(array('cargo' => $reserva->getCargo()->getId(), 'contrato' => $contrato->getId()));
            $user = $this->get('security.context')->getToken()->getUser();
            
            if($actividad->getPrecio()){
                $valor = $actividad->getPrecio();
            }else{
                $valor = round(($reserva->getCargo()->getValor()+($reserva->getCargo()->getValor()*$contrato->getPorcentaje())));
            }
            
            $query = $em->createQuery("   SELECT
								    		f
							    		FROM
							    			ParametrizarBundle:Factura f
							    		WHERE
            								f.sede = :sede AND
            								f.paciente = :paciente AND
            								f.cliente = :cliente AND
            								f.autorizacion = :autorizacion");

            

            $query->setParameter('sede', $reserva->getAgenda()->getSede()->getId());
            $query->setParameter('paciente', $reserva->getPaciente()->getId());
            $query->setParameter('cliente', $reserva->getCliente());
            $query->setParameter('autorizacion', $entity->getAutorizacion());
            
            $doble_ops = $query->getResult();
            
            if ($doble_ops){
                $grupo = $doble_ops[0]->getGrupo();
            	//$entity->setGrupo($doble_ops[0]->getGrupo());
            }else {
                $grupo = $reserva->getCargo()->getTipo();
            	//$entity->setGrupo($reserva->getCargo()->getTipo());
            }
            
            $fecha = new \DateTime('now');

            $entity->setFecha($fecha);
            $entity->setEstado('I');
            $entity->setCargo($reserva->getCargo());
            $entity->setValor($valor);
            $entity->setCliente($cliente);
            $entity->setPaciente($reserva->getPaciente());
            $entity->setSede($reserva->getAgenda()->getSede());
            $entity->setCupo($reserva);
            $entity->setGrupo($grupo);
            
            $reserva->setEstado('F');
            
            $dql= " SELECT
    				MAX(f.consecutivo) AS id
    			FROM
    				ParametrizarBundle:Facturacion f
				WHERE
					f.prefijo = 'T'";
            
            $query = $em->createQuery($dql);
            
            $id = $query->getSingleResult();
            
            if ($id){
            	$id = 1 + $id['id'];
            }else{
            	$id=1;
            }
            
            if ($cliente->getParticular() == 'SI'){
                
                $f_f  = new Facturacion();
                
                $f_f->setPrefijo('T');
                $f_f->setConsecutivo($id);
                $f_f->setFecha($fecha);
                $f_f->setInicio($fecha);
                $f_f->setFin($fecha);
                $f_f->setConcepto($reserva->getCargo()->getNombre());
                $f_f->setSubtotal($valor);
                $f_f->setIva(0);
                $f_f->setEstado('G');
                $f_f->setCliente($cliente);
                $f_f->setSede($reserva->getAgenda()->getSede());
                $f_f->setPaciente($reserva->getPaciente());
                $f_f->setCargo($reserva->getCargo());
                $f_f->setGrupo($grupo);
                $f_f->setUser($user->getId());
                
                $em->persist($entity);
                $em->persist($reserva);
                $em->persist($f_f);
                
                $ft = true;
            }else{
                
                $f_f  = new Facturacion();
                
                if ($entity->getCopago() > 0){
                    $valor = $valor - $entity->getCopago();
                }
                
                $f_f->setPrefijo('T');
                $f_f->setConsecutivo($id);
                $f_f->setFecha($fecha);
                $f_f->setInicio($fecha);
                $f_f->setFin($fecha);
                $f_f->setConcepto($reserva->getCargo()->getNombre());
                $f_f->setSubtotal($valor);
                $f_f->setIva(0);
                $f_f->setEstado('G');
                $f_f->setCliente($cliente);
                $f_f->setSede($reserva->getAgenda()->getSede());
                $f_f->setPaciente($reserva->getPaciente());
                $f_f->setFinal(1);
                $f_f->setSedes($reserva->getAgenda()->getSede());
                $f_f->setCargo($reserva->getCargo());
                $f_f->setAutorizacion($entity->getAutorizacion());
                $f_f->setCopago($entity->getCopago());
                $f_f->setGrupo($grupo);
                $f_f->setUser($user->getId());
                
                $em->persist($entity);
                $em->persist($reserva);
                $em->persist($f_f);
                
                if ($entity->getCopago() > 0){
                    
                    $f_f  = new Facturacion();
                    
                    $id++;
                    
                    $f_f->setPrefijo('T');
                    $f_f->setConsecutivo($id);
                    $f_f->setFecha($fecha);
                    $f_f->setInicio($fecha);
                    $f_f->setFin($fecha);
                    $f_f->setConcepto('COPAGO '.$reserva->getCargo()->getNombre());
                    $f_f->setSubtotal($entity->getCopago());
                    $f_f->setIva(0);
                    $f_f->setEstado('G');
                    $f_f->setCliente($cliente);
                    $f_f->setSede($reserva->getAgenda()->getSede());
                    $f_f->setPaciente($reserva->getPaciente());
                    $f_f->setFinal(0);
                    $f_f->setUser($user->getId());
                    
                    $em->persist($f_f);
                    
                    $factura_copago = $f_f->getId();
                    
                }
                
                $ft = false;
            }
            
            $em->flush();
            
            if($entity->getCopago() > 0){
                $factura_copago = $f_f->getId();
                $factura_actividad = $factura_copago - 1;
            }else{
                $factura_copago = 0;
                $factura_actividad = $f_f->getId();
            }
            
            $this->get('session')->setFlash('ok', 'La admisión ha sido registrada éxitosamente.');            

            if ($ft) {
            	return $this->redirect($this->generateUrl('factura_paciente_show',array("id"=>$f_f->getId())));
            }else{
                return $this->redirect($this->generateUrl('factura_show',array( "factura"=>$factura_actividad, "copago"=>$factura_copago )));
            }
        }

        return $this->render('AdminBundle:Factura:new.html.twig', array(
                'cupo' => $reserva,
                'actividad' => $valor,
                'form'   => $form->createView()
        ));
    }   
        
    public function searchAction()
    {
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Buscar");
    	
    	return $this->render('AdminBundle:Factura:search.html.twig');
    }
    
    public function buscarFacturaAction()
    {
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Buscar admisión");    	
    	
    	return $this->render('AdminBundle:Factura:buscar_factura.html.twig');
    }
    
    public function listAdmisionAction()
    {
    	$request = $this->get('request');

    	$parametro = $request->request->get('parametro');
    	$valor = $request->request->get('valor');

    	if(is_numeric(trim($valor)) && $valor > 0 ){

    		$em = $this->getDoctrine()->getEntityManager();

    		$dql= " SELECT
			    		f.id,
                        f.prefijo,
                        f.consecutivo,
			    		p.id as paciente,
			    		p.tipoId,
			    		p.identificacion,
			    		f.fecha,
			    		f.autorizacion,
			    		p.priNombre,
			    		p.segNombre,
			    		p.priApellido,
			    		p.segApellido,
			    		c.cups,
			    		f.subtotal,
			    		f.copago,
			    		f.estado
		    		FROM
		    			ParametrizarBundle:Facturacion f
		    		JOIN
		    			f.cargo c
		    		JOIN
		    			f.paciente p
		    		JOIN
		    			f.cliente cli
		    		WHERE
			    		p.identificacion = :identificacion
		    		ORDER BY
		    			f.fecha DESC";
    		
    		$query = $em->createQuery($dql);
    		$query->setParameter('identificacion', $valor);    		
    		$entity = $query->getResult();
    		
    		$breadcrumbs = $this->get("white_october_breadcrumbs");
    		$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    		$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));    		
    		$breadcrumbs->addItem("Listar admisión");
    		
    		return $this->render('AdminBundle:Factura:listar_admisiones.html.twig', array(
    				'entities' => $entity
    		));

    	}else{
    		$this->get('session')->setFlash('info', 'El parametro ingresado es incorrecto.');
    		return $this->redirect($this->generateUrl('factura_admision_search'));
    	}
    }

    public function editAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();    
    	$entity = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura solicitada no existe');
    	}
    	$user = $this->get('security.context')->getToken()->getUser();
    	    
    	if ($user->getPerfil() == 'ROLE_ADMIN') {
    		$editForm = $this->createForm(new AdmisionType(), $entity);
    	}
    	else{
    		$editForm = $this->createForm(new AdmisionAuxType(), $entity);
    	}
    
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	//$breadcrumbs->addItem("Detalle ",$this->get("router")->generate("factura_show",array("id" => $id)));
    	$breadcrumbs->addItem("Modificar admisión");
    	
    	return $this->render('AdminBundle:Factura:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    	));
    }
    
    public function updateAction($id)
        {
    	$em = $this->getDoctrine()->getEntityManager();    
    	$entity = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    	
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura solicitada no existe.');
    	}
    	
    	$request = $this->getRequest();
    	$user = $this->get('security.context')->getToken()->getUser();
    	
    	if($user->getPerfil() == 'ROLE_ADMIN'){

    		$editForm = $this->createForm(new AdmisionType(), $entity);    		
    	}else{
    		$editForm = $this->createForm(new AdmisionAuxType(), $entity);
    	}
    	
    	$editForm->bindRequest($request);
    
    	if ($editForm->isValid()) {
    		$cliente = $em->getRepository('ParametrizarBundle:Cliente')->find($entity->getCliente()->getId());    	
    		$contrato = $em->getRepository('ParametrizarBundle:Contrato')->findOneBy(array('sede' => $entity->getSede()->getId(), 'cliente' => $cliente->getId()));
    		
    		if(!$contrato){
    			$this->get('session')->setFlash('info', 'El cliente seleccionado no tiene contrato con la sede, por favor verifique y vuelva a intentarlo.');
    			return $this->redirect($this->generateUrl('factura_edit', array('id' => $id)));
    		} 		
    		
    		$actividad = $em->getRepository('ParametrizarBundle:Actividad')->findOneBy(array('cargo' => $entity->getCargo()->getId(), 'contrato' => $contrato->getId()));
    		
    		if(!$user->getPerfil() == 'ROLE_ADMIN'){    		
	    		if($actividad->getPrecio()){
	    			$valor = $actividad->getPrecio();
	    		}else{
	    			$valor = round(($entity->getCargo()->getValor()+($entity->getCargo()->getValor()*$contrato->getPorcentaje()/100)));
	    		}
	    		
	    		$entity->setValor($valor);
    		}    		
    		$entity->setCliente($cliente);
    
    		$em->persist($entity);
    		$em->flush();
    
    		$this->get('session')->setFlash('info', 'La información de la admisión ha sido modificada éxitosamente.');    
    		return $this->redirect($this->generateUrl('factura_edit', array('id' => $id)));
    	}    
    	return $this->render('AdminBundle:Factura:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    	));
    }   
    
    public function showAction($factura, $copago=0)
    {
        $em = $this->getDoctrine()->getEntityManager();
    
        $factura = $em->getRepository('ParametrizarBundle:Facturacion')->find($factura);
        
        
        if (!$factura) {
            throw $this->createNotFoundException('La factura solicitada no esta disponible.');
        }
    
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
        $breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
        $breadcrumbs->addItem("Detalle factura");
        
        if($copago != 0){
            $copago = $em->getRepository('ParametrizarBundle:Facturacion')->find($copago);
        }else{
            $copago = null;
        }
        
        return $this->render('AdminBundle:Factura:show.html.twig', array(
                'entity'  => $factura,
                'copago' => $copago,
                
        ));
    }
    
	public function imprimirAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$entity = $em->getRepository('ParametrizarBundle:Factura')->find($id);

    	if (!$entity) {
    		throw $this->createNotFoundException('La factura a imprimir no esta disponible.');
    	}    

    	$paciente = $entity->getPaciente();
    	$cliente = $entity->getCliente();
    	$sede = $entity->getSede();

    	$html = $this->renderView('AdminBundle:Factura:factura.pdf.twig',
    			array('entity' 	=> $entity,    				  
    				  'paciente' => $paciente,
    				  'cliente'	=> $cliente,
    				  'sede'=>$sede
    			));

    	$this->get('io_tcpdf')->formato = 'P5';
    	$this->get('io_tcpdf')->orientacion = 'L';

    	return $this->get('io_tcpdf')->quick_pdf($html, 'factura_venta'.$entity->getId().'.pdf', 'I');

	}


    public function updateEstadoAction($id, $estado)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    
    	$entity = $em->getRepository('ParametrizarBundle:Factura')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura solicitada no existe.');
    	}
    	
    	$entity->setEstado($estado);

    	$em->persist($entity);
    	$em->flush();
    
    	return new Response('ok');
    }    
    
    /**
     * @uses Función que devuelve las sedes asociadas de un usuario. 
     * 
     * @param ninguno
     */
    public function ajaxBuscarAction() {
        
        $request = $this->get('request');
        $parametro=$request->request->get('parametro');
        $valor=$request->request->get('valor');
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $fecha=new \DateTime('now');
        
        if($parametro == 'codigo'){
            $query = $em->createQuery(' SELECT c.id,
                    c.hora,
                    c.nota,
                    c.registra,
                    c.verificacion,
                    p.priNombre,
                    p.segNombre,
                    p.priApellido,
                    p.segApellido,
                    car.nombre
                    FROM AgendaBundle:Cupo c
                    LEFT JOIN c.paciente p
                    LEFT JOIN c.cargo car
                    WHERE c.verificacion = :codigo
                    AND c.hora >= :fecha
                    ORDER BY c.hora ASC');           
            
            $query->setParameter('fecha', $fecha->format('Y-m-d 00:00:00'));
            $query->setParameter('codigo', $valor);
            $reserva = $query->getArrayResult();
        }
        
        if($parametro == 'identificacion'){
        
            $query = $em->createQuery(' SELECT c.id,
                    c.hora,
                    c.nota,
                    c.registra,
                    c.verificacion,
                    p.priNombre,
                    p.segNombre,
                    p.priApellido,
                    p.segApellido,
                    car.nombre as cargo,
            		s.nombre as sede
                    FROM AgendaBundle:Cupo c
                    LEFT JOIN c.paciente p
                    LEFT JOIN c.cargo car
            		LEFT JOIN c.agenda a
            		LEFT JOIN a.sede s
                    WHERE p.identificacion = :identificacion
                    AND c.hora >= :fechaI
            		AND c.hora <= :fechaF
            		AND c.estado = :estado
                    ORDER BY c.hora ASC');
            
            
            $query->setParameter('fechaI', $fecha->format('Y-m-d 00:00:00'));
            $query->setParameter('fechaF', $fecha->format('Y-m-d 23:59:00'));
            $query->setParameter('identificacion', $valor);
            $query->setParameter('estado', 'A');
            $reserva = $query->getArrayResult();
    }
    
    
    if($parametro == 'nombre'){
        
            $query = $em->createQuery(" SELECT c.id,
                    c.hora,
                    c.nota,
                    c.registra,
                    c.verificacion,
                    p.priNombre,
                    p.segNombre,
                    p.priApellido,
                    p.segApellido,
                    car.nombre
                    FROM AgendaBundle:Cupo c
                    LEFT JOIN c.paciente p
                    LEFT JOIN c.cargo car
                    WHIT p.priNombre LIKE '%hernan%'");
            
            
            $query->setParameter('nombre', $valor);            
            $reserva = $query->getArrayResult();
    }
        
            if (!$reserva){
                $response=array("responseCode"=>400, "msg"=>"No existen reservas para los parametros de consulta ingrasados.");
            }else{           
    
                $response=array("responseCode"=>200);
    
                foreach($reserva as $key => $value)
                {
                    $response['cupo'][$key] = $value;
                }
                
            }
        
        $return=json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));
        
    } 

    public function arqueoAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();    	

    	$usuarios = $em->getRepository('UsuarioBundle:Usuario')->findAll();
    	$sedes = $em->getRepository('ParametrizarBundle:Sede')->findAll();
    	
    	if(!$usuarios)
    	{
    		throw $this->createNotFoundException('El usuario no existe no esta identificado.'); 
    	}    	
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Arqueo");
    	
    	return $this->render('AdminBundle:Factura:arqueo.html.twig', array(
    			'sedes' => $sedes,
    			'usuarios' => $usuarios
    	));
    	
    }
    
    public function imprimirArqueoAction()
    {
        $request = $this->get('request');
        $sede = $request->request->get('sede');
        $usuario = $request->request->get('usuario');
        
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	$fecha=new \DateTime('now');
    	
    	$dql= " SELECT 
    				f.prefijo,
                    f.consecutivo,
    				c.cups,
    				f.autorizacion,
    				p.identificacion,
    				p.priNombre,
                    p.segNombre,
                    p.priApellido,
                    p.segApellido,
                    cli.id as cliente,
                    cli.nombre,
                    f.subtotal,
                    f.copago,
                    f.estado,
                    cli.particular
    			FROM 
    				ParametrizarBundle:Facturacion f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			WHERE 
    				f.fecha > :inicio AND 
    				f.fecha <= :fin AND 
    				f.sede = :sede AND
                    f.user = :usuario
    			ORDER BY 
    				f.fecha ASC";
    	
    	$query = $em->createQuery($dql);

    	$query->setParameter('inicio', $fecha->format('Y-m-d 00:00:00'));
    	$query->setParameter('fin', $fecha->format('Y-m-d 23:59:00'));
    	$query->setParameter('sede', $sede);
    	$query->setParameter('usuario', $usuario);
    	
    	$entity = $query->getResult();
    	 
    	$sede  = $em->getRepository('ParametrizarBundle:Sede')->find($sede);    	 
    	
    	if (!$sede) {
    		throw $this->createNotFoundException('La sede no existe no esta identificado.');
    	}    	
    	if (!$entity) {
    		$this->get('session')->setFlash('info', 'No hay informacion facturada el dia de hoy.');
    		return $this->redirect($this->generateUrl('factura_arqueo'));
    	}
    	
    	$user = $em->getRepository('UsuarioBundle:Usuario')->find($usuario);
    	
    	$html = $this->renderView('AdminBundle:Factura:imprimir_arqueo.pdf.twig', array(
    			'entity' => $entity,
    			'sede' => $sede,
    	        'usuario' => $user
    	));

    	return $this->get('io_tcpdf')->quick_pdf($html, 'Arqueo de Caja '.$fecha->format('d-m-Y').' Sede '.$sede->getNombre().'.pdf', 'I');
    }
    
    
    public function consultarArqueoAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$usuario = $this->get('security.context')->getToken()->getUser();
    	$usuario = $em->getRepository('UsuarioBundle:Usuario')->find($usuario->getId());
    	$sedes = $usuario->getSede();
    	 
    	if(!$usuario)
    	{
    		throw $this->createNotFoundException('El usuario no existe no esta identificado.');
    	}
    	 
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_list"));
    	$breadcrumbs->addItem("Consultar cierre de caja");
    	 
    	return $this->render('AdminBundle:Factura:consultar_arqueo.html.twig', array(
    			'sedes' => $sedes,
    			'usuario' => $sedes
    	));
    	 
    }
    

    public function gstConsultarArqueoAction()
    {
    
    	$request = $this->get('request');
    
    	$sede = $request->request->get('sede');
    	$dia = $request->request->get('dia');
    	$mes = $request->request->get('mes');
    	$ano = $request->request->get('ano');
    	 
    	$url_retorno = 'factura_consultar_arqueo';
    
    	if(trim($dia) && trim($mes) && trim($ano)){
    
    		if(!checkdate($mes,$dia,$ano)){
    			$this->get('session')->setFlash('info', 'La fecha ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl($url_retorno));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha no puede estar en blanco.');
    		return $this->redirect($this->generateUrl($url_retorno));
    	}

    	$em = $this->getDoctrine()->getEntityManager();
    	
    	$str = $ano.'-'.$mes.'-'.$dia;
		$fecha = \DateTime::createFromFormat('Y-m-d', $str);
		
		$sede  = $em->getRepository('ParametrizarBundle:Sede')->find($sede);
		 
		if (!$sede) {
			throw $this->createNotFoundException('La sede no existe no esta identificado.');
		}

    
    	$dql= " SELECT 
    				f.id,
    				c.cups,
    				f.autorizacion,
    				p.identificacion,
    				p.priNombre,
                    p.segNombre,
                    p.priApellido,
                    p.segApellido,
                    cli.id as cliente,
                    cli.nombre,
                    f.valor,
                    f.copago,
                    f.descuento,
                    f.estado
    			FROM 
    				ParametrizarBundle:Factura f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			WHERE 
    				f.fecha > :inicio AND 
    				f.fecha <= :fin AND 
    				f.sede = :id 
    			ORDER BY 
    				f.fecha ASC";
    	
    	$query = $em->createQuery($dql);

    	$query->setParameter('inicio', $fecha->format('Y-m-d 00:00:00'));
    	$query->setParameter('fin', $fecha->format('Y-m-d 23:59:00'));
    	$query->setParameter('id', $sede);
    	
    	$entity = $query->getResult();
    	
    	if (!$entity) {
    		$this->get('session')->setFlash('info', 'No hay informacion facturada el dia '.$fecha->format('d-m-Y').'.');
    		return $this->redirect($this->generateUrl('factura_consultar_arqueo'));
    	}
    	 
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Reporte ",$this->get("router")->generate("factura_reporte_medico"));
    	$breadcrumbs->addItem("Actividad medico");
    
    	
    	$html = $this->renderView('AdminBundle:Factura:imprimir_arqueo.pdf.twig', array(
    			'entity' => $entity,
    			'sede' => $sede
    	));

    	return $this->get('io_tcpdf')->quick_pdf($html, 'Arqueo de Caja '.$fecha->format('d-m-Y').' Sede '.$sede->getNombre().'.pdf', 'I');
    }
    
    
    public function listadoClienteAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	$sedes = $em->getRepository("ParametrizarBundle:Sede")->findAll();
    	$clientes = $em->getRepository("ParametrizarBundle:Cliente")->findBy(array(), array('nombre' => 'ASC'));
    	
    	if($this->get('security.context')->isGranted('ROLE_ADMIN')) {
    		$plantilla = 'AdminBundle:Factura:listar_cliente.html.twig';
    	}else {
    		$plantilla = 'AdminBundle:Reporte:listado_cliente.html.twig';
    	}
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Clientes");

    	return $this->render($plantilla, array(
    			'sedes' => $sedes,
    			'clientes' => $clientes
    	));
    }
    
    /**
     * @uses Muestra el listado generado a partir de los parametros de consultas definidos.
     * 
     * @param Pasados por POST.
     */
    public function actividadesClienteAction()
    {
    	
    	$request = $this->get('request');
    	
    	$sede = $request->request->get('sede');
    	$cliente = $request->request->get('cliente');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	$tipo = $request->request->get('tipo');
        $rips = $request->request->get('rips');
    	
    	if(trim($f_inicio)){
    		$desde = explode('-',$f_inicio);

    		if(!checkdate($desde[1],$desde[2],$desde[0])){		
    			$this->get('session')->setFlash('info', 'La fecha de inicio ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl('factura_cliente_list'));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de inicio no puede estar en blanco.');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	
    	if(trim($f_fin)){
    		$hasta = explode('-',$f_fin);
    		
    		if(!checkdate($hasta[1],$hasta[2],$hasta[0])){		
    			$this->get('session')->setFlash('info', 'La fecha de finalización ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl('factura_cliente_list'));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de finalización no puede estar en blanco.');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	    	  	
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    	}else{
    		$obj_sede['nombre'] = 'Todas las sedes.';
    		$obj_sede['id'] = '';
    	}
    	
    	if(is_numeric(trim($cliente))){
    		$obj_cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe seleccionar un cliente para continuar..');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	
    	if(!$obj_cliente){
    		$this->get('session')->setFlash('info', 'El cliente seleccionado no existe.');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	
    	if(is_object($obj_sede)){
    		$con_sede = "AND f.sede =".$sede;
    	}else{
    		$con_sede = "";
    	}
    	
    	if(trim($tipo)){
    		$con_tipo = "AND f.grupo ='".$tipo."'";
    	}else{
    		$con_tipo = "";
    	}
    	
    	$dql= " SELECT
			    	f.id,
                    f.prefijo,
                    f.consecutivo,
			    	p.id as paciente,
			    	p.tipoId,
			    	p.identificacion,
                    p.fN,
			    	f.fecha,
			    	p.priNombre,
			    	p.segNombre,
			    	p.priApellido,
			    	p.segApellido,
			    	f.concepto,
			    	f.subtotal,
			    	f.copago,
			    	f.estado,
                    f.autorizacion,
                    c.cups
    			FROM
    				ParametrizarBundle:Facturacion f
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
                JOIN
    				f.cargo c
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
                    f.final = 1 AND			    	
			    	f.cliente = :cliente ".
			    	$con_sede." ".
			    	$con_tipo."
		    	ORDER BY
		    		f.fecha ASC";
    	 
    	$query = $em->createQuery($dql);
    	
    	$query->setParameter('inicio', $desde[0]."/".$desde[1]."/".$desde[2].' 00:00:00');
    	$query->setParameter('fin', $hasta[0]."/".$hasta[1]."/".$hasta[2].' 23:59:00');
    	$query->setParameter('cliente', $cliente);
    	
    	$entity = $query->getResult();
    	
    	$user = $this->get('security.context')->getToken()->getUser();
    	
    	if($user->getPerfil() == 'ROLE_ADMIN') {
    		$plantilla = 'AdminBundle:Factura:actividades_cliente.html.twig';
    	}else {
    		$plantilla = 'AdminBundle:Reporte:actividades_cliente.html.twig';
    	}
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Actividades");
    	    	
    	return $this->render($plantilla, array(
    			'entities' => $entity,
    			'sede' => $obj_sede,
    			'cliente' => $obj_cliente,
    			'f_i' => $desde[0]."/".$desde[1]."/".$desde[2],
    			'f_f' => $hasta[0]."/".$hasta[1]."/".$hasta[2],
    			'tipo' => $tipo,
    			'rips' => $rips
    	));
    }
    
    
    public function listadoMedicoAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	$sedes = $em->getRepository("ParametrizarBundle:Sede")->findAll();
    	$medicos = $em->getRepository("UsuarioBundle:Usuario")->findBy(array('perfil' => 'ROLE_MEDICO'), array('nombre' => 'ASC'));
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Reporte medico");
    	    
    	return $this->render('AdminBundle:Reporte:listado_medico.html.twig', array(
    			'sedes' => $sedes,
    			'medicos' => $medicos
    	));
    }
    
    /**
     * @uses Muestra el listado generado a partir de los parametros de consultas definidos.
     *
     * @param Pasados por POST.
     */
    public function actividadesMedicoAction()
    {
    	 
    	$request = $this->get('request');
    	 
    	$sede = $request->request->get('sede');
    	$usuario = $request->request->get('usuario');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	
    	$url_retorno = 'factura_reporte_medico';
    	 
    	if(trim($f_inicio)){
    		$desde = explode('/',$f_inicio);
    
    		if(!checkdate($desde[1],$desde[0],$desde[2])){
    			$this->get('session')->setFlash('info', 'La fecha de inicio ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl($url_retorno));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de inicio no puede estar en blanco.');
    		return $this->redirect($this->generateUrl($url_retorno));
    	}
    	 
    	if(trim($f_fin)){
    		$hasta = explode('/',$f_fin);
    
    		if(!checkdate($hasta[1],$hasta[0],$hasta[2])){
    			$this->get('session')->setFlash('info', 'La fecha de finalización ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl($url_retorno));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de finalización no puede estar en blanco.');
    		return $this->redirect($this->generateUrl($url_retorno));
    	}
    	 
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    	}else{
    		$obj_sede['nombre'] = 'Todas las sedes.';
    		$obj_sede['id'] = '';
    	}
    	 
    	if(is_numeric(trim($usuario))){
    		$obj_usuario = $em->getRepository("UsuarioBundle:Usuario")->find($usuario);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe seleccionar un medico para continuar..');
    		return $this->redirect($this->generateUrl($url_retorno));
    	}
    	 
    	if(!$obj_usuario){
    		$this->get('session')->setFlash('info', 'El medico seleccionado no existe.');
    		return $this->redirect($this->generateUrl($url_retorno));
    	}
    	 
    	if(is_object($obj_sede)){
    		$con_sede = "AND f.sede =".$sede;
    	}else{
    		$con_sede = "";
    	}
    	 
    	$dql= " SELECT
			    	f.id,
			    	p.id as paciente,
			    	p.tipoId,
			    	p.identificacion,
			    	f.fecha,
			    	f.autorizacion,
			    	p.priNombre,
			    	p.segNombre,
			    	p.priApellido,
			    	p.segApellido,
    				cli.nombre,
			    	c.cups,
			    	f.valor,
			    	f.copago,
			    	f.descuento,
			    	f.estado
    			FROM
    				ParametrizarBundle:Factura f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			JOIN
    				f.cupo cp
    			JOIN
    				cp.agenda a
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
			    	a.usuario = :usuario ".
    			    	$con_sede."
		    	ORDER BY
		    		f.fecha ASC";
    
    	$query = $em->createQuery($dql);
    	 
    	$query->setParameter('inicio', $desde[2]."/".$desde[1]."/".$desde[0].' 00:00:00');
    	$query->setParameter('fin', $hasta[2]."/".$hasta[1]."/".$hasta[0].' 23:59:00');
    	$query->setParameter('usuario', $usuario);
    	 
    	$entity = $query->getResult();
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Reporte ",$this->get("router")->generate("factura_reporte_medico"));
    	$breadcrumbs->addItem("Actividad medico");
    
    	return $this->render('AdminBundle:Reporte:actividades_medico.html.twig', array(
    			'entities' => $entity,
    			'sede' => $obj_sede,
    			'usuario' => $obj_usuario,
    			'f_i' => $desde[2]."/".$desde[1]."/".$desde[0],
    			'f_f' => $hasta[2]."/".$hasta[1]."/".$hasta[0]
    	));
    }
    
    
    public function imprimirHonorarioAction()
    {
    	$request = $this->get('request');
    	 
    	$sede = $request->request->get('sede');
    	$usuario = $request->request->get('usuario');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	if(is_numeric(trim($usuario))){
    		$obj_usuario = $em->getRepository("UsuarioBundle:Usuario")->find($usuario);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe seleccionar un medico para continuar..');
    		return $this->redirect($this->generateUrl('factura_reporte_medico'));
    	}
    	 
    	if(!$obj_usuario){
    		$this->get('session')->setFlash('info', 'El medico seleccionado no existe.');
    		return $this->redirect($this->generateUrl('factura_reporte_medico'));
    	}
    	 
    	$obj_sede = null;
    	
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    		$con_sede = "AND f.sede =".$sede;
    		$empresa = $obj_sede->getEmpresa();
    	}else{
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find('1');
    		$empresa = $obj_sede->getEmpresa();
    		$con_sede = "";
    	}
    	 
    	$dql= " SELECT
			    	f.id,
			    	p.id as paciente,
			    	p.tipoId,
			    	p.identificacion,
			    	f.fecha,
			    	f.autorizacion,
    				cli.nombre,
			    	p.priNombre,
			    	p.segNombre,
			    	p.priApellido,
			    	p.segApellido,
			    	c.cups
    			FROM
    				ParametrizarBundle:Factura f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			JOIN
    				f.cupo cp
    			JOIN
    				cp.agenda a
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
    				f.estado = :estado AND
			    	a.usuario = :usuario ".
    			    	$con_sede."
		    	ORDER BY
		    		f.fecha ASC";
    	  
    	$query = $em->createQuery($dql);
    	 
    	$query->setParameter('inicio', $f_inicio.' 00:00:00');
    	$query->setParameter('fin', $f_fin.' 23:59:00');
    	$query->setParameter('usuario', $usuario);
    	$query->setParameter('estado', 'I');
    	 
    	$entity = $query->getResult();
    	
    	$dql= " SELECT
			    	c.cups,
    				count(c.id) as cantidad
    			FROM
    				ParametrizarBundle:Factura f
    			JOIN
    				f.cargo c
    			JOIN
    				f.cupo cp
    			JOIN
    				cp.agenda a
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
    				f.estado = :estado AND
			    	a.usuario = :usuario ".
    				    	$con_sede."
    			GROUP BY
    				c.cups
		    	ORDER BY
		    		f.fecha ASC
    			";
    	
    	$query = $em->createQuery($dql);
    	 
    	$query->setParameter('inicio', $f_inicio.' 00:00:00');
    	$query->setParameter('fin', $f_fin.' 23:59:00');
    	$query->setParameter('usuario', $usuario);
    	$query->setParameter('estado', 'I');
    	 
    	$cantidad = $query->getResult();   	
    
    	$html = $this->renderView('AdminBundle:Reporte:honorarios.pdf.twig', array(
    			'entities' => $entity,
    			'cantidad' => $cantidad,
    			'empresa' => $empresa,
    			'usuario' => $obj_usuario,
    			'f_i' => $f_inicio,
    			'f_f' => $f_fin
    	));
    
    	$this->get('io_tcpdf')->dir = $obj_sede->getDireccion();
    	$this->get('io_tcpdf')->ciudad = $obj_sede->getCiudad();
    	$this->get('io_tcpdf')->tel = $obj_sede->getTelefono();
    	$this->get('io_tcpdf')->mov = $obj_sede->getMovil();
    	$this->get('io_tcpdf')->mail = $obj_sede->getEmail();
    	$this->get('io_tcpdf')->sede = $obj_sede->getnombre();
    	$this->get('io_tcpdf')->empresa = $obj_sede->getEmpresa()->getNombre();
    
    	$this->get('io_tcpdf')->SetMargins(3, 10, 3);
    
    	return $this->get('io_tcpdf')->quick_pdf($html, 'Honorarios_Medicos.pdf', 'I');
    }
    
    
    public function imprimirCtaCobroAction()
    {
    	set_time_limit(0);
    	
    	$request = $this->get('request');
    	
    	$sede = $request->request->get('sede');
    	$cliente = $request->request->get('cliente');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	$tipo = $request->request->get('tipo');

    	$em = $this->getDoctrine()->getEntityManager();
    	
    	if(is_numeric(trim($cliente))){
    		$obj_cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe seleccionar un cliente para continuar..');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	
    	if(!$obj_cliente){
    		$this->get('session')->setFlash('info', 'El cliente seleccionado no existe.');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    	
    	$obj_sede = null;
    	
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    		$empresa = $obj_sede->getEmpresa();
    	}else{
    		$sede = 1;
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    		$empresa = $obj_sede->getEmpresa();
    	}

    	
    	if(is_object($obj_sede)){
    		$con_sede = "AND f.sede =".$sede;
    	}else{
    		$con_sede = "";
    	}
    	
    	if(trim($tipo)){
    		$con_tipo = "AND f.grupo ='".$tipo."'";
    	}else{
    		$con_tipo = "";
    	}
    	
    	$dql= " SELECT
			    	f.id,
                    f.prefijo,
                    f.consecutivo,
			    	p.id as paciente,
			    	p.tipoId,
			    	p.identificacion,
                    p.fN,
			    	f.fecha,
			    	p.priNombre,
			    	p.segNombre,
			    	p.priApellido,
			    	p.segApellido,
                    c.nombre,
                    c.cups,
			    	f.concepto,
			    	f.subtotal AS valor,
			    	f.copago,
			    	f.estado
    			FROM
    				ParametrizarBundle:Facturacion f
                JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
                    f.final = 1 AND
			    	f.cliente = :cliente ".
			    	$con_sede." ".
			    	$con_tipo."
		    	ORDER BY
		    		f.fecha ASC";
			    	
			    	$query = $em->createQuery($dql);
			    	
			    	$query->setParameter('inicio', $f_inicio.' 00:00:00');
			    	$query->setParameter('fin', $f_fin.' 23:59:00');
			    	$query->setParameter('cliente', $cliente);
    	
    	$entity = $query->getResult();
    	 
    	$html = $this->renderView('AdminBundle:Reporte:cta_cobro.pdf.twig', array(
    			'entities' => $entity,
    			'empresa' => $empresa,
    			'cliente' => $obj_cliente,
    			'f_i' => $f_inicio,
    			'f_f' => $f_fin
    	));
    	 
    	$this->get('io_tcpdf')->dir = $obj_sede->getDireccion();
    	$this->get('io_tcpdf')->ciudad = $obj_sede->getCiudad();
    	$this->get('io_tcpdf')->tel = $obj_sede->getTelefono();
    	$this->get('io_tcpdf')->mov = $obj_sede->getMovil();
    	$this->get('io_tcpdf')->mail = $obj_sede->getEmail();
    	$this->get('io_tcpdf')->sede = $obj_sede->getnombre();
    	$this->get('io_tcpdf')->empresa = $obj_sede->getEmpresa()->getNombre();
    	 
    	$this->get('io_tcpdf')->SetMargins(3, 10, 3);
    
    	return $this->get('io_tcpdf')->quick_pdf($html, 'Cta_Cobro_'.$obj_sede->getNombre().'.pdf', 'I');
    }
    
	public function buscarRipsAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	
    	$sedes = $em->getRepository("ParametrizarBundle:Sede")->findAll();
    	$clientes = $em->getRepository("ParametrizarBundle:Cliente")->findAll();
    	
    	$plantilla = 'AdminBundle:Factura:rips.html.twig';
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Buscar rips");    	

    	return $this->render($plantilla, array(
    			'sedes' => $sedes,
    			'clientes' => $clientes
    	));
    }
    
    
    public function ripsFilesAction()
    {
    	$request = $this->get('request');
    	 
    	$sede = $request->request->get('sede');
    	$cliente = $request->request->get('cliente');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	$factura = $request->request->get('factura');
    	
    	if(trim($f_inicio)){
    		$desde = explode('/',$f_inicio);
    	
    		if(!checkdate($desde[1],$desde[0],$desde[2])){
    			$this->get('session')->setFlash('info', 'La fecha de inicio ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl('factura_rips_search'));
    		}
    		else{
    			$f_inicio = $desde[2].'-'.$desde[1].'-'.$desde[0];
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de inicio no puede estar en blanco.');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}
    	 
    	if(trim($f_fin)){
    		$hasta = explode('/',$f_fin);
    	
    		if(!checkdate($hasta[1],$hasta[0],$hasta[2])){
    			$this->get('session')->setFlash('info', 'La fecha de finalización ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl('factura_rips_search'));
    		}else {
    			$f_fin = $hasta[2].'-'.$hasta[1].'-'.$hasta[0];
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de finalización no puede estar en blanco.');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}
    
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	if(is_numeric(trim($cliente))){
    		$cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe seleccionar un cliente para continuar..');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}

    	if(!$cliente){
    		$this->get('session')->setFlash('info', 'El cliente seleccionado no existe.');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}

    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    	}else{
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find(1);
    		
    		if(!$obj_sede){
    			$this->get('session')->setFlash('info', 'La sede seleccionada no existe.');
    			return $this->redirect($this->generateUrl('factura_rips_search'));
    		}
    	}
    	
    	if(!trim($factura)){
    		$this->get('session')->setFlash('info', 'El No. de factura no puede estar vacio..');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}
    	
    	$dir = $this->container->getParameter('dlaser.directorio.rips');
    	
    	array_map('unlink', glob($dir."*.zip"));
    	array_map('unlink', glob($dir."*.txt"));

    	$us = $this->fileUS($cliente, $f_inicio, $f_fin);    	
    	$ap = $this->fileAP($cliente, $f_inicio, $f_fin, $factura);
    	$ac = $this->fileAC($cliente, $f_inicio, $f_fin, $factura);
    	$ad = $this->fileAD($cliente, $f_inicio, $f_fin, $factura);
    	$af = $this->fileAF($cliente, $f_inicio, $f_fin, $factura, $obj_sede);
    	
    	$this->fileCt($us, $ap, $ac, $ad, $af);

    	$zip = new ZipArchive;
    	if ($zip->open($dir.$hasta[1].$hasta[2].".zip") === TRUE) {
    		
    		foreach (glob("*.txt") as $filename) {
    			$zip->addFile($dir.$filename, $filename);
    		}
    		
    		$zip->close();
    		
    	} else {
    		echo 'failed';
    	}
    	
		$abririps=$dir.$hasta[1].$hasta[2].".zip";
                    
		$fsize = filesize($abririps);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
        header("Content-Type: application/x-gzip");
        header("Content-Disposition: attachment; filename=\"".basename($abririps)."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$fsize);
                    
        ob_clean();
        flush();
        readfile( $abririps );
    }

    private function fileUS($cliente, $f_inicio, $f_fin, $obj_sede){
    	
    	$dir = $this->container->getParameter('dlaser.directorio.rips');
    	
    	$em = $this->getDoctrine()->getEntityManager();    	
    	
    	$dql= " SELECT
    				DISTINCT
    				p.identificacion AS id,
			    	p.tipoId,			    	
			    	p.priApellido,
			    	p.segApellido,
			    	p.priNombre,
			    	p.segNombre,
			    	p.fN,
			    	p.sexo,
			    	p.depto,
			    	p.mupio,
			    	p.zona,
			    	cli.codEps
		    	FROM
		    		ParametrizarBundle:Facturacion f
		    	JOIN
		    	   	f.paciente p
		    	JOIN
		    		f.cliente cli
    			JOIN
		    		f.cargo c
		    	WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
			    	f.estado = :estado AND
			    	f.cliente = :cliente AND
    				f.sede = :sede
		    	ORDER BY
		    		f.fecha ASC";
    	
    	$query = $em->createQuery($dql);
    	 
    	$query->setParameter('inicio', $f_inicio.' 00:00:00');
    	$query->setParameter('fin', $f_fin.' 23:59:00');
    	$query->setParameter('cliente', $cliente->getId());
    	$query->setParameter('estado', 'G');
    	$query->setParameter('sede', $obj_sede->getId());
    	 
    	$entity = $query->getArrayResult();
    	
    	$gestor = fopen($dir."US.txt", "w+"); 
    	
    	if (!$gestor){
    		$this->get('session')->setFlash('info', 'No se puede crear txt.');
    		return $this->redirect($this->generateUrl('factura_rips_search'));
    	}    	
    	
    	$date2 = new \DateTime("now");
    	
    	foreach ($entity as $value){
    		$fn = new \DateTime($value['fN']);
    		$interval = $fn->diff($date2);
    		fwrite($gestor, "".$value['tipoId'].",".$value['id'].",".$value['codEps'].",1,".$value['priApellido'].",".$value['segApellido'].",".$value['priNombre'].",".$value['segNombre'].",".$interval->format('%y').",1,".$value['sexo'].",".$value['depto'].",".$value['mupio'].",".$value['zona']."\r\n");
    	} 	
    	
    	return count($entity);
    }
    
    private function fileAP($cliente, $f_inicio, $f_fin, $obj_sede, $rips='I'){
         
        $dir = $this->container->getParameter('dlaser.directorio.rips');
         
        $em = $this->getDoctrine()->getEntityManager();
         
        $dql= " SELECT      
                    p.identificacion AS id,
                    p.tipoId,
                    f.prefijo AS pre,
                    f.consecutivo AS idf,
                    f.fecha,
                    f.autorizacion,
                    c.cups,
                    f.subtotal
                FROM
                    ParametrizarBundle:Facturacion f
                JOIN
                    f.paciente p
                JOIN
                    f.cargo c
                WHERE
                    f.fecha > :inicio AND
                    f.fecha <= :fin AND
                    f.estado = :estado AND
                    f.cliente = :cliente AND
                    c.cups != '890202' AND
                    f.sede = :sede
                ORDER BY
                    f.fecha ASC";
         
        $query = $em->createQuery($dql);
    
        $query->setParameter('inicio', $f_inicio.' 00:00:00');
        $query->setParameter('fin', $f_fin.' 23:59:00');
        $query->setParameter('cliente', $cliente->getId());
        $query->setParameter('estado', 'G');
        $query->setParameter('sede', $obj_sede->getId());
    
        $entity = $query->getArrayResult();
         
        $gestor = fopen($dir."AP.txt", "w+");
         
        if (!$gestor){
            $this->get('session')->setFlash('info', 'No se puede crear txt.');
            return $this->redirect($this->generateUrl('factura_rips_search'));
        }
        
        if  ($rips == "I")
        { 

        foreach ($entity as $value){
            
            $fecha = new \DateTime($value['fecha']);
            
            fwrite($gestor, "".$value['pre'].$value['idf'].",768340706001,".$value['tipoId'].",".$value['id'].",".$fecha->format('d/m/Y').",".$value['autorizacion'].",".$value['cups'].",1,1,1,,,,,".$value['subtotal']."\r\n");
            }
         
        return count($entity);
        }elseif ($rips == "G") {
            foreach ($entity as $value){
            
            $fecha = new \DateTime($value['fecha']);
            
            fwrite($gestor, "".$factura.",768340706001,".$value['tipoId'].",".$value['id'].",".$fecha->format('d/m/Y').",".$value['autorizacion'].",".$value['cups'].",1,1,1,,,,,".$value['valor']."\r\n");
            }
        return count($entity);
        }

    }
    
    private function fileAC($cliente, $f_inicio, $f_fin){
    
        $dir = $this->container->getParameter('dlaser.directorio.rips');
    
        $em = $this->getDoctrine()->getEntityManager();
    
        $dql= " SELECT
                    p.identificacion AS id,
                    p.tipoId,
                    f.prefijo AS pre,
                    f.consecutivo AS idf,
                    f.fecha,
                    f.autorizacion,
                    c.cups,
                    f.subtotal,
                    f.copago
                FROM
                    ParametrizarBundle:Facturacion f
                JOIN
                    f.paciente p
                JOIN
                    f.cargo c
                WHERE
                    f.fecha > :inicio AND
                    f.fecha <= :fin AND
                    f.estado = :estado AND
                    f.cliente = :cliente AND
                    c.cups = '890202'
                ORDER BY
                    f.fecha ASC";
    
        $query = $em->createQuery($dql);
    
        $query->setParameter('inicio', $f_inicio.' 00:00:00');
        $query->setParameter('fin', $f_fin.' 23:59:00');
        $query->setParameter('cliente', $cliente->getId());
        $query->setParameter('estado', 'G');
    
        $entity = $query->getArrayResult();
        
        if ($entity) {
            
    
            $gestor = fopen($dir."AC.txt", "w+");
        
            if (!$gestor){
                $this->get('session')->setFlash('info', 'No se puede crear txt.');
                return $this->redirect($this->generateUrl('factura_rips_search'));
            }
        
            foreach ($entity as $value){
        
                $fecha = new \DateTime($value['fecha']);
        
                fwrite($gestor, "".$value['pre'].$value['idf'].",768340706001,".$value['tipoId'].",".$value['id'].",".$fecha->format('d/m/Y').",".$value['autorizacion'].",".$value['cups'].",10,15,,,,,1,".$value['valor'].".00,".$value['copago'].".00,".($value['valor']-$value['copago']).".00\r\n");
            }
            
        }
    
        return count($entity);
    }
    
     private function fileAD($cliente, $f_inicio, $f_fin, $obj_sede,$rips='I'){
    
        $dir = $this->container->getParameter('dlaser.directorio.rips');
    
        $em = $this->getDoctrine()->getEntityManager();
        
        $dql= " SELECT
                    c.cups,
                    f.prefijo AS pre,
                    f.consecutivo AS idf,
                    f.id,
                    f.subtotal,
                    f.copago
                FROM
                    ParametrizarBundle:Facturacion f
                JOIN
                    f.paciente p
                JOIN
                    f.cargo c
                WHERE
                    f.fecha > :inicio AND
                    f.fecha <= :fin AND
                    f.estado = :estado AND
                    f.cliente = :cliente AND
                    c.cups != '890202' AND
                    f.sede = :sede ";     
        
        $query = $em->createQuery($dql);
        
        $query->setParameter('inicio', $f_inicio.' 00:00:00');
        $query->setParameter('fin', $f_fin.' 23:59:00');
        $query->setParameter('cliente', $cliente->getId());
        $query->setParameter('estado', 'G');
        $query->setParameter('sede', $obj_sede->getId());
        
        $entity2 = $query->getArrayResult();

        $gestor = fopen($dir."AD.txt", "w+");
    
        if (!$gestor){
            $this->get('session')->setFlash('info', 'No se puede crear txt.');
            return $this->redirect($this->generateUrl('factura_rips_search'));
        }
        
        if($rips=="G")
        {
               $num_px = 0;
               $val_px = 0;
               $copago_px = 0;
               foreach ($entity2 as $value){
               $val_px+=$value['valor'];
               $copago_px+=$value['copago'];
               $num_px++;
            }   
         
            fwrite($gestor, "".$value['pre'].$value['idf'].",768340706001,02,".$num_px.",0,".($val_px-$copago_px).".00\r\n");
           
           return 1;
           
        }else
        if($rips=="I")
        {
           $num_px = 0;
           foreach ($entity2 as $value)
           {
               fwrite($gestor, "".$value['pre'].$value['idf'].",768340706001,02,".$num_px.",0,".($value['subtotal']-$value['copago']).".00\r\n");
           $num_px++;
          } 
          return count($entity2);   
            
        }
        
    }
    
    private function fileAF($cliente, $f_inicio, $f_fin, $obj_sede,$rips){
    
        $dir = $this->container->getParameter('dlaser.directorio.rips');
    
        $em = $this->getDoctrine()->getEntityManager();
    
        if($rips=="G"){
            
            $dql= " SELECT
                        SUM (f.subtotal) AS valor,
                        SUM (f.copago) AS copago
                    FROM
                        ParametrizarBundle:Facturacion f
                    JOIN
                        f.cargo c
                    WHERE
                        f.fecha > :inicio AND
                        f.fecha <= :fin AND
                        f.estado = :estado AND
                        f.cliente = :cliente AND
                        f.sede = :sede ";

            $query = $em->createQuery($dql);
    
            $query->setParameter('inicio', $f_inicio.' 00:00:00');
            $query->setParameter('fin', $f_fin.' 23:59:00');
            $query->setParameter('cliente', $cliente->getId());
            $query->setParameter('estado', 'G');
            $query->setParameter('sede', $obj_sede->getId());
    
            $entity = $query->getArrayResult();
    
            $gestor = fopen($dir."AF.txt", "w+");
    
            if (!$gestor){
                $this->get('session')->setFlash('info', 'No se puede crear txt.');
                return $this->redirect($this->generateUrl('factura_rips_search'));
            }
        
            $fecha = new \DateTime('now');
            $inicio = new \DateTime($f_inicio);
            $fin = new \DateTime($f_fin);
            
            $contrato = $em->getRepository("ParametrizarBundle:Contrato")->findOneBy(array('cliente' => $cliente->getId(), 'sede' => $obj_sede->getId()));
        
            fwrite($gestor, "768340706001,CENTRO DE IMAGENES Y HEMODINAMIA CIMHE IPS,NI,900225202,".$factura.",".$fecha->format('d/m/Y').",".$inicio->format('d/m/Y').",".$fin->format('d/m/Y').",".$cliente->getCodEps().",".$cliente->getRazon().",,ISS 2001 + ".$contrato->getPorcentaje()."%,,".$entity[0]['copago'].".00,0.00,0.00,".($entity[0]['valor']-$entity[0]['copago']).".00\r\n");
    
        }elseif($rips=="I"){
        
            $dql= " SELECT
                      f.id,
                      f.prefijo AS pre,
                      f.consecutivo AS idf,
                      f.fecha,
                      f.subtotal,
                      f.copago
                    FROM
                        ParametrizarBundle:Facturacion f
                    JOIN
                        f.cargo c
                    WHERE
                        f.fecha > :inicio AND
                        f.fecha <= :fin AND
                        f.estado = :estado AND
                        f.cliente = :cliente AND
                        f.sede = :sede ";  
    
            $query = $em->createQuery($dql);
    
            $query->setParameter('inicio', $f_inicio.' 00:00:00');
            $query->setParameter('fin', $f_fin.' 23:59:00');
            $query->setParameter('cliente', $cliente->getId());
            $query->setParameter('estado', 'G');
            $query->setParameter('sede', $obj_sede->getId());
        
            $entity = $query->getArrayResult();
    
            $gestor = fopen($dir."AF.txt", "w+");
    
            if (!$gestor){
                $this->get('session')->setFlash('info', 'No se puede crear txt.');
                return $this->redirect($this->generateUrl('factura_rips_search'));
            }

            $inicio = new \DateTime($f_inicio);
            $fin = new \DateTime($f_fin);
            
            $contrato = $em->getRepository("ParametrizarBundle:Contrato")->findOneBy(array('cliente' => $cliente->getId(), 'sede' => $obj_sede->getId()));
        
            foreach ($entity as $value){
                $fecha = new \DateTime($value['fecha']);
                fwrite($gestor, "768340706001,CENTRO DE IMAGENES Y HEMODINAMIA CIMHE IPS,NI,900225202,".$value['pre'].$value['idf'].",".$fecha->format('d/m/Y').",".$inicio->format('d/m/Y').",".$fin->format('d/m/Y').",".$cliente->getCodEps().",".$cliente->getRazon().",,ISS 2001 + ".$contrato->getPorcentaje()."%,,".$value['copago'].".00,0.00,0.00,".($value['subtotal']-$value['copago']).".00\r\n");
            }
        }
    
        return count($entity);
    }
    
     private function fileCt($us, $ap, $ac, $ad, $af){
         
        $dir = $this->container->getParameter('dlaser.directorio.rips');
         
        $gestor = fopen($dir."CT.txt", "w+");
         
        if (!$gestor){
            $this->get('session')->setFlash('info', 'No se puede crear txt.');
            return $this->redirect($this->generateUrl('factura_rips_search'));
        }
            
        $fecha = new \DateTime('now');
        
        fwrite($gestor, "768340706001,".$fecha->format('d/m/Y').",US,".$us."\r\n");
        fwrite($gestor, "768340706001,".$fecha->format('d/m/Y').",AF,".$af."\r\n");
        fwrite($gestor, "768340706001,".$fecha->format('d/m/Y').",AD,".$ad."\r\n");
        if ($ac > 0) {
            fwrite($gestor, "768340706001,".$fecha->format('d/m/Y').",AC,".$ac."\r\n");
        }
        fwrite($gestor, "768340706001,".$fecha->format('d/m/Y').",AP,".$ap."\r\n");
         
        return true;
    }
    
    public function facturacionPreviaAction()
    {
    	$request = $this->get('request');
    
    	$sede = $request->request->get('sede');
    	$cliente = $request->request->get('cliente');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	$tipo = $request->request->get('tipo');
        $rips = $request->request->get('rips');
    	 
    	$em = $this->getDoctrine()->getEntityManager();
    
    	if(is_numeric(trim($cliente))){
    		$obj_cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	}else{
    		$this->get('session')->setFlash('info', 'Debe ingresar un cliente valido..');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    
    	if(!$obj_cliente){
    		$this->get('session')->setFlash('info', 'El cliente seleccionado no existe.');
    		return $this->redirect($this->generateUrl('factura_cliente_list'));
    	}
    
    	$obj_sede = null;
    	 
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    		$con_sede = "AND f.sede =".$sede;
    		$empresa = $obj_sede->getEmpresa();
    	}else{
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find('1');
    		$empresa = $obj_sede->getEmpresa();
    		$con_sede = "";
    	}
    	
    	if(trim($tipo)){
    		$con_tipo = "AND c.tipo ='".$tipo."'";
    	}else{
    		$con_tipo = "";
    	}
    
    	$dql= " SELECT
    				SUM(f.valor) AS valor,
    				SUM(f.copago) AS copago
    			FROM
    				ParametrizarBundle:Factura f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin AND
    				f.estado = :estado AND
			    	f.cliente = :cliente ".
			    	$con_sede.
    				$con_tipo;
    	 
    	$query = $em->createQuery($dql);
    
    	$query->setParameter('inicio', $f_inicio.' 00:00:00');
    	$query->setParameter('fin', $f_fin.' 23:59:00');
    	$query->setParameter('cliente', $cliente);
    	$query->setParameter('estado', 'I');
    
    	$valor = $query->getSingleResult();
    	
    	if($con_sede=="") $sedes = 0;
    	else $sedes = $obj_sede->getId();
    	
    	$entity = new Facturacion();
    	
    	$dql= " SELECT
    				MAX(f.consecutivo) AS id
    			FROM
    				ParametrizarBundle:Facturacion f
				WHERE
					f.prefijo = 'T'";
    	
    	$query = $em->createQuery($dql);
    	
    	$id = $query->getSingleResult();
    	
    	if ($id){
    		$id = 1 + $id['id'];
    	}else{
    		$id=1;
    	}
    	
    	$entity->setPrefijo('T');
    	$entity->setConsecutivo($id);
    	$entity->setInicio($f_inicio);
    	$entity->setFin($f_fin);
    	$entity->setSedes($sedes);
    	$entity->setConcepto('');
    	$entity->setSubtotal($valor['valor']);
    	$entity->setCopago($valor['copago']);
    	$entity->setNota('Copago $'.$valor['copago']);
    	$entity->setIva(0);
    	
    	$form   = $this->createForm(new FacturacionType(), $entity);
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Final nuevo");
    	
    	if(!trim($tipo)){
    		$tipo='N';
    	}
    	
    	return $this->render('AdminBundle:Factura:factura_previa.html.twig', array(
    			'valores' => $valor,
    			'cliente' => $obj_cliente,
    			'sede' => $obj_sede,
    			'f_i' => $f_inicio,
    			'f_f' => $f_fin,
    			'tipo' => $tipo,
                'rips' => $rips,
    			'form'   => $form->createView()
    	));
    }
    
    
    public function facturacionSaveAction($cliente, $sede, $tipo)
    {
        
        $request = $this->get('request');
        $rips = $request->request->get('rips');

        $entity  = new Facturacion();
		$em = $this->getDoctrine()->getEntityManager();	
    
    	$request = $this->getRequest();
    	$form    = $this->createForm(new FacturacionType(), $entity);
    	$form->bindRequest($request);
    	
    	$registro = $form->getData();
    	
    	$inicio = new \DateTime($registro->getInicio());
    	$fin = new \DateTime($registro->getFin());
    	
    	$cliente = $em->getRepository('ParametrizarBundle:Cliente')->find($cliente);
    	$sede = $em->getRepository('ParametrizarBundle:Sede')->find($sede);
    
    	if ($registro) {

    		$entity->setPrefijo($registro->getPrefijo());
    		$entity->setConsecutivo($registro->getConsecutivo());
    		$entity->setFecha(new \DateTime('now'));
    		$entity->setInicio($inicio);
    		$entity->setFin($fin);
    		$entity->setEstado('G');
    		$entity->setCliente($cliente);
    		$entity->setSede($sede);
    		   
    		$em->persist($entity);
    		$em->flush();
    
    		$this->get('session')->setFlash('info', 'La información de la factura ha sido registrada éxitosamente.');
    
    		return $this->redirect($this->generateUrl('factura_final_show',array("id"=>$entity->getId(), "tipo"=>$tipo, "rips"=>$rips)));
    
    	}
    
        return $this->render('AdminBundle:Factura:factura_previa.html.twig', array(
                'cliente' => $cliente,
                'sede' => $sede,
                'f_i' => $inicio,
                'f_f' => $fin,
                'tipo' => $tipo,
                'rips' => $rips,
                'form'   => $form->createView()
        ));
    
    }
    
    
    public function facturacionShowAction($id, $tipo, $rips)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    
    	$factura = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    
    
    	if (!$factura) {
    		throw $this->createNotFoundException('La factura solicitada no esta disponible.');
    	}
    	
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Factura venta");
    
        return $this->render('AdminBundle:Factura:factura_final_show.html.twig', array(
                'entity'  => $factura,
                'tipo' => $tipo,
                'rips' => $rips
        ));
    }
    
    public function facturacionImprimirAction($id)
    {
    	set_time_limit(0);
    	
    	$em = $this->getDoctrine()->getEntityManager();
    
    	$entity = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    	 
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura a imprimir no esta disponible.');
    	}

    	if ($entity->getFinal() == 1){
    	    $cliente = $entity->getCliente();
    	    $paciente = null;
    	}else{
    	    $paciente = $entity->getPaciente();
    	    $cliente = null;
    	}
    	
    	$sede = $entity->getSede();
    
    	$html = $this->renderView('AdminBundle:Factura:factura_venta.pdf.twig',
    			array('entity' 	=> $entity,
    					'cliente'	=> $cliente,
    			        'paciente'	=> $paciente,
    					'sede'      => $sede
    			));
    	 
    	 
    	$this->get('io_tcpdf')->dir = $sede->getDireccion();
    	$this->get('io_tcpdf')->ciudad = $sede->getCiudad();
    	$this->get('io_tcpdf')->tel = $sede->getTelefono();
    	$this->get('io_tcpdf')->mov = $sede->getMovil();
    	$this->get('io_tcpdf')->mail = $sede->getEmail();
    	$this->get('io_tcpdf')->sede = $sede->getnombre();
    	$this->get('io_tcpdf')->empresa = $sede->getEmpresa()->getNombre();
    	
    	$this->get('io_tcpdf')->formato = 'P5';
    	$this->get('io_tcpdf')->orientacion = 'L';
    
    	return $this->get('io_tcpdf')->quick_pdf($html, 'factura_venta_'.$entity->getId().'.pdf', 'I');
    }
    
    
    public function facturacionRipsAction(){
        
        $request = $this->get('request');
        
        $sede = $request->request->get('sede');
        $cliente = $request->request->get('cliente');
        $f_inicio = $request->request->get('f_inicio');
        $f_fin = $request->request->get('f_fin');

    	$em = $this->getDoctrine()->getEntityManager();

    	$cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	
    	$desde = explode('/',$f_inicio);
    	$hasta = explode('/',$f_fin);

    	$f_inicio = $desde[0]."-".$desde[1]."-".$desde[2];
    	$f_fin = $hasta[0]."-".$hasta[1]."-".$hasta[2];
    	
    	$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    	 
    	$dir = $this->container->getParameter('dlaser.directorio.rips');
    	 
    	array_map('unlink', glob($dir."*.zip"));
    	array_map('unlink', glob($dir."*.txt"));
    
    	$us = $this->fileUS($cliente, $f_inicio, $f_fin, $obj_sede);
    	$ap = $this->fileAP($cliente, $f_inicio, $f_fin, $obj_sede, 'I');
    	$ac = $this->fileAC($cliente, $f_inicio, $f_fin);
    	$ad = $this->fileAD($cliente, $f_inicio, $f_fin, $obj_sede, 'I');
    	$af = $this->fileAF($cliente, $f_inicio, $f_fin, $obj_sede, 'I');
    	 
    	$this->fileCt($us, $ap, $ac, $ad, $af);
    	
    	$zip = new ZipArchive;
    	
    	if ($zip->open('rips/'.$hasta[1].'_'.$hasta[0].".zip", ZipArchive::CREATE) === TRUE) {        	 

    		foreach (glob($dir."*.txt") as $filename) {
    			$zip->addFile($filename, basename($filename));
    		}
    		
    		$zip->close();
    		
    	} else {
    		$this->get('session')->setFlash('error', 'El archivo comprimido no ha podido ser creado.');
    		
    		return $this->redirect($this->generateUrl('factura_final_show',array("id"=>$entity->getId(), "tipo"=>$tipo, "rips"=>$rips)));
    	}
    	 
    	$abririps=$dir.$hasta[1].'_'.$hasta[0].".zip";
    
    	$fsize = filesize($abririps);

    	header("Pragma: public");
    	header("Expires: 0");
    	header('Content-Description: File Transfer');
    	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Cache-Control: private",false);
    	header("Content-Type: application/zip");
    	header("Content-Disposition: attachment; filename=\"".basename($abririps)."\";" );
    	header("Content-Transfer-Encoding: binary");
    	header("Content-Length: \".$fsize.\"");

    	readfile( $abririps );
    	exit;
    }
    
    public function reporteFacturaAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	$sedes = $em->getRepository("ParametrizarBundle:Sede")->findAll();
    	$clientes = $em->getRepository("ParametrizarBundle:Cliente")->findAll();
    	 
    	$plantilla = 'AdminBundle:Factura:reporte_factura.html.twig';
    	 
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Generar Reporte");
    
    	return $this->render($plantilla, array(
    			'sedes' => $sedes,
    			'clientes' => $clientes
    	));
    }
    
    
    /**
     * @uses Muestra el listado generado a partir de los parametros de consultas definidos.
     *
     * @param Pasados por POST.
     */
    public function gstReporteFacturaAction()
    {
    	 
    	$request = $this->get('request');
    	 
    	$sede = $request->request->get('sede');
    	$cliente = $request->request->get('cliente');
    	$f_inicio = $request->request->get('f_inicio');
    	$f_fin = $request->request->get('f_fin');
    	
    	$url = 'factura_genera_reporte';
    	 
    	if(trim($f_inicio)){
    		
    		$desde = explode('-',$f_inicio);
    		
    		if(!checkdate($desde[1],$desde[2],$desde[0])){
    			$this->get('session')->setFlash('info', 'La fecha de inicio ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl($url));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de inicio no puede estar en blanco.');
    		return $this->redirect($this->generateUrl($url));
    	}
    	 
    	if(trim($f_fin)){
    		$hasta = explode('-',$f_fin);
    
    		if(!checkdate($hasta[1],$hasta[2],$hasta[0])){
    			$this->get('session')->setFlash('info', 'La fecha de finalización ingresada es incorrecta.');
    			return $this->redirect($this->generateUrl($url));
    		}
    	}else{
    		$this->get('session')->setFlash('info', 'La fecha de finalización no puede estar en blanco.');
    		return $this->redirect($this->generateUrl($url));
    	}
    	 
    	$em = $this->getDoctrine()->getEntityManager();
    	 
    	if(is_numeric(trim($sede))){
    		$obj_sede = $em->getRepository("ParametrizarBundle:Sede")->find($sede);
    	}else{
    		$obj_sede['nombre'] = 'Todas las sedes.';
    		$obj_sede['id'] = '';
    	}
    	 
    	if(is_numeric(trim($cliente))){
    		$obj_cliente = $em->getRepository("ParametrizarBundle:Cliente")->find($cliente);
    	}else{
    		$obj_cliente['nombre'] = 'Todos los clientes.';
    		$obj_cliente['id'] = '';
    	}    	
    	 
    	if(!$obj_cliente){
    		$this->get('session')->setFlash('info', 'El cliente seleccionado no existe.');
    		return $this->redirect($this->generateUrl($url));
    	}
    	 
    	if(is_object($obj_sede)){
    		$con_sede = "AND f.sede =".$sede;
    	}else{
    		$con_sede = "";
    	}
    	
    	if(is_object($obj_cliente)){
    		$con_cliente = "AND f.cliente =".$cliente;
    	}else{
    		$con_cliente = "";
    	}
    	 
    	$dql= " SELECT
			    	f.id,
                    f.prefijo,
                    f.consecutivo,
			    	p.id as paciente,
			    	p.tipoId,
			    	p.identificacion,
			    	f.fecha,
			    	f.autorizacion,
                    f.concepto,
			    	p.priNombre,
			    	p.segNombre,
			    	p.priApellido,
			    	p.segApellido,
                    p.fN,
			    	c.cups,
			    	f.subtotal,
			    	f.copago,
			    	f.estado
    			FROM
    				ParametrizarBundle:Facturacion f
    			JOIN
    				f.cargo c
    			JOIN
    				f.paciente p
    			JOIN
    				f.cliente cli
    			WHERE
			    	f.fecha > :inicio AND
			    	f.fecha <= :fin ".
			    		$con_cliente." ".
    			    	$con_sede."
		    	ORDER BY
		    		f.fecha ASC";
    
    	$query = $em->createQuery($dql);
    	 
    	$query->setParameter('inicio', $desde[0]."-".$desde[1]."-".$desde[2].' 00:00:00');
    	$query->setParameter('fin', $hasta[0]."-".$hasta[1]."-".$hasta[2].' 23:59:00');
    	//$query->setParameter('cliente', $cliente);
    	 
    	$entity = $query->getResult();
    	 
    	$user = $this->get('security.context')->getToken()->getUser();
    	 
    	if($user->getPerfil() == 'ROLE_ADMIN') {
    		$plantilla = 'AdminBundle:Reporte:actividades_cliente.html.twig';
    	}else {
    		$plantilla = 'AdminBundle:Reporte:actividades_cliente.html.twig';
    	}
    	 
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("factura_arqueo"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	$breadcrumbs->addItem("Actividades");
    
    	return $this->render($plantilla, array(
    			'entities' => $entity,
    			'sede' => $obj_sede,
    			'cliente' => $obj_cliente,
    			'f_i' => $desde[2]."/".$desde[1]."/".$desde[0],
    			'f_f' => $hasta[2]."/".$hasta[1]."/".$hasta[0]
    	));
    }
    
    public function facturacionPacienteShowAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    
    	$factura = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    
    
    	if (!$factura) {
    		throw $this->createNotFoundException('La factura solicitada no esta disponible.');
    	}
    
    	return $this->render('AdminBundle:Factura:factura_paciente_show.html.twig', array(
    			'entity'  => $factura
    	));
    }
    
    public function facturacionPacienteImprimirAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    
    	$entity = $em->getRepository('ParametrizarBundle:Facturacion')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura a imprimir no esta disponible.');
    	}
    
    	$paciente = $entity->getPaciente();
    	$sede = $entity->getSede();
    
    	$html = $this->renderView('AdminBundle:Factura:factura_venta_paciente.pdf.twig',
    			array('entity' 	=> $entity,
    					'cliente'	=> $paciente,
    					'sede'=>$sede
    			));
    
    	$this->get('io_tcpdf')->dir = $sede->getDireccion();
    	$this->get('io_tcpdf')->ciudad = $sede->getCiudad();
    	$this->get('io_tcpdf')->tel = $sede->getTelefono();
    	$this->get('io_tcpdf')->mov = $sede->getMovil();
    	$this->get('io_tcpdf')->mail = $sede->getEmail();
    	$this->get('io_tcpdf')->sede = $sede->getnombre();
    	$this->get('io_tcpdf')->empresa = $sede->getEmpresa()->getNombre();
    	
    	$this->get('io_tcpdf')->formato = 'P5';
    	$this->get('io_tcpdf')->orientacion = 'L';
    
    	return $this->get('io_tcpdf')->quick_pdf($html, 'Factura_Venta_CC'.$entity->getId().'.pdf', 'I');
    }
    
    public function facturacionPacienteEditAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('ParametrizarBundle:Factura')->find($id);
    
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura solicitada no existe');
    	}
    	$user = $this->get('security.context')->getToken()->getUser();
    		
    	if ($user->getPerfil() == 'ROLE_ADMIN') {
    		$editForm = $this->createForm(new AdmisionType(), $entity);
    	}
    	else{
    		$editForm = $this->createForm(new AdmisionAuxType(), $entity);
    	}
    
    	$breadcrumbs = $this->get("white_october_breadcrumbs");
    	$breadcrumbs->addItem("Inicio", $this->get("router")->generate("agenda_list"));
    	$breadcrumbs->addItem("Factura", $this->get("router")->generate("factura_search"));
    	//$breadcrumbs->addItem("Detalle ",$this->get("router")->generate("factura_show",array("id" => $id)));
    	$breadcrumbs->addItem("Modificar admisión");
    	 
    	return $this->render('AdminBundle:Factura:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    	));
    }
    
    public function facturacionPacienteUpdateAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity = $em->getRepository('ParametrizarBundle:Factura')->find($id);
    	 
    	if (!$entity) {
    		throw $this->createNotFoundException('La factura solicitada no existe.');
    	}
    	$request = $this->getRequest();
    	$user = $this->get('security.context')->getToken()->getUser();
    	if($user->getPerfil() == 'ROLE_ADMIN'){
    		$editForm = $this->createForm(new AdmisionType(), $entity);
    	}else{
    		$editForm = $this->createForm(new AdmisionAuxType(), $entity);
    	}
    	$editForm->bindRequest($request);
    	 
    
    	if ($editForm->isValid()) {
    
    		$cliente = $em->getRepository('ParametrizarBundle:Cliente')->find($entity->getCliente()->getId());
    		$contrato = $em->getRepository('ParametrizarBundle:Contrato')->findOneBy(array('sede' => $entity->getSede()->getId(), 'cliente' => $cliente->getId()));
    
    		if(!$contrato){
    			$this->get('session')->setFlash('info', 'El cliente seleccionado no tiene contrato con la sede, por favor verifique y vuelva a intentarlo.');
    			return $this->redirect($this->generateUrl('factura_edit', array('id' => $id)));
    		}
    
    		$actividad = $em->getRepository('ParametrizarBundle:Actividad')->findOneBy(array('cargo' => $entity->getCargo()->getId(), 'contrato' => $contrato->getId()));
    
    		if(!$user->getPerfil() == 'ROLE_ADMIN'){
    			if($actividad->getPrecio()){
    				$valor = $actividad->getPrecio();
    			}else{
    				$valor = round(($entity->getCargo()->getValor()+($entity->getCargo()->getValor()*$contrato->getPorcentaje()/100)));
    			}
    	   
    			$entity->setValor($valor);
    		}
    		$entity->setCliente($cliente);
    
    		$em->persist($entity);
    		$em->flush();
    
    		$this->get('session')->setFlash('info', 'La información de la admisión ha sido modificada éxitosamente.');
    		return $this->redirect($this->generateUrl('factura_edit', array('id' => $id)));
    	}
    	return $this->render('AdminBundle:Factura:edit.html.twig', array(
    			'entity'      => $entity,
    			'edit_form'   => $editForm->createView(),
    	));
    }
    
}
