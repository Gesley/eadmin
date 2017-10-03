<?php

class Sisad_Form_Table_SadTbDcprDocumentoProcesso extends Zend_Form {

    public function init() {
        /*
         * Todos os campos do banco
         * com suas respectivas regras
         */
        $dcpr_id_processo_digital = new Zend_Form_Element_Hidden('DCPR_ID_PROCESSO_DIGITAL');
        $dcpr_id_processo_digital->setRequired(true)
                ->addValidator('StringLength', false, array(0, 5));

        $dcpr_id_documento = new Zend_Form_Element_Hidden('DCPR_ID_DOCUMENTO');
        $dcpr_id_documento->setRequired(true)
                ->addValidator('StringLength', false, array(0, 20));

        $dcpr_id_tp_vinculacao = new Zend_Form_Element_Select('DCPR_ID_TP_VINCULACAO');
        $dcpr_id_tp_vinculacao->setRequired(true)
                ->setLabel('Selecione o tipo de juntada:');

        $dcpr_dh_vinculacao_doc = new Zend_Form_Element_Text('DCPR_DH_VINCULACAO_DOC');
        $dcpr_dh_vinculacao_doc->setRequired(true)
                ->setLabel('Data e Hora da juntada:');

        $dcpr_ic_ativo = new Zend_Form_Element_Checkbox('DCPR_IC_ATIVO');
        $dcpr_ic_ativo->setRequired(true)
                ->setLabel('Ativo:')
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $this->addElements(array($dcpr_id_processo_digital
            , $dcpr_id_documento
            , $dcpr_id_tp_vinculacao
            , $dcpr_dh_vinculacao_doc
            , $dcpr_ic_ativo));
    }

}