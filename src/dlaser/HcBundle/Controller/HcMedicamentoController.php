<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;

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

class HcMedicamentoController extends Controller 
{
	
	//------------------------------- MEDICAMENTO HC ---------------------------------------------------
	
	public function hcMediAction()
	{
		$request = $this->get('request');
		
		$hc = $request->request->get('hc');
		$medica = $request->request->get('medica');
		
		$em = $this->getDoctrine()->getEntityManager();
		
		$hc = $em->getRepository('HcBundle:Hc')->find($hc);
		$medica = $em->getRepository('HcBundle:Medicamento')->find($medica);
		
		$dql = $em->createQuery('SELECT
				hm.id,
				m.principioActivo
				FROM
				HcBundle:HcMedicamento hm
				JOIN
				hm.medicamento m
				WHERE
				hm.hc = :hc AND
				m.principioActivo = :principio');
			
		$dql->setParameter('hc', $hc);
		$dql->setParameter('principio', $medica->getPrincipioActivo());
			
		$medicamento = $dql->getResult();
		
		if(!$medicamento)
		{
			
			$entity  = new HcMedicamento();
			
			$entity->setEstado('R');
			$entity->setHc($hc);
			$entity->setMedicamento($medica);
				
			$em->persist($entity);
			$em->flush();
			
			$response=array("responseCode"=>200, "msg"=>"Medicamento solicitado correctamente");
			
			$response['medica']['id'] = $entity->getId();
			$response['medica']['pactivo'] = $entity->getMedicamento()->getPrincipioActivo();
			$response['medica']['cncntracn'] = $entity->getMedicamento()->getConcentracion();
			$response['medica']['ddia'] = $entity->getMedicamento()->getDosisDia();
			$response['medica']['pos'] = $entity->getMedicamento()->getPos();
			$response['medica']['tiempo'] = $entity->getMedicamento()->getTiempo();
			$response['medica']['estado'] = $entity->getEstado();
			
			
		}else{
			$response=array("responseCode"=>400, "msg"=>"El Medicamento seleccionado ya esta solicitado en la historia clinica.");
		}
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
	
	public function updateHcMediAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:HcMedicamento')->find($id);		
		
		if(!$entity)
		{
			throw $this->createNotFoundException('informacion solicitada no existe');
		}
		
		
		if($entity->getEstado() == 'P')	
		{
			$entity->setEstado('R');
		}else if($entity->getEstado() == 'R')	
		{
			$entity->setEstado('P');
		}
		
		$em->persist($entity);
		$em->flush();
		$hc = $entity->getHc()->getId();
		$this->get('session')->setFlash('info',
				'¡Enhorabuena! La informacion se ha registrado correctamente ');
		
		return $this->redirect($this->generateUrl('hc_edit',array('id'=>$hc)));
	}

	
	public function delHcMediAction()
	{
		$request = $this->get('request');
		
		$medica = $request->request->get('medica');
		
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('HcBundle:HcMedicamento')->find($medica);		
		
		if (!$entity) {
			
			$response=array("responseCode"=>400, "msg"=>"Medicamento solicitado incorrecto");
			
		}else{
			$historia = $entity->getHc();
			//--------------------------- eliminar el ctc deacuerdo al medicamento
			$md = $entity->getMedicamento()->getId();
			
			$dql = $em->createQuery('SELECT ctc.id FROM HcBundle:Ctc ctc WHERE ctc.medicamento = :id');
			$dql->setParameter('id', $md);
			$hcMe = $dql->getResult();
			
			if(!$hcMe)
			{
				$em->remove($entity);
				$em->flush();
			
				$response=array("responseCode"=>200, "msg"=>"Medicamento eliminado éxitosamente");
			}else{
				$hcMe = $dql->getSingleResult();
				$ctc = $em->getRepository('HcBundle:Ctc')->find($hcMe['id']);
				$em->remove($ctc);
				$em->flush();
					
				$em->remove($entity);
				$em->flush();
				
				$response=array("responseCode"=>200, "msg"=>"Medicamento-ctc eliminado éxitosamente");
			}				
			
		}
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
		//--------------------------- end eliminar ctc 		
	}
	//------------------------------- END MEDICAMENTO HC -------------------------------------------------
}
