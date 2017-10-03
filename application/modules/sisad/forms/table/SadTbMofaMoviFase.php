<?php

class Sisad_Form_Table_SadTbMofaMoviFase extends Zend_Form {

    public function init() {
        /*
         * Todos os campos do banco
         * com suas respectivas regras
         */
        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->setRequired(true)
                ->addValidator('StringLength', false, array(0, 20));

        $mofa_dh_fase = new Zend_Form_Element_Hidden('MOFA_DH_FASE');
        $mofa_dh_fase->setRequired(true);

        $mofa_id_fase = new Zend_Form_Element_Hidden('MOFA_ID_FASE');
        $mofa_id_fase->setRequired(true)
                ->addValidator('StringLength', false, array(0, 4));

        $mofa_cd_matricula = new Zend_Form_Element_Hidden('MOFA_CD_MATRICULA');
        $mofa_cd_matricula->setRequired(true)
                ->addValidator('StringLength', false, array(0, 14));

        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(false)
                ->addValidator('StringLength', false, array(0, 4000))
                ->setLabel('Descrição da Fase:');

        $this->addElements(array($mofa_id_movimentacao
            , $mofa_dh_fase
            , $mofa_id_fase
            , $mofa_cd_matricula
            , $mofa_ds_complemento));
    }

}