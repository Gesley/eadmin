<?php
class Guardiao_Form_Modulo extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $modelModlModulo = new Application_Model_DbTable_OcsTbModlModulo();
        $sistemas = $modelModlModulo->getSistemaEadmin();
        
        $modl_id_modulo = new Zend_Form_Element_Hidden('MODL_ID_MODULO');
        
        $modl_nm_modulo = new Zend_Form_Element_Text('MODL_NM_MODULO');
        $modl_nm_modulo->setRequired(true)
                           ->setLabel('*Nome do Módulo: ')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->setAttrib('style', 'width: 500px;')
                           ->addValidator('NotEmpty');
        
        $modl_nm_sistema = new Zend_Form_Element_Select('MODL_NM_SISTEMA');
        $modl_nm_sistema->setRequired(true)
                     ->setLabel('Sistema:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->setAttrib('style', 'width: 500px;')
                     //->setAttrib('onChange','this.form.submit();')
                     ->addMultiOptions(array(''=>'SELECIONE O SISTEMA'));
        foreach ($sistemas as $sistemas_p):
            $modl_nm_sistema->addMultiOptions(array($sistemas_p["NOME_SISTEMA"] => $sistemas_p["NOME_SISTEMA"]  . ' - ' . $sistemas_p["DS_NOME_SISTEMA"]));
        endforeach;;
        
        $associar = new Zend_Form_Element_Submit('Associar');
        $associar->setOptions(array('class' => 'novo'));
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($modl_id_modulo,
                                 $modl_nm_sistema,
                                 $modl_nm_modulo,
                                 $associar,
                                 $Alterar));
    }
}