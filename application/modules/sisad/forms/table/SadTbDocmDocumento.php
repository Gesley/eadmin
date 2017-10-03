<?php

class Sisad_Form_Table_SadTbDocmDocumento extends Zend_Form {

    public function init() {
        /*
         * Todos os campos do banco
         * com suas respectivas regras
         */
        $docm_id_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $docm_id_documento->setRequired(true)
                ->addValidator('StringLength', false, array(0, 20))
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $docm_nr_documento = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setRequired(false)
                ->addValidator('StringLength', false, array(0, 28));

        $docm_nr_sequencial_doc = new Zend_Form_Element_Text('DOCM_NR_SEQUENCIAL_DOC');
        $docm_nr_sequencial_doc->setRequired(false)
                ->addValidator('StringLength', false, array(0, 10));

        $docm_nr_dcmto_usuario = new Zend_Form_Element_Text('DOCM_NR_DCMTO_USUARIO');
        $docm_nr_dcmto_usuario->setRequired(false)
                ->addValidator('StringLength', false, array(0, 16));

        $docm_dh_cadastro = new Zend_Form_Element_Text('DOCM_DH_CADASTRO');
        $docm_dh_cadastro->setRequired(false);

        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setRequired(false)
                ->addValidator('StringLength', false, array(0, 14));

        $docm_id_tipo_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setRequired(false)
                ->addValidator('StringLength', false, array(0, 5));

        $docm_sg_secao_geradora = new Zend_Form_Element_Text('DOCM_SG_SECAO_GERADORA');
        $docm_sg_secao_geradora->setRequired(false)
                ->addValidator('StringLength', false, array(0, 2));

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setRequired(false)
                ->addValidator('StringLength', false, array(0, 5));

        $docm_sg_secao_redatora = new Zend_Form_Element_Text('DOCM_SG_SECAO_REDATORA');
        $docm_sg_secao_redatora->setRequired(false)
                ->addValidator('StringLength', false, array(0, 2));

        $docm_cd_lotacao_redatora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_REDATORA');
        $docm_cd_lotacao_redatora->setRequired(false)
                ->addValidator('StringLength', false, array(0, 5));

        $docm_id_pctt = new Zend_Form_Element_Select('DOCM_ID_PCTT');
        $docm_id_pctt->setRequired(false)
                ->addValidator('StringLength', false, array(0, 7));

        $docm_ds_assunto_doc = new Zend_Form_Element_Text('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(false)
                ->addValidator('StringLength', false, array(0, 4000));

        $docm_id_tipo_situacao_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc->setRequired(false)
                ->addValidator('StringLength', false, array(0, 2));

        $docm_id_confidencialidade = new Zend_Form_Element_Select('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade->setRequired(false)
                ->addValidator('StringLength', false, array(0, 2));

        $docm_nr_documento_red = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO_RED');
        $docm_nr_documento_red->setRequired(false)
                ->addValidator('StringLength', false, array(0, 20));

        $docm_dh_expiracao_documento = new Zend_Form_Element_Text('DOCM_DH_EXPIRACAO_DOCUMENTO');
        $docm_dh_expiracao_documento->setRequired(false);


        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave->setRequired(false)
                ->addValidator('StringLength', false, array(0, 500));

        $docm_ic_arquivamento = new Zend_Form_Element_Checkbox('DOCM_IC_ARQUIVAMENTO');
        $docm_ic_arquivamento->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $docm_id_pessoa = new Zend_Form_Element_Text('DOCM_ID_PESSOA');
        $docm_id_pessoa->setRequired(false)
                ->addValidator('StringLength', false, array(0, 20));

        $docm_ic_documento_externo = new Zend_Form_Element_Checkbox('DOCM_IC_DOCUMENTO_EXTERNO');
        $docm_ic_documento_externo->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $docm_ic_ativo = new Zend_Form_Element_Checkbox('DOCM_IC_ATIVO');
        $docm_ic_ativo->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $docm_ic_processo_autuado = new Zend_Form_Element_Checkbox('DOCM_IC_PROCESSO_AUTUADO');
        $docm_ic_processo_autuado->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $docm_id_movimentacao = new Zend_Form_Element_Text('DOCM_ID_MOVIMENTACAO');
        $docm_id_movimentacao->setRequired(false)
                ->addValidator('StringLength', false, array(0, 20));

        $docm_dh_fase = new Zend_Form_Element_Text('DOCM_DH_FASE');
        $docm_dh_fase->setRequired(false);

        $docm_id_documento_pai = new Zend_Form_Element_Text('DOCM_ID_DOCUMENTO_PAI');
        $docm_id_documento_pai->setRequired(false)
                ->addValidator('StringLength', false, array(0, 20));

        $docm_id_pessoa_temporaria = new Zend_Form_Element_Text('DOCM_ID_PESSOA_TEMPORARIA');
        $docm_id_pessoa_temporaria->setRequired(false)
                ->addValidator('StringLength', false, array(0, 6));

        $docm_id_tp_extensao = new Zend_Form_Element_Select('DOCM_ID_TP_EXTENSAO');
        $docm_id_tp_extensao->setRequired(false)
                ->addValidator('StringLength', false, array(0, 3));

        $docm_ic_movi_individual = new Zend_Form_Element_Checkbox('DOCM_IC_MOVI_INDIVIDUAL');
        $docm_ic_movi_individual->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $docm_ic_apensado = new Zend_Form_Element_Checkbox('DOCM_IC_APENSADO');
        $docm_ic_apensado->setRequired(false)
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $this->addElements(array(
            $docm_id_documento
            , $docm_nr_documento
            , $docm_nr_sequencial_doc
            , $docm_nr_dcmto_usuario
            , $docm_dh_cadastro
            , $docm_cd_matricula_cadastro
            , $docm_id_tipo_doc
            , $docm_sg_secao_geradora
            , $docm_cd_lotacao_geradora
            , $docm_sg_secao_redatora
            , $docm_cd_lotacao_redatora
            , $docm_id_pctt
            , $docm_ds_assunto_doc
            , $docm_id_tipo_situacao_doc
            , $docm_id_confidencialidade
            , $docm_nr_documento_red
            , $docm_dh_expiracao_documento
            , $docm_ds_palavra_chave
            , $docm_ic_arquivamento
            , $docm_id_pessoa
            , $docm_ic_documento_externo
            , $docm_ic_ativo
            , $docm_ic_processo_autuado
            , $docm_id_movimentacao
            , $docm_dh_fase
            , $docm_id_documento_pai
            , $docm_id_pessoa_temporaria
            , $docm_id_tp_extensao
            , $docm_ic_movi_individual
            , $docm_ic_apensado
        ));
    }

}