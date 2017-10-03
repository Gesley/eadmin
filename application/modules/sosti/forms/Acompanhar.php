<?php

class Sosti_Form_Acompanhar extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');
        
        $papd_cd_matricula_interessado = new Zend_Form_Element_Text('PAPD_CD_MATRICULA_INTERESSADO');
        $papd_cd_matricula_interessado->setRequired(false)
                                   ->setLabel('Acompanhar andamento de solicitação:: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($papd_cd_matricula_interessado, $submit));
    }

}
