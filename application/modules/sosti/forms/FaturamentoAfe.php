<?php

class Sosti_Form_FaturamentoAfe extends Zend_Form {

    public function init() {

        $faturamento = new Trf1_Sosti_Negocio_Faturamento();
        $statusAfericao = $faturamento->retornaStatus('AFE');
        $classificacao = $faturamento->retornaClassificacao();

        $this->setAction('')
                ->setMethod('post');

        /**
         * Form Usado pela Aferidora
         */
        $status_fat = new Zend_Form_Element_Select('PFAF_ID_STATUS');
        $status_fat->setLabel('Status da Aferição:');
        $status_fat->addMultiOptions(array('' => ''));
        foreach ($statusAfericao as $status):
            $status_fat->addMultiOptions(array($status["SCTA_ID_STATUS"] => $status["SCTA_DS_STATUS"]));
        endforeach;

        $classificacao_fat = new Zend_Form_Element_Select('PFAF_ID_CLASSIFICACAO');
        $classificacao_fat->setLabel('Classificação da Aferição:');
        $classificacao_fat->addMultiOptions(array('' => ''));
        foreach ($classificacao as $status):
            $classificacao_fat->addMultiOptions(array($status["CLCO_ID_CLASSIFICACAO"] => $status["CLCO_DS_OBSERVACAO"]));
        endforeach;
        
        $nrLote = new Zend_Form_Element_Text('PFAF_NR_LOTE');
        $nrLote->setLabel('N° Lote: ')
                ->setAttrib ( 'size', '10' )
                ->setAttrib ( 'maxlength', 6 )
               ->addFilter ( 'Digits' )->addValidator ( 'Digits' );
        
        $dataPrevRetorno = new Zend_Form_Element_Text('PFAF_DH_PREVISAO_RETORNO_LOTE');
        $dataPrevRetorno->setLabel('Data Prevista Retorno:');

        $dataRetorno = new Zend_Form_Element_Text('PFAF_DH_RETORNO_LOTE');
        $dataRetorno->setLabel('Data de Retorno:');
        
        $this->addElements(array(
            $status_fat,
            $classificacao_fat,
            $nrLote,
            $dataPrevRetorno,
            $dataRetorno));
    }

}