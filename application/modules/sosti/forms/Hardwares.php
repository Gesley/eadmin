<?php

class Sosti_Form_Hardwares extends Zend_Form {

    protected $secao = null;
    protected $lotacao = null;

    public function init() {
        $this->setAction('')
                ->setMethod('post')
                ->setName('hardwarelista');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();

        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('*TRF1/Seção:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 470px; ')
                ->addMultiOptions(array('' => ''));
        foreach ($secao as $v) {
            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
        }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('*Seção/Subseção:')
                ->setAttrib('style', 'width: 470px; ')
                ->setRequired(true)
                ->addMultiOptions(array('' => 'Primeiro escolha a TRF1/Seção'));

        $hardware = new Zend_Form_Element_Select('HARDWARE');
        $hardware->setLabel('Digite o Hardware:');
        $hardware->setOptions(array('style' => 'width: 490px'));
        $hardware->addMultiOptions(array('' => ''));

        $this->addElements(array($trf1_secao, $secao_subsecao, $hardware));
    }

}

?>