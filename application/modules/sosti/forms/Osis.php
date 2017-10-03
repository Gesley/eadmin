<?php

class Sosti_Form_Osis extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');

        $tabelaOsis = new Application_Model_DbTable_SosTbOsisOcorrenciaSistema();
        $tipoOcorrencias = $tabelaOsis->getOcorrencias();

        $osis_id_ocorrencia = new Zend_Form_Element_Text('OSIS_ID_OCORRENCIA');
        $osis_id_ocorrencia->setLabel('Id Ocorrencia')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $osis_nm_ocorrencia = new Zend_Form_Element_Select('OSIS_NM_OCORRENCIA');
        $osis_nm_ocorrencia->setLabel('Tipo de Ocorrência')
                ->setRequired(false)
                ->addMultiOptions(array('' => 'Primeiro escolha o Tipo de Ocorrência'));
        foreach ($tipoOcorrencias as $ocorrencias) {
            $osis_nm_ocorrencia->addMultiOptions(array($ocorrencias["OSIS_ID_OCORRENCIA"] => $ocorrencias["OSIS_NM_OCORRENCIA"]));
        }

        $osis_ds_ocorrencia = new Zend_Form_Element_Text('OSIS_DS_OCORRENCIA');
        $osis_ds_ocorrencia->setLabel('Nm Ocorrência')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');


        $this->addElements(array($osis_nm_ocorrencia));
    }

}
