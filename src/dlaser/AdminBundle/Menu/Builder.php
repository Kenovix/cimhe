<?php

namespace dlaser\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
	public function adminMenu(FactoryInterface $factory, array $options)
	{
		$menu = $factory->createItem('root');
		$menu->setChildrenAttributes(array('id' => 'menu'));
		
		$securityContext = $this->container->get('security.context');
		
		if($securityContext->isGranted('ROLE_ADMIN')){

			$menu->addChild('Parametrizar', array('uri' => '#'));	
				$menu['Parametrizar']->addChild('Empresa', array('route' => 'empresa_list'));
				$menu['Parametrizar']->addChild('Cliente', array('route' => 'cliente_list'));
				$menu['Parametrizar']->addChild('Cargo', array('route' => 'cargo_list'));
				$menu['Parametrizar']->addChild('Paciente', array('uri' => '#'));
					$menu['Parametrizar']['Paciente']->addChild('Consultar', array('route' => 'paciente_list', 'routeParameters' => array('char' => 'A')));
					$menu['Parametrizar']['Paciente']->addChild('Listar', array('route' => 'paciente_list', 'routeParameters' => array('char' => 'A')));
				$menu['Parametrizar']->addChild('Usuarios', array('route' => 'usuario_list'));
				
			$menu->addChild('Agendamiento', array('uri' => '#'));
				$menu['Agendamiento']->addChild('Agenda', array('uri' => '#'));
					$menu['Agendamiento']['Agenda']->addChild('Listado', array('route' => 'agenda_list'));
					$menu['Agendamiento']['Agenda']->addChild('Nueva', array('route' => 'agenda_new'));
					$menu['Agendamiento']['Agenda']->addChild('Agenda Medica', array('route' => 'agenda_medica_list'));
				
				$menu['Agendamiento']->addChild('Citas', array('uri' => '#'));
					$menu['Agendamiento']['Citas']->addChild('Listado', array('route' => 'cupo_list'));
					$menu['Agendamiento']['Citas']->addChild('Nueva', array('route' => 'cupo_new'));
					$menu['Agendamiento']['Citas']->addChild('Consultar', array('route' => 'cupo_search'));
					$menu['Agendamiento']['Citas']->addChild('Facturar', array('route' => 'factura_search'));
					
			$menu->addChild('Facturación', array('uri' => '#'));
				$menu['Facturación']->addChild('Facturas', array('uri' => '#'));
					$menu['Facturación']['Facturas']->addChild('Listar', array('route' => 'factura_list'));
					$menu['Facturación']['Facturas']->addChild('Consultar', array('route' => 'factura_list'));
					$menu['Facturación']['Facturas']->addChild('Generar reporte', array('route' => 'factura_genera_reporte'));
				$menu['Facturación']->addChild('Cierre de caja', array('uri' => '#'));
					$menu['Facturación']['Cierre de caja']->addChild('Generar', array('route' => 'factura_arqueo'));
					$menu['Facturación']['Cierre de caja']->addChild('Consultar', array('route' => 'factura_consultar_arqueo'));
				$menu['Facturación']->addChild('Admisión', array('uri' => '#'));
					$menu['Facturación']['Admisión']->addChild('Consultar', array('route' => 'factura_admision_search'));
				$menu['Facturación']->addChild('Cliente', array('route' => 'factura_cliente_list'));
				$menu['Facturación']->addChild('Informes', array('uri' => '#'));
				$menu['Facturación']['Informes']->addChild('Honorarios', array('route' => 'factura_reporte_medico'));
							
		
		}elseif($securityContext->isGranted('ROLE_MEDICO')){
			
			$menu->addChild('Agendamiento', array('uri' => '#'));
				$menu['Agendamiento']->addChild('Agenda', array('route' => 'agenda_medica_list'));
			
			
		}else{
			
			$menu->addChild('Agendamiento', array('uri' => '#'));
				//$menu['Agendamiento']->addChild('Agenda', array('route' => 'agenda_aux_list'));
			
				$menu['Agendamiento']->addChild('Citas', array('uri' => '#'));
					$menu['Agendamiento']['Citas']->addChild('Listado', array('route' => 'cupo_list'));
					$menu['Agendamiento']['Citas']->addChild('Nueva', array('route' => 'cupo_new'));
					$menu['Agendamiento']['Citas']->addChild('Consultar', array('route' => 'cupo_search'));
					$menu['Agendamiento']['Citas']->addChild('Facturar', array('route' => 'factura_search'));
				
			$menu->addChild('Facturación', array('uri' => '#'));
				$menu['Facturación']->addChild('Cierre de caja', array('route' => 'factura_arqueo'));
				$menu['Facturación']->addChild('Admisión', array('uri' => '#'));
					$menu['Facturación']['Admisión']->addChild('Consultar', array('route' => 'factura_admision_search'));
				$menu['Facturación']->addChild('Cliente', array('route' => 'factura_cliente_list'));
		}
		
		$actualUser = $securityContext->getToken()->getUser();
		
		$menu->addChild($actualUser->getUsername(), array('uri' => '#'));
		$menu[$actualUser->getUsername()]->addChild('Perfil', array('route' => 'usuario_show', 'routeParameters' => array('id' => $actualUser->getId())));
		$menu[$actualUser->getUsername()]->addChild('Salir', array('route' => 'logout'));

		return $menu;
	}
}