<?php

class Sosti_Form_FaturamentoTrf extends Zend_Form {

    public function init() {

        $faturamento = new Trf1_Sosti_Negocio_Faturamento();
        $statusContratante = $faturamento->retornaStatus('CTE');
        $classificacao = $faturamento->retornaClassificacao();

        $this->setAction('')
                ->setMethod('post');

        /**
         * Form usado pela Contratante
         */
        $status_trf = new Zend_Form_Element_Select('PFTR_ID_STATUS');
        $status_trf->setLabel('Status Do TRF1:');
        $status_trf->addMultiOptions(array('' => ''));
        foreach ($statusContratante as $status):
            $status_trf->addMultiOptions(array($status["SCTA_ID_STATUS"] => $status["SCTA_DS_STATUS"]));
        endforeach;

        $classificacao_trf = new Zend_Form_Element_Select('PFTR_ID_CLASSIFICACAO');
        $classificacao_trf->setLabel('Classificação do TRF1:');
        $classificacao_trf->addMultiOptions(array('' => ''));
        foreach ($classificacao as $status):
            $classificacao_trf->addMultiOptions(array($status["CLCO_ID_CLASSIFICACAO"] => $status["CLCO_DS_OBSERVACAO"]));
        endforeach;
        
        $nrRelaFaturamento = new Zend_Form_Element_Text('PFTR_NR_ID_RELAT_FATURAMENTO');
        $nrRelaFaturamento->setLabel('N° Relatório Faturamento: ')
                ->setAttrib('style', 'text-transform: uppercase; width: 40px;');

        $dataFaturamento = new Zend_Form_Element_Text('PFTR_DH_FATURAMENTO');
        $dataFaturamento->setLabel('Data de Faturamento:');
        

        $this->addElements(array(
            $status_trf,
            $classificacao_trf,
            $nrRelaFaturamento,
            $dataFaturamento));
    }

}