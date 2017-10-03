<?php
class Sisad_Form_Autuardcmto extends Zend_Form {

   public function init()
   {
        $this->setAction('')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post')
                ->setName('autuarForm');

        $osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumento();

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacao();

        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $arraypctt = $mapperPctt->getPCTT();


        $SadTbTpsdTipoSituacaoDoc = new Application_Model_DbTable_SadTbTpsdTipoSituacaoDoc();

        $SadTbConfConfidencialidade = new Application_Model_DbTable_SadTbConfConfidencialidade();
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('FormAutuar');

        
        $prdi_id_proc_fspr = new Zend_Form_Element_Hidden('PRDI_ID_PROCESSO_DIGITAL');
        $prdi_id_proc_fspr->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $prdi_dh_autuacao = new Zend_Form_Element_Hidden('PRDI_DH_AUTUACAO');
        $prdi_dh_autuacao->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $prdi_cd_matr_autuador = new Zend_Form_Element_Hidden('PRDI_CD_MATR_AUTUADOR');
        $prdi_cd_matr_autuador->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $prdi_sg_secao_autuadora = new Zend_Form_Element_Hidden('PRDI_SG_SECAO_AUTUADORA');
        $prdi_sg_secao_autuadora->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $prdi_cd_unid_autuadora = new Zend_Form_Element_Hidden('PRDI_CD_UNID_AUTUADORA');
        $prdi_cd_unid_autuadora->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $relator = new Zend_Form_Element_Radio('RELATOR');
        $relator->setLabel('Relator:')
                ->setRequired(false)
                ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não'))
                ->setValue('N');
        
        $prdi_cd_matr_serv_relator = new Zend_Form_Element_Text('PRDI_CD_MATR_SERV_RELATOR');
        $prdi_cd_matr_serv_relator->setRequired(false)
                                   ->setLabel('Servidor Relator do Processo: ')
                                   ->setAttrib('style', 'width: 540px;');
        
        $prdi_id_aqvp = new Zend_Form_Element_Select('PRDI_ID_AQVP');
         $prdi_id_aqvp->setRequired(true)
                      ->setLabel('*Assunto do Processo')
                      ->addFilter('StripTags')
                      ->setOptions(array('style' => 'width:500px'))
                      ->addFilter('StringTrim')
                      ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.')
                      ->addValidator('NotEmpty')
                      ->addMultiOptions(array('' => ''));
        foreach ($arraypctt as $arraypctt_p):
            $prdi_id_aqvp->addMultiOptions(array($arraypctt_p["AQVP_ID_PCTT"] => $arraypctt_p["DESCRICAO_PCTT"]));
        endforeach;
        
        
        $prdi_ds_texto_autuacao = new Zend_Form_Element_Textarea('PRDI_DS_TEXTO_AUTUACAO');
        $prdi_ds_texto_autuacao->setRequired(true)
                            ->setLabel('*Objeto do Processo:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(5, 4000));

        $prdi_cd_juiz_relator_processo = new Zend_Form_Element_Text('PRDI_CD_JUIZ_RELATOR_PROCESSO');
        $prdi_cd_juiz_relator_processo->setRequired(false)
                                   ->setLabel('Juiz Relator do Processo: ')
                                   ->setAttrib('style', 'width: 540px;');
        
        $prdi_dh_distribuicao = new Zend_Form_Element_Hidden('PRDI_DH_DISTRIBUICAO');
        $prdi_dh_distribuicao->setRequired(false)
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');
        

        $prdi_cd_matr_distribuicao = new Zend_Form_Element_Hidden('PRDI_CD_MATR_DISTRIBUICAO');
        $prdi_cd_matr_distribuicao->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');
        
        
        $prdi_ic_tp_distribuicao = new Zend_Form_Element_Hidden('PRDI_IC_TP_DISTRIBUICAO');
        $prdi_ic_tp_distribuicao->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');
        
//        $prdi_ic_sigiloso = new Zend_Form_Element_Checkbox('PRDI_IC_SIGILOSO');
//        $prdi_ic_sigiloso->setLabel('Sigiloso:')
//                        ->addFilter('StripTags')
//                        ->addFilter('StringTrim')
//                        ->addValidator('NotEmpty')
//                        ->setCheckedValue('S')
//                        ->setUncheckedValue('N');;
//                        
        $prdi_ic_sigiloso = new Zend_Form_Element_Hidden('PRDI_IC_SIGILOSO');
        $prdi_ic_sigiloso->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');
        
        $prdi_ic_cancelado = new Zend_Form_Element_Hidden('PRDI_IC_CANCELADO');
        $prdi_ic_cancelado->setRequired(false)
                                   ->removeDecorator('Label')
                                   ->removeDecorator('HtmlTag');
        
        $salvar = new Zend_Form_Element_Submit('Salvar');
        //$gerar->removeDecorator('DtDdWrapper');
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios')
                    ->setAttrib('style', 'display: none;');
        
        /*
         * Tabela de Documentos
         */
        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave->setRequired(true)
                            ->setLabel('*Palavras Chave:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(3, 500));


//        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll('CONF_ID_CONFIDENCIALIDADE =  0 OR CONF_ID_CONFIDENCIALIDADE =  4','CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll("CONF_ID_CONFIDENCIALIDADE IN (0,1,4)",'CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
        $docm_id_confidencialidade = new Zend_Form_Element_Select('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade->setRequired(true)
                ->setLabel('*Confidencialidade');
        foreach ($ConfConfidencialidade_array as $ConfConfidencialidade_array_p):
            $docm_id_confidencialidade->addMultiOptions(array($ConfConfidencialidade_array_p["CONF_ID_CONFIDENCIALIDADE"] => $ConfConfidencialidade_array_p["CONF_DS_CONFIDENCIALIDADE"]));
        endforeach;

        $TpsdTipoSituacaoDoc_array = $SadTbTpsdTipoSituacaoDoc->fetchAll()->toArray();
        $docm_id_tipo_situacao_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc->setRequired(true)
                ->setLabel('*Estado do Processo');
        foreach ($TpsdTipoSituacaoDoc_array as $TpsdTipoSituacaoDoc_array_p):
            $docm_id_tipo_situacao_doc->addMultiOptions(array($TpsdTipoSituacaoDoc_array_p["TPSD_ID_TIPO_SITUACAO_DOC"] => $TpsdTipoSituacaoDoc_array_p["TPSD_DS_TIPO_SITUACAO_DOC"]));
        endforeach;

// novo controle
        $sidp_cd_matricula_visualizacao = new Zend_Form_Element_Select('SIDP_CD_MATRICULA_VISUALIZACAO');
        $sidp_cd_matricula_visualizacao->setRequired(false)
                                   ->setLabel('Busca pela Pessoa: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte da lista que a mesma será adicionada à lista. Ex.: TR22 ou Maria');
// novo controle        
        
        $sidp_cd_matricula_visualizacao = new Zend_Form_Element_Text('SIDP_CD_MATRICULA_VISUALIZACAO');
        $sidp_cd_matricula_visualizacao->setRequired(false)
                                   ->setLabel('Busca pela Pessoa: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte da lista que a mesma será adicionada à lista. Ex.: TR22 ou Maria');
        
        
        $this->addElements(array($acao,
                                 $prdi_id_proc_fspr,
                                 $prdi_dh_autuacao,
                                 $prdi_cd_matr_autuador,
                                 $prdi_sg_secao_autuadora,
                                 $prdi_cd_unid_autuadora,
                                 $prdi_id_aqvp,
                                 $prdi_ds_texto_autuacao,
                                 $relator,
                                 $prdi_cd_matr_serv_relator,
                                 $prdi_cd_juiz_relator_processo,
                                 $prdi_dh_distribuicao,
                                 $prdi_cd_matr_distribuicao,
                                 $prdi_ic_tp_distribuicao,
                                 $prdi_ic_sigiloso,
                                 $prdi_ic_cancelado,
                                 $docm_ds_palavra_chave,
                                 $docm_id_confidencialidade,
                                 $docm_id_tipo_situacao_doc,
                                 $sidp_cd_matricula_visualizacao,
                                 $salvar,
                                 $obrigatorio
                                 ));

     }

}
