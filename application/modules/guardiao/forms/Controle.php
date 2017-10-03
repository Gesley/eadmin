<?php
class Guardiao_Form_Controle extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $modelControleSistema = new Application_Model_DbTable_OcsTbCtrlControleSistema();
        $modulos = $modelControleSistema->getModulos();
        
        
        $ctrl_id_controle_sistema = new Zend_Form_Element_Hidden('CTRL_ID_CONTROLE_SISTEMA');
        
        $ctrl_nm_controle_sistema = new Zend_Form_Element_Text('CTRL_NM_CONTROLE_SISTEMA');
        $ctrl_nm_controle_sistema->setRequired(true)
                           ->setLabel('*Nome do Controle: ')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->setAttrib('style', 'width: 200px;')
                           ->addValidator('NotEmpty');
        
        $ctrl_id_modulo = new Zend_Form_Element_Select('CTRL_ID_MODULO');
        $ctrl_id_modulo->setRequired(true)
                     ->setLabel('Modulo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addMultiOptions(array(''=>'SELECIONE O MÓDULO'));
        foreach ($modulos as $modulos_p):
            $ctrl_id_modulo->addMultiOptions(array($modulos_p["MODL_ID_MODULO"] => strtoupper($modulos_p["MODL_NM_MODULO"])));
        endforeach;;
        
        $associar = new Zend_Form_Element_Submit('Associar');
        $associar->setOptions(array('class' => 'novo'));
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($ctrl_id_controle_sistema,
                                 $ctrl_id_modulo,
                                 $ctrl_nm_controle_sistema,
                                 $associar,
                                 $Alterar));
    }
}