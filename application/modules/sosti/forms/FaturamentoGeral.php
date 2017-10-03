<?php

class Sosti_Form_FaturamentoGeral extends Zend_Form {

    public function init() {

        $faturamento = new Trf1_Sosti_Negocio_Faturamento();

        $this->setAction('')
                ->setMethod('post');
        
        /**
         * Form usado em todos os perfils
         */
        $mofa_id_fase = new Zend_Form_Element_Select('STATUS_SOLICITACAO');
        $mofa_id_fase->setLabel('Status da Solicitação:');
        $mofa_id_fase->addMultiOptions(array('' => 'Todas', '1000' => 'Baixadas', '1014' => 'Avaliadas', '1019' => 'Recusadas'));

        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial->setLabel('Data da Baixa - Inicial:');

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setLabel('Data da Baixa - Final:');

        $data_entrada_inicial = new Zend_Form_Element_Text('DATA_ENTRADA_CAIXA_INICIAL');
        $data_entrada_inicial->setLabel('Data da Entrada na Caixa - Inicial:');

        $data_entrada_final = new Zend_Form_Element_Text('DATA_ENTRADA_CAIXA_FINAL');
        $data_entrada_final->setLabel('Data da Entrada na Caixa - Final:');

        $submit = new Zend_Form_Element_Submit('acao');
        $submit->setLabel('Pesquisar');

        $this->addElements(array(
            $mofa_id_fase,
            $data_inicial,
            $data_final,
            $data_entrada_inicial,
            $data_entrada_final,
            $submit));
    }

}