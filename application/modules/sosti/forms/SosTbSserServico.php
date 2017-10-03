<?php

class Sosti_Form_SosTbSserServico extends Zend_form {
    
    public function init() {
        
        $this->setAction('')
                ->setMethod('post');
        
        $descricao_servico = new Zend_Form_Element_Text('SETP_DS_SERVICO');
        $descricao_servico->setLabel('Descrição do Serviço')
            ->setRequired(true);
        
        
    }
}
?>
