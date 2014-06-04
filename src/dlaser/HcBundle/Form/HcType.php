<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class HcType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('fecha',   			'datetime',array('read_only'=>true))
		->add('sistole', 			'integer', array('required' => false, 'label' => 'Sist.','attr' => array('autofocus'=>'autofocus')))
		->add('diastole',			'integer', array('required' => false, 'label' => 'Dias.'))
		->add('f_c',	 			'integer',	array('required' => false, 'label'=> 'F/C'))
		->add('f_r',	 			'integer',	array('required' => false, 'label'=> 'F/R'))
		->add('peso',	 			'integer',	array( 'label'=> 'Peso'))
		->add('estatura',			'integer',	array( 'label'=> 'Talla'))
				
		->add('motivo',		    	'textarea',array('label' => 'Motivo consulta:', 'attr' => array('placeholder' => 'Ingrese el motivo de la consulta')))
		->add('enfermedad',		    'textarea',array('label' => 'Enfermedad actual:', 'attr' => array('placeholder' => 'Ingrese la enfermedad actual del paciente')))
		->add('antecedentes',		'textarea',array('label' => 'Antecedentes:', 'attr' => array('placeholder' => 'Ingrese los antecedentes del paciente')))
		->add('rev_sistema',		'textarea',array('label' => 'Revisión Sistema:', 'required' => false))
		->add('exa_fisico',			'textarea',array('label' => 'Examen Físico:', 'required' => false))
		->add('dx_presunto',		'textarea',array('label' => 'Diagnostico Presunto:', 'required' => false))
		->add('exa_presentado',		'textarea',array('label' => 'Examenes presentados', 'required' => false))
		->add('nota_exa_soli',		'textarea',array('label' => 'Nota examen solicitado', 'required' => false))
		->add('interconsulta',		'textarea',array('required' => false,'attr' => array('placeholder' => 'Remisión a interconsulta')))
		->add('manejo',				'textarea',array('label' => 'Plan de Manejo:', 'required' => false))
		->add('dx_presunto',		'text',array('label' => 'Otro diagnostico', 'required' => false))
		->add('manejo',				'textarea',array('required' => false))
		->add('control',			'text', array('required' => false))
		->add('ctrl_prioritario',	'checkbox',array('required' => false))
		->add('postfecha',			'integer', array('required' => false,'attr' => array('placeholder' => '2 Caracteres maximo')))
		
		->add('inicio_inca',   		'date',array('label' => 'Inicio Incapacidad:','required' => false))
		->add('duracion_inca',   	'integer',array('label' => 'Tiempo de Incapacidad:','required' => false,'attr' => array('placeholder' => 'Tiempo que dura la incapacidad.')))
		->add('nota_inca',   		'text',array('label' => 'Nota Incapacidad:','required' => false,'attr' => array('placeholder' => 'Motivo incapacidad')))
        ;	
	}

	public function getName()
	{
		return 'newHC';
	}
}