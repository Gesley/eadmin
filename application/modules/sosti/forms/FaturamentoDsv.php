<?php

class Sosti_Form_FaturamentoDsv extends Zend_Form {

    public function init() {

        $faturamento = new Trf1_Sosti_Negocio_Faturamento();
        $statusContratada = $faturamento->retornaStatus('CTA');
        $classificacao = $faturamento->retornaClassificacao();

        $this->setAction('')
                ->setMethod('post');

        /**
         * Form para Desenvolvedora
         */
        $status_dsv = new Zend_Form_Element_Select('PFDS_ID_STATUS');
        $status_dsv->setLabel('Status do Desenvolvimento:');
        $status_dsv->addMultiOptions(array('' => ''));
        foreach ($statusContratada as $status):
            $status_dsv->addMultiOptions(array($status["SCTA_ID_STATUS"] => $status["SCTA_DS_STATUS"]));
        endforeach;

        $classificacao_dsv = new Zend_Form_Element_Select('PFDS_ID_CLASSIFICACAO');
        $classificacao_dsv->setRequired(true);
        $classificacao_dsv->setOptions(array('class' => 'required'));
        $classificacao_dsv->setLabel('Classificação do Desenvolvimento:');
        $classificacao_dsv->addMultiOptions(array('' => ''));
        foreach ($classificacao as $status):
            $classificacao_dsv->addMultiOptions(array($status["CLCO_ID_CLASSIFICACAO"] => $status["CLCO_DS_OBSERVACAO"]));
        endforeach;

        $responsavel_baixa = new Zend_Form_Element_Text('MOFA_CD_MATRICULA');
        $responsavel_baixa->setLabel('Responsável pela Baixa: ')
                ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $this->addElements(array(
            $responsavel_baixa,
            $status_dsv,
            $classificacao_dsv));
    }

}