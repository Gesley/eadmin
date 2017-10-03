<?php
class Guardiao_Form_Perfil extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $modelPapel = new Application_Model_DbTable_OcsTbPerfPerfil();
        
        $perf_ds_perfil = new Zend_Form_Element_Text('PERF_DS_PERFIL');
        $perf_ds_perfil->setRequired(true)
                     ->setLabel('*Nome do Perfil:')
                     ->setAttrib('style', 'text-transform: uppercase; width: 200px;')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty');
        
        $submit = new Zend_Form_Element_Submit('Criar');
        $submit->setOptions(array('class' => 'novo'));
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($perf_ds_perfil,$submit,$Alterar));

    }
}