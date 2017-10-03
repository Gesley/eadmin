<?php

class Sosti_Form_Servicos extends Zend_Form {

    public function init() {

        $servicoObj = new Application_Model_DbTable_SosTbTpseTipoServico();

        $this->setAction('')
                ->setMethod('post')
                ->setName('Arquivar');

        $servico = new Zend_Form_Element_Select('SERVICO');
        $servico->setLabel('Digite o Serviço:');
        $servico->setOptions(array('style' => 'width: 490px'));
        $rows = $servicoObj->fetchAll(array('TPSE_IC_ATIVO=?' => 'S'), array(' TPSE_DS_TP_SERVICO  ASC'));

        $servico->addMultiOptions(array('' => ''));
        foreach ($rows as $v) {
            $servico->addMultiOptions(array($v['TPSE_ID_TP_SERVICO'] => $v["TPSE_DS_TP_SERVICO"]));
        }

        $this->addElements(array($servico));
    }

}