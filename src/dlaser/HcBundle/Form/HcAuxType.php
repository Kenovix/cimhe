<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class HcAuxType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('fecha',	 'datetime',array('read_only'=> true))	
		->add('sistole', 'integer', array('label' => 'Sistole *','attr' => array('placeholder' => 'campo requerido 999 max','autofocus'=>'autofocus')))
		->add('diastole','integer', array('label' => 'Diastole *','attr' => array('placeholder' => 'campo requerido 999 max')))
		->add('f_c',	 'integer',	array('required' => false, 'label'=> 'Frecuencia cardiaca','attr' => array('placeholder' => '999 MAX && 10 MIN')))
		->add('f_r',	 'integer',	array('required' => false, 'label'=> 'Frecuencia respiratoria','attr' => array('placeholder' => '999 MAX && 10 MIN')))
		->add('peso',	 'integer',	array( 'label'=> 'Peso Kg *','attr' => array('placeholder' => '999 MAX && 10 MIN')))
		->add('estatura','integer',	array( 'label'=> 'Estatura cm *','attr' => array('placeholder' => '150 MAX && 10 MIN')))		
		;
	}

	public function getName()
	{
		return 'newHcAux';
	}
}