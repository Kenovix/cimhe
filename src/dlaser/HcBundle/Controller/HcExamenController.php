<?php
namespace dlaser\HcBundle\Controller;

use dlaser\HcBundle\Entity\HcMedicamento;
use dlaser\HcBundle\Entity\HcExamen;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use dlaser\HcBundle\Entity\Hc;
use dlaser\HcBundle\Form\HcType;
use dlaser\HcBundle\Form\searchType;
use dlaser\HcBundle\Form\HcExamenType;
use dlaser\ParametrizarBundle\Entity\Factura;
use dlaser\ParametrizarBundle\Entity\Paciente;


class HcExamenController extends Controller
{
	public function newAction($factura)
	{
		$em = $this->getDoctrine()->getEntityManager();
		
		$factura = $em->getRepository('ParametrizarBundle:Factura')->find($factura);
		
		if(!$factura)
		{
			throw $this->createNotFoundException('La información ingresada es incorrecta.');
		}
		
		$hc = $em->getRepository('HcBundle:Hc')->findOneBy(array('factura' => $factura->getId()));
		
		if(!$hc)
		{
			throw $this->createNotFoundException('La historia clinica no existe');
		}
		
		$examenes = $factura->getCupo()->getAgenda()->getUsuario()->getExamen();
		$paciente = $factura->getPaciente();
		
		$dql = $em->createQuery('SELECT
				he.id,
				he.fecha,
				he.fecha_r,
				he.resultado,
				he.estado,
				e.nombre
				FROM
				HcBundle:HcExamen he
				JOIN he.examen e
				JOIN he.hc hc
				JOIN hc.factura f
				JOIN f.paciente p
				WHERE
				he.hc = hc.id AND
				hc.factura = f.id AND
				f.paciente = p.id AND
				p.id = :paciente
				ORDER BY
				e.nombre DESC');
		
		$dql->setParameter('paciente', $paciente->getId());
		
		$exaPresentados = $dql->getResult();
		
		
		$editexform = $this->createForm(new HcExamenType());
		
		return $this->render('HcBundle:HcExamen:new.html.twig', array(
				'factura' => $factura,
				'hc' => $hc,
				'examenes' => $examenes,
				'exaPresentados' => $exaPresentados,
				'paciente' => $paciente,
				'ex_form'   => $editexform->createView()
		));
		
	}
	
	
	public function hcExamenAction()
	{
		$request = $this->get('request');
        
		$hc = $request->request->get('hc');
        $examen = $request->request->get('examen');
        $combo = $request->request->get('combo');
        
        $em = $this->getDoctrine()->getEntityManager();
        
        if($combo){
        	
        	if($combo==1){
        		$tupla = "(903818, 903815, 903816, 903868, 903841, 903824, 902207)";
        	}elseif ($combo==2){
        		$tupla = "(903818, 903815, 903816, 903868, 903841, 903824)";
        	}else{
        		$tupla = "(903818, 903815, 903816, 903868)";
        	}
        	
        	$query = $em->createQuery(' SELECT 
        									h.id
    									FROM 
        									HcBundle:Examen h    				
    									WHERE 
        									h.codigo IN '.$tupla
        	);
    
    		$exs = $query->getArrayResult();
    		
    		$entity = new HcExamen();    		
    		
    		foreach ($exs as $key => $value){
				
    			//$examen_hc = $em->getRepository('HcBundle:HcExamen')->findBy(array('hc' => $hc, 'examen' => $value['id']));
    			
    			if(true){
    				
    				$hc = $em->getRepository('HcBundle:Hc')->find($hc);
    				$examen = $em->getRepository('HcBundle:Examen')->find($value['id']);
    				 
    				$entity->setExamen($examen);
    				$entity->setHc($hc);
    				$entity->setFecha(new \DateTime('now'));
    				$entity->setEstado('P');
    				 
    				$em->persist($entity);
    				$em->flush();
    				 
    				$response=array("responseCode"=>200, "msg"=>"Examenes solicitados correctamente");
    				 
    				$response['examen']['id'] = $entity->getId();
    				$response['examen']['nombre'] = $entity->getExamen()->getNombre();
    				$response['examen']['fecha'] = $entity->getFecha();
    			}
    			else{
    				$response=array("responseCode"=>400, "msg"=>"El examen seleccionado ya esta solicitado en la historia clinica.");
    			}
    		}
        }else{
        	$examen_hc = $em->getRepository('HcBundle:HcExamen')->findBy(array('hc' => $hc, 'examen' => $examen, 'estado' => 'P'));
        	
        	if(!$examen_hc){
        			
        		$hc = $em->getRepository('HcBundle:Hc')->find($hc);
        		$examen = $em->getRepository('HcBundle:Examen')->find($examen);
        		
        		$entity = new HcExamen();
        			
        		$entity->setExamen($examen);
        		$entity->setHc($hc);
        		$entity->setFecha(new \DateTime('now'));
        		$entity->setEstado('P');
        			
        		$em->persist($entity);
        		$em->flush();
        			
        		$response=array("responseCode"=>200, "msg"=>"Examen solicitado correctamente");
        			
        		$response['examen']['id'] = $entity->getId();
        		$response['examen']['nombre'] = $entity->getExamen()->getNombre();
        		$response['examen']['fecha'] = $entity->getFecha();
        			
        	}else{
        		$response=array("responseCode"=>400, "msg"=>"El examen seleccionado ya esta solicitado en la historia clinica.");
        	}
        }    
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
	
	public function editHcExamenAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:HcExamen')->find($id);
	
		if(!$entity)
		{
			throw $this->createNotFoundException('El Examen no existe');
		}
		
		$entity->setFechaR(new \DateTime('now'));
	
		$form   = $this->createForm(new HcExamenType(), $entity);
	
		return $this->render('HcBundle:HcExamen:edit.html.twig', array(
				'entity' => $entity,
				'edit_form'   => $form->createView()
		));
	}
	
	public function ajaxUpdHcExamenAction()
	{
		$request = $this->get('request');
		
		$examen = $request->request->get('examen');
		$dia = $request->request->get('dia');
		$mes = $request->request->get('mes');
		$anio = $request->request->get('anio');
		$resultado = $request->request->get('resultado');

		$em = $this->getDoctrine()->getEntityManager();

		$examen = $em->getRepository('HcBundle:HcExamen')->find($examen);

		if($examen){
			
			$fecha_r = new \DateTime($mes.'/'.$dia.'/'.$anio);
			
			$examen->setFechaR($fecha_r);
			$examen->setResultado($resultado);
			$examen->setEstado('R');

			$em->persist($examen);
			$em->flush();

			$response=array("responseCode"=>200, "msg"=>"Examen modificado éxitosamente");
			
			$response['resultado'] = $examen->getResultado();
			$response['fecha'] = $examen->getFechaR()->format('Y-m-d');
		}else{
			$response=array("responseCode"=>400, "msg"=>"Examen solicitado incorrecto");
		}

		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));			
	}

	
	public function delHcExamenAction()
	{
		$request = $this->get('request');
		
		$examen = $request->request->get('examen');		
		
		$em = $this->getDoctrine()->getEntityManager();
		$entity= $em->getRepository('HcBundle:HcExamen')->find($examen);
	
		if (!$entity) {
			$response=array("responseCode"=>400, "msg"=>"Examen solicitado incorrecto");
		}else {
			$em->remove($entity);
			$em->flush();
			
			$response=array("responseCode"=>200, "msg"=>"Examen eliminado éxitosamente");
		}
				
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));	
	}
	
	public function cronologicoExamenesAction(){
		$request = $this->get('request');
		
		$examen = $request->request->get('examen');
		$factura = $request->request->get('factura');
		
		$em = $this->getDoctrine()->getEntityManager();
		
		$factura= $em->getRepository('ParametrizarBundle:Factura')->find($factura);
		
		if(!$factura){
			$response=array("responseCode"=>400, "msg"=>"Examenes solicitado incorrecto");
		}else{
			$dql = $em->createQuery('SELECT 
										he.id,
										he.fecha_r,
										he.resultado
									FROM 
										HcBundle:HcExamen he 
									JOIN he.examen e
									JOIN he.hc hc
									JOIN hc.factura f
									JOIN f.paciente p									
									WHERE
										he.examen = e.id AND
										he.hc = hc.id AND
										hc.factura = f.id AND
										f.paciente = p.id AND
										p.id = :paciente AND
										e.id = :examen
									ORDER BY
										he.fecha_r DESC');
			
			$dql->setParameter('paciente', $factura->getPaciente()->getId());
			$dql->setParameter('examen', $examen);
			
			$cronologico = $dql->getResult();
			
			if($cronologico){
				
				$response=array("responseCode"=>200);
				
				foreach ($cronologico as $key => $value){
					$response['ex'][$value['id']]['id'] = $value['id'];
					$response['ex'][$value['id']]['fecha'] = $value['fecha_r'];
					$response['ex'][$value['id']]['resultado'] = $value['resultado'];
				}
			}else{
				$response=array("responseCode"=>400, "msg"=>"No hay examenes disponibles.");
			}			
			
		}
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));		
	}	
}
