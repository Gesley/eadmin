<?php
 class Arquivo_Form_ListarPrincipal extends Zend_Form{
     
    public function init() {
        $aqas_cd_assunto_principal = new Zend_Form_Element_Select('AQAP_CD_ASSUNTO_PRINCIPAL');
        $aqas_cd_assunto_principal->setRequired(true)
                ->setLabel('Cód - Assunto Principal:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => 'SELECIONE UM ASSUNTO'));

        $this->addElements(array($aqas_cd_assunto_principal));
    }
 }