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
				$menu['Facturación']->addChild('Cierre de caja', array('route' => 'factura_arqueo'));
				$menu['Facturación']->addChild('Admisión', array('uri' => '#'));
					$menu['Facturación']['Admisión']->addChild('Consultar', array('route' => 'factura_admision_search'));
				$menu['Facturación']->addChild('Cliente', array('route' => 'factura_cliente_list'));
				$menu['Facturación']->addChild('Informes', array('uri' => '#'));
				$menu['Facturación']['Informes']->addChild('Honorarios', array('route' => 'factura_reporte_medico'));
				
			$menu->addChild('Historia Clínica', array('uri' => '#'));
				$menu['Historia Clínica']->addChild('Consultar', array('route' => 'hc_search'));
				$menu['Historia Clínica']->addChild('Diagnostico', array('uri' => '#'));
					$menu['Historia Clínica']['Diagnostico']->addChild('Listado', array('route' => 'dx_userDx'));
					$menu['Historia Clínica']['Diagnostico']->addChild('Nuevo', array('route' => 'dx_new'));
				$menu['Historia Clínica']->addChild('Medicamento', array('uri' => '#'));
					$menu['Historia Clínica']['Medicamento']->addChild('Listado', array('route' => 'medicamento_list'));
					$menu['Historia Clínica']['Medicamento']->addChild('Nuevo', array('route' => 'medicamento_new'));
				$menu['Historia Clínica']->addChild('Examen', array('uri' => '#'));
					$menu['Historia Clínica']['Examen']->addChild('Listado', array('route' => 'examen_list'));
					$menu['Historia Clínica']['Examen']->addChild('Nuevo', array('route' => 'examen_new'));
				$menu['Historia Clínica']->addChild('CTC', array('uri' => '#'));
					$menu['Historia Clínica']['CTC']->addChild('Consultar', array('route' => 'ctc_search'));
		
		}elseif($securityContext->isGranted('ROLE_MEDICO')){
			
			$menu->addChild('Agendamiento', array('uri' => '#'));
				$menu['Agendamiento']->addChild('Agenda', array('route' => 'agenda_medica_list'));
			
			$menu->addChild('Historia Clínica', array('uri' => '#'));
				$menu['Historia Clínica']->addChild('Consultar', array('route' => 'hc_search'));
				$menu['Historia Clínica']->addChild('Diagnostico', array('uri' => '#'));
					$menu['Historia Clínica']['Diagnostico']->addChild('Listado', array('route' => 'dx_userDx'));
					$menu['Historia Clínica']['Diagnostico']->addChild('Nuevo', array('route' => 'dx_new'));
				$menu['Historia Clínica']->addChild('Medicamento', array('uri' => '#'));
					$menu['Historia Clínica']['Medicamento']->addChild('Listado', array('route' => 'medicamento_list'));
					$menu['Historia Clínica']['Medicamento']->addChild('Nuevo', array('route' => 'medicamento_new'));
				$menu['Historia Clínica']->addChild('Examen', array('uri' => '#'));
					$menu['Historia Clínica']['Examen']->addChild('Listado', array('route' => 'examen_list'));
					$menu['Historia Clínica']['Examen']->addChild('Nuevo', array('route' => 'examen_new'));
				$menu['Historia Clínica']->addChild('CTC', array('uri' => '#'));
				$menu['Historia Clínica']['CTC']->addChild('Consultar', array('route' => 'ctc_search'));
		}else{
			
			$menu->addChild('Agendamiento', array('uri' => '#'));
				$menu['Agendamiento']->addChild('Agenda', array('route' => 'agenda_aux_list'));
			
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