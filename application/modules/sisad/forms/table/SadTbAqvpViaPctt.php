<?php

class Sisad_Form_Table_SadTbAqvpViaPctt extends Zend_Form {

    public function init() {
        /*
         * Todos os campos do banco
         * com suas respectivas regras
         */

        $aqvp_id_pctt = new Zend_Form_Element_Text('AQVP_ID_PCTT');
        $aqvp_id_pctt->setRequired(true)
                ->addValidator('StringLength', false, array(1, 7));

        $aqvp_id_aqat = new Zend_Form_Element_Text('AQVP_ID_AQAT');
        $aqvp_id_aqat->setRequired(true)
                ->addValidator('StringLength', false, array(1, 6));

        $aqvp_cd_aqvi = new Zend_Form_Element_Text('AQVP_CD_AQVI');
        $aqvp_cd_aqvi->setRequired(false)
                ->addValidator('StringLength', false, array(0, 2));

        $aqvp_cd_pctt = new Zend_Form_Element_Text('AQVP_CD_PCTT');
        $aqvp_cd_pctt->setRequired(true)
                ->addValidator('StringLength', false, array(1, 13));

        $aqvp_ic_mais_vias = new Zend_Form_Element_Text('AQVP_IC_MAIS_VIAS');
        $aqvp_ic_mais_vias->setRequired(false)
                ->addValidator('StringLength', false, array(0, 1));

        $aqvp_cd_aqde_ini = new Zend_Form_Element_Text('AQVP_CD_AQDE_INI');
        $aqvp_cd_aqde_ini->setRequired(false)
                ->addValidator('StringLength', false, array(0, 3));

        $aqvp_cd_aqte_cor = new Zend_Form_Element_Text('AQVP_CD_AQTE_COR');
        $aqvp_cd_aqte_cor->setRequired(false)
                ->addValidator('StringLength', false, array(0, 3));

        $aqvp_cd_aqte_int = new Zend_Form_Element_Text('AQVP_CD_AQTE_INT');
        $aqvp_cd_aqte_int->setRequired(false)
                ->addValidator('StringLength', false, array(0, 3));

        $aqvp_cd_aqde_fim = new Zend_Form_Element_Text('AQVP_CD_AQDE_FIM');
        $aqvp_cd_aqde_fim->setRequired(false)
                ->addValidator('StringLength', false, array(0, 3));

        $aqvp_ds_observacao = new Zend_Form_Element_Text('AQVP_DS_OBSERVACAO');
        $aqvp_ds_observacao->setRequired(false)
                ->addValidator('StringLength', false, array(0, 1000));

        $aqvp_dh_criacao = new Zend_Form_Element_Text('AQVP_DH_CRIACAO');
        $aqvp_dh_criacao->setRequired(true);

        $aqvp_dh_fim = new Zend_Form_Element_Text('AQVP_DH_FIM');
        $aqvp_dh_fim->setRequired(false)
                ->addValidator('StringLength', false, array(0, 5));

        $aqvp_id_aqvp_atual = new Zend_Form_Element_Text('AQVP_ID_AQVP_ATUAL');
        $aqvp_id_aqvp_atual->setRequired(false)
                ->addValidator('StringLength', false, array(0, 7));


        $this->addElements(array(
            $aqvp_id_pctt
            , $aqvp_id_aqat
            , $aqvp_cd_aqvi
            , $aqvp_cd_pctt
            , $aqvp_ic_mais_vias
            , $aqvp_cd_aqde_ini
            , $aqvp_cd_aqte_cor
            , $aqvp_cd_aqte_int
            , $aqvp_cd_aqde_fim
            , $aqvp_ds_observacao
            , $aqvp_dh_criacao
            , $aqvp_dh_fim
            , $aqvp_id_aqvp_atual
        ));
    }

}