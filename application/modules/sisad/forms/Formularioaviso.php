<?php
class Sisad_Form_Formularioaviso extends Zend_Form {

   public function init()
   {
        $this->setAction('save')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post')
             ->setName('Formularioaviso');

        $userNamespace = new Zend_Session_Namespace('userNs'); 
        
        $osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumentoAviso();

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacao();

        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $arraypctt = $mapperPctt->getPCTT();



        $docm_cd_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $docm_cd_documento->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_nr_sequencial_doc = new Zend_Form_Element_Hidden('DOCM_NR_SEQUENCIAL_DOC');
        $docm_nr_sequencial_doc->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_dh_cadastro = new Zend_Form_Element_Hidden('DOCM_DH_CADASTRO');
        $docm_dh_cadastro->setRequired(false)
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');

        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setRequired(false)
                                   ->setLabel('Solicitante/Assinante: ')
                                   ->setValue(($userNamespace->nome)?($userNamespace->nome.' - '.$userNamespace->matricula):('Digite/Selecione um nome'))
                                   ->setOptions(array('style' => 'width:500px'))
                                   ->removeDecorator('HtmlTag')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres.');
                         
        $docm_id_tipo_doc = new Zend_Form_Element_Text('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setRequired(false)
                         ->setLabel('*Tipo Documento')
                         ->setAttrib('readonly', 'readonly')
                         ->setValue('Aviso');


        $docm_sg_secao_geradora = new Zend_Form_Element_Hidden('DOCM_SG_SECAO_GERADORA');
        $docm_sg_secao_geradora->setRequired(false)
                               ->removeDecorator('Label')
                               ->removeDecorator('HtmlTag');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setRequired(true)
                ->setLabel('*Unidade Emissora.')
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');

        $docm_sg_secao_redatora = new Zend_Form_Element_Hidden('DOCM_SG_SECAO_REDATORA');
        $docm_sg_secao_redatora->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_cd_lotacao_redatora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_REDATORA');
        $docm_cd_lotacao_redatora->setRequired(true)
                                ->setLabel('*Unidade Redatora.')
                                ->addFilter('StripTags')
                                ->setOptions(array('style' => 'width:500px'))
                                ->addFilter('StringTrim')
                                ->addValidator('NotEmpty')
                                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        

         $docm_id_pctt = new Zend_Form_Element_Select('DOCM_ID_PCTT');
         $docm_id_pctt->setRequired(true)
                      ->setLabel('*Assunto do Documento')
                      ->addFilter('StripTags')
                      ->setOptions(array('style' => 'width:500px'))
                      ->addFilter('StringTrim')
                      ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.')
                      ->addValidator('NotEmpty')
                      ->addMultiOptions(array('' => ''));
                      
        foreach ($arraypctt as $arraypctt_p):
            $docm_id_pctt->addMultiOptions(array($arraypctt_p["AQVP_ID_PCTT"] => $arraypctt_p["DESCRICAO_PCTT"]));
        endforeach;

        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(true)
                            ->setLabel('*Texto do Documento:')
                             //->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                            ->setAttrib('id', 'textArea1')
                             ->addValidator('NotEmpty');
                             //->addValidator('StringLength', false, array(0, 10000));
        

        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave->setRequired()
                            ->setLabel('*Palavras Chave:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('id', 'elm2')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(3, 500));


//        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll('CONF_ID_CONFIDENCIALIDADE =  0 OR CONF_ID_CONFIDENCIALIDADE =  4','CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
//        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll(null,'CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
        $docm_id_confidencialidade = new Zend_Form_Element_Text('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade->setRequired(false)
                ->setLabel('Confidencialidade')
                ->setAttrib('readonly', 'readonly')
                ->setValue('Público');
//       foreach ($ConfConfidencialidade_array as $ConfConfidencialidade_array_p):
//            $docm_id_confidencialidade->addMultiOptions(array($ConfConfidencialidade_array_p["CONF_ID_CONFIDENCIALIDADE"] => $ConfConfidencialidade_array_p["CONF_DS_CONFIDENCIALIDADE"]));
//        endforeach;

//        $TpsdTipoSituacaoDoc_array = $SadTbTpsdTipoSituacaoDoc->fetchAll()->toArray();
        $docm_id_tipo_situacao_doc = new Zend_Form_Element_Text('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc->setRequired(false)
                ->setLabel('Estado do documento')
                ->setAttrib('readonly', 'readonly')
                ->setValue('Digital');

        foreach ($TpsdTipoSituacaoDoc_array as $TpsdTipoSituacaoDoc_array_p):
            $docm_id_tipo_situacao_doc->addMultiOptions(array($TpsdTipoSituacaoDoc_array_p["TPSD_ID_TIPO_SITUACAO_DOC"] => $TpsdTipoSituacaoDoc_array_p["TPSD_DS_TIPO_SITUACAO_DOC"]));
        endforeach;

        $docm_nr_documento_red = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO_RED');
        $docm_nr_documento_red->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $salvar = new Zend_Form_Element_Submit('Salvar');
        $salvar->removeDecorator('DTDDWRAPPER')
                ->setAttrib('style', 'margin:6px 0 0 0;');

        $visualizar = new Zend_Form_Element_Submit('PréVisualizar');
        $visualizar->removeDecorator('DTDDWRAPPER');
        //$gerar->removeDecorator('DtDdWrapper');
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatorio')
                    ->setAttrib('style', 'display: none;');
       
        $this->addElements(array($docm_cd_documento,
                                 $docm_nr_documento,
                                 $docm_nr_sequencial_doc,
                                 $docm_dh_cadastro,
                                 $docm_sg_secao_geradora,
                                 $docm_cd_lotacao_geradora,
                                 $docm_sg_secao_redatora,
                                 $docm_cd_lotacao_redatora,
                                 $docm_id_tipo_doc,
                                 $docm_id_pctt,
                                 $docm_ds_assunto_doc,
                                 $docm_ds_palavra_chave,
                                 $docm_cd_matricula_cadastro,
                                 $docm_id_tipo_situacao_doc,
                                 $docm_id_confidencialidade,
                                 $docm_nr_documento_red,
                                 $docm_ds_hash_red,
                                 $salvar,
                                 $visualizar,
                                 $obrigatorio
                                 ));

     }

}