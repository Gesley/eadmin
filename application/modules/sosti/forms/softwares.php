<?php

class Sosti_Form_softwares extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setMethod('post')
                ->setName('softwarelista');

        $softwareobj = new Application_Model_DbTable_SosTbLsfwSoftware ();

        $software = new Zend_Form_Element_Select('SOFTWARE');
        $software->setLabel('Digite o Software:');
        $software->setOptions(array('style' => 'width: 490px'));
        $rows = $softwareobj->fetchAll(null, array('LSFW_DS_SOFTWARE  ASC'));
        $software->addMultiOptions(array('' => ''));
        foreach ($rows as $v) {
            $qtd_total = $softwareobj->getQtdTotalSoftware($v['LSFW_ID_SOFTWARE']);
            $qtd_saida = $softwareobj->getQtdLicencasSaida($v['LSFW_ID_SOFTWARE']);
            $qtd = (int) $qtd_total['QTD_TOTAL'] - (int) $qtd_saida['QTD_SAIDA'];
            if ($qtd > 0) {
                $software->addMultiOptions(array($v['LSFW_ID_SOFTWARE'] => $v["LSFW_DS_SOFTWARE"]));
            }
        }
        $this->addElements(array($software));
    }

}

?>