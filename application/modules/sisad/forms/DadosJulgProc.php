<?php

class Sisad_Form_DadosJulgProc extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setMethod('post');

        /*
         * TODOS OS CAMPOS ESTAO CONFIGURADOS PARA SEREM USADOS COMO ARRAY
         * EX: <input type='text' name='nome[]'/>
         */

        /* INICIO CAMPOS HIDENS */
        //ID DISTRIBUICAO
        $hdpa_id_distribuicao = new Zend_Form_Element_Hidden('HDPA_ID_DISTRIBUICAO');
        $hdpa_id_distribuicao->setIsArray(true);
        /* FIM CAMPOS HIDENS */

        //ID DO PROCESSO ADMINISTRATIVO
        $hdpa_cd_proc_administrativo = new Zend_Form_Element_Text('HDPA_CD_PROC_ADMINISTRATIVO');
        $hdpa_cd_proc_administrativo->setRequired(true)
                ->removeDecorator('label')
                ->setAttrib('maxlength', 20)
                ->setIsArray(true);

        //ID DO PROCESSO FSPR
        $hdpa_id_proc_fspr = new Zend_Form_Element_Text('HDPA_ID_PROC_FSPR');
        $hdpa_id_proc_fspr->setRequired(true)
                ->removeDecorator('label')
                ->setAttrib('maxlength', 20)
                ->setIsArray(true);

        //DATA DA DISTRIBUICAO
        $hdpa_ts_distribuicao = new Zend_Form_Element_Text('HDPA_TS_DISTRIBUICAO');
        $hdpa_ts_distribuicao->setRequired(true)
                ->removeDecorator('label')
                ->setIsArray(true);

        //DATA DO JULGAMENTO
        $hdpa_dt_julgamento = new Zend_Form_Element_Text('HDPA_DT_JULGAMENTO');
        $hdpa_dt_julgamento->setRequired(true)
                ->removeDecorator('label')
                ->setIsArray(true);

        //RESUMO DA DECISAO
        $hdpa_ds_resumo_decisao = new Zend_Form_Element_Text('HDPA_DS_RESUMO_DECISAO');
        $hdpa_ds_resumo_decisao->setRequired(true)
                ->removeDecorator('label')
                ->setAttrib('maxlength', 4000)
                ->setIsArray(true);

        /* ####DATAS DE PUBLICACAO#### */
        //DIARIO DA JUSTICAO
        $hdpa_dt_public_julgamento_dj = new Zend_Form_Element_Text('HDPA_DT_PUBLIC_JULGAMENTO_DJ');
        $hdpa_dt_public_julgamento_dj->setRequired(true)
                ->removeDecorator('label')
                ->setIsArray(true);

        //DIARIO DA JUSTICAO
        $hdpa_dt_public_julgamento_bs = new Zend_Form_Element_Text('HDPA_DT_PUBLIC_JULGAMENTO_BS');
        $hdpa_dt_public_julgamento_bs->setRequired(true)
                ->removeDecorator('label')
                ->setIsArray(true);



        $this->addElements(array($hdpa_id_distribuicao,
            $hdpa_cd_proc_administrativo,
            $hdpa_id_proc_fspr,
            $hdpa_ts_distribuicao,
            $hdpa_dt_julgamento,
            $hdpa_ds_resumo_decisao,
            $hdpa_dt_public_julgamento_dj,
            $hdpa_dt_public_julgamento_bs));
    }

}