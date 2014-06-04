<?php
namespace dlaser\HcBundle\Controller;

use Symfony\Component\BrowserKit\Request;
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

class HcCieController extends Controller
{
	//------------------------------- DIAGNOSTICO HC -------------------------------------------------
	
	public function hcCieAction()
	{
		$request = $this->get('request');
		
		$hc = $request->request->get('hc');
		$cie = $request->request->get('cie');
		
		$em = $this->getDoctrine()->getEntityManager();
		
				
		$historia = $em->getRepository('HcBundle:Hc')->find($hc);
		$diagnostico = $em->getRepository('HcBundle:Cie')->find($cie);
	
		if(!$historia || !$diagnostico)
		{
			throw $this->createNotFoundException('informacion solicitada no existe');
		}

		$dql = $em->createQuery('SELECT h FROM HcBundle:Hc h JOIN h.cie c
				WHERE h.id = :hc AND c.id = :cie');
		$dql->setParameter('hc', $hc);
		$dql->setParameter('cie', $cie);		
		$hc_cie = $dql->getResult();
					
		if(!$hc_cie)
		{
			if($historia->addCie($diagnostico)){
			
				$em->persist($historia);
				$em->flush();
					
				$response=array("responseCode"=>200, "msg"=>"Diagnostico solicitado correctamente");
					
				$response['cie']['id'] = $diagnostico->getId();
				$response['cie']['nombre'] = $diagnostico->getNombre();
				$response['cie']['codigo'] = $diagnostico->getCodigo();
			}
		}else{
			$response=array("responseCode"=>400, "msg"=>"El Diagnostico seleccionado ya esta solicitado en la historia clinica.");
		}
		
	
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
	
	public function delHcCieAction()
	{
		$reques = $this->get('request');
		
		$cie = $reques->request->get('cie');
		$hc = $reques->request->get('hc');
		
		$em = $this->getDoctrine()->getEntityManager();
		$historia = $em->getRepository('HcBundle:Hc')->find($hc);
		$diagnostico = $em->getRepository('HcBundle:Cie')->find($cie);
			
		if(!$historia||!$diagnostico)
		{
			$response=array("responseCode"=>400, "msg"=>"Diagnostico solicitado incorrecto");
		}else{
			$key = $historia->getCie()->indexOf($diagnostico);
			$historia->getCie()->remove($key);
			$em->flush();

			$response=array("responseCode"=>200, "msg"=>"Diagnostico eliminado éxitosamente");
		}
		
		$return=json_encode($response);
		return new Response($return,200,array('Content-Type'=>'application/json'));
	}
	//------------------------------- END DIAGNOSTICO HC -------------------------------------------------
	
}
