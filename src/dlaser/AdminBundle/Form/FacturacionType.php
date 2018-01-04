<?php

namespace dlaser\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FacturacionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        ->add('prefijo', 'hidden', array('required' => true))
        ->add('consecutivo', 'hidden', array('required' => true))
        ->add('inicio', 'hidden', array('required' => true))
        ->add('fin', 'hidden', array('required' => true))
        ->add('sedes', 'hidden', array('required' => true))
        ->add('concepto', 'textarea', array('label' => 'Concepto', 'required' => true, 'attr' => array('autofocus'=>'autofocus')))
        ->add('nota', 'textarea', array('required' => false))
        ->add('subtotal', 'integer', array('required' => true))
        ->add('copago', 'integer', array('required' => false))
        ->add('iva', 'integer', array('required' => true))
        ;
    }

    public function getName()
    {
        return 'newFacturaFinal';
    }
}