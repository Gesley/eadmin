<?php

class Sisad_Form_Table_SadTbVipdVincProcDigital extends Zend_Form {

    public function init() {

        //SAD_TB_VIPD_VINC_PROC_DIGITAL

        /*
          VIPD_ID_VINCULACAO_PROCESSO    NUMBER(20,0)          NOT NULL
          VIPD_ID_PROCESSO_DIGITAL_PRINC NUMBER(20,0)          NOT NULL
          VIPD_ID_PROCESSO_DIGITAL_VINDO NUMBER(20,0)          NOT NULL
          VIPD_ID_TP_VINCULACAO          NUMBER(3,0)           NOT NULL
          VIPD_DH_VINCULACAO             DATE                  NOT NULL
          VIPD_CD_MATR_VINCULACAO        VARCHAR2(14)          NOT NULL
          VIPD_NR_VOL_PRINCIPAL          NUMBER(5,0)
          VIPD_NR_FOLHA_PRINCIPAL        NUMBER(5,0)
          VIPD_IC_ATIVO                  VARCHAR2(1)           NOT NULL
         */

        $vipd_id_vinculacao_processo = new Zend_Form_Element_Hidden('VIPD_ID_VINCULACAO_PROCESSO');
        $vipd_id_vinculacao_processo->setRequired(true)
                ->addValidator('StringLength', false, array(1, 20));

        $vipd_id_processo_digital_princ = new Zend_Form_Element_Hidden('VIPD_ID_PROCESSO_DIGITAL_PRINC');
        $vipd_id_processo_digital_princ->setRequired(true)
                ->addValidator('StringLength', false, array(1, 20));

        $vipd_id_processo_digital_vindo = new Zend_Form_Element_Hidden('VIPD_ID_PROCESSO_DIGITAL_VINDO');
        $vipd_id_processo_digital_vindo->setRequired(true)
                ->addValidator('StringLength', false, array(1, 20));

        $vipd_id_tp_vinculacao = new Zend_Form_Element_Select('VIPD_ID_TP_VINCULACAO');
        $vipd_id_tp_vinculacao->setRequired(true)
                ->addValidator('StringLength', false, array(1, 20))
                ->setLabel('Selecione o tipo de juntada:');

        $vipd_dh_vinculacao = new Zend_Form_Element_Text('VIPD_DH_VINCULACAO');
        $vipd_dh_vinculacao->setRequired(true);

        $vipd_cd_matr_vinculacao = new Zend_Form_Element_Text('VIPD_CD_MATR_VINCULACAO');
        $vipd_cd_matr_vinculacao->setRequired(true)
                ->addValidator('StringLength', false, array(1, 14));

        $vipd_nr_vol_principal = new Zend_Form_Element_Text('VIPD_NR_VOL_PRINCIPAL');
        $vipd_nr_vol_principal->addValidator('StringLength', false, array(1, 5));

        $vipd_nr_folha_principal = new Zend_Form_Element_Text('VIPD_NR_FOLHA_PRINCIPAL');
        $vipd_nr_folha_principal->addValidator('StringLength', false, array(1, 5));

        $vipd_ic_ativo = new Zend_Form_Element_Checkbox('VIPD_IC_ATIVO');
        $vipd_ic_ativo->setRequired(true)
                ->setLabel('Ativo:')
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');


        $this->addElements(array(
            $vipd_id_vinculacao_processo,
            $vipd_id_processo_digital_princ,
            $vipd_id_processo_digital_vindo,
            $vipd_id_tp_vinculacao,
            $vipd_dh_vinculacao,
            $vipd_cd_matr_vinculacao,
            $vipd_nr_vol_principal,
            $vipd_nr_folha_principal,
            $vipd_ic_ativo
        ));
    }

}