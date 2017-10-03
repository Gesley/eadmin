<?php

class Sosti_Form_Sesi extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');

        $tabelaSesi = new Application_Model_DbTable_SosTbSesiServicoSistema();
        $sesi = $tabelaSesi->getServicoSistema();

        $sesi_id_servico_sistema = new Zend_Form_Element_Text('SESI_ID_SERVICO_SISTEMA');
        $sesi_id_servico_sistema->setLabel('Id Serviço Sistema')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $sesi_nm_servico_sistema = new Zend_Form_Element_Select('SESI_NM_SERVICO_SISTEMA');
        $sesi_nm_servico_sistema->setLabel('Serviço Sistema')
                ->addMultiOptions(array('' => 'Escolha o serviço sistema'));
//                foreach ($sesi as $servicosistema) {
//                    $sesi_nm_servico_sistema->addMultiOptions(array($servicosistema['SESI_ID_SERVICO_SISTEMA'] => $servicosistema['SESI_NM_SERVICO_SISTEMA']));
//                }


        $sesi_ds_servico_sistema = new Zend_Form_Element_Text('SESI_DS_SERVICO_SISTEMA');
        $sesi_ds_servico_sistema->setLabel('Ds Serviço Sistema')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $this->addElements(array($sesi_nm_servico_sistema));
    }

}
