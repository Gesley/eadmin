<?php
class Guardiao_Form_Acao extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $modelControleSistema = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        $modulos = $modelControleSistema->getModulos();
        $controles = $modelControleSistema->getTodosControle();
        

        $acao_id_acao_sistema = new Zend_Form_Element_Hidden('ACAO_ID_ACAO_SISTEMA');
        
        $acao_nm_acao_sistema = new Zend_Form_Element_Text('ACAO_NM_ACAO_SISTEMA');
        $acao_nm_acao_sistema->setRequired(true)
                           ->setLabel('*Nome da Ação: ')
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
            $ctrl_id_modulo->addMultiOptions(array($modulos_p["MODL_ID_MODULO"] => $modulos_p["MODL_NM_MODULO"]));
        endforeach;;
        
        $acao_id_controle_sistema = new Zend_Form_Element_Select('ACAO_ID_CONTROLE_SISTEMA');
        $acao_id_controle_sistema->setRequired(true)
                     ->setLabel('Controle:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     //->setAttrib('onChange','this.form.submit();')
                     ->addMultiOptions(array(''=>'SELECIONE O CONTROLE'));
         foreach ($controles as $controles_p):
            $acao_id_controle_sistema->addMultiOptions(array($controles_p["CTRL_ID_CONTROLE_SISTEMA"] => $controles_p["CTRL_NM_CONTROLE_SISTEMA"]));
        endforeach;;
        
        $associar = new Zend_Form_Element_Submit('Associar');
        $associar->setOptions(array('class' => 'novo'));
        
        $Alterar = new Zend_Form_Element_Submit('Alterar');
        $Alterar->setOptions(array('class' => 'novo'));
        
        $this->addElements(array($acao_id_acao_sistema,
                                 $ctrl_id_modulo,
                                 $acao_id_controle_sistema,
                                 $acao_nm_acao_sistema,
                                 $associar,
                                 $Alterar));
    }
}