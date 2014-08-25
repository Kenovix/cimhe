<?php

namespace dlaser\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FacturacionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        ->add('inicio', 'hidden', array('required' => true))
        ->add('fin', 'hidden', array('required' => true))
        ->add('sedes', 'hidden', array('required' => true))
        ->add('copago', 'hidden', array('required' => false))
        ->add('grupo', 'hidden', array('required' => false))
        ->add('concepto', 'textarea', array('label' => 'Concepto', 'required' => true, 'attr' => array('autofocus'=>'autofocus')))
        ->add('observacion', 'textarea', array('required' => false))
        ->add('valor', 'integer', array('required' => true))
        ->add('iva', 'integer', array('required' => true))
        ;
    }

    public function getName()
    {
        return 'newFacturaFinal';
    }
}