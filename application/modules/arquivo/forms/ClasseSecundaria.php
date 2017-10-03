<?php

class Arquivo_Form_ClasseSecundaria extends Zend_Form {

    public function init() {
        $aqas_cd_assunto_secundario = new Zend_Form_Element_Select('AQAS_CD_ASSUNTO_SECUNDARIO');
        $aqas_cd_assunto_secundario->setRequired(true)
                ->setLabel('Cód - Assunto Secundario:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => 'SELECIONE UM ASSUNTO'));

        $this->addElements(array($aqas_cd_assunto_secundario));
    }

}
