<?php
class Sisad_Form_Pesquisadcmto extends Zend_Form 
{
    
    private function _getBaseUrl()
    {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        if (!$baseUrl) 
        {
          $baseUrl = rtrim(preg_replace( '/([^\/]*)$/', '', $_SERVER['PHP_SELF'] ), '/\\');
        }
        
        return $baseUrl;
    }
    
    
   public function init()
   {
        $this->setAction($this->_getBaseUrl() .'/sisad/pesquisadcmto')
             ->setMethod('post')
             ->setName('pesquisaSisad');
        $osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumentoPesq();

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rhCentralLotacao->getSecoestrf1();
        $lotacao = $rhCentralLotacao->getLotacao();

        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $arraypctt = $mapperPctt->getPCTT();

        $SadTbTpsdTipoSituacaoDoc = new Application_Model_DbTable_SadTbTpsdTipoSituacaoDoc();

        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO_1');
        $trf1_secao->setLabel('TRF1/Seção:')
                     ->setRequired(false)
                     ->setAttrib('style', 'width: 500px; ')
                     ->addMultiOptions(array(''=>''));
        foreach($secao as $v){
           $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"].'|'.$v["LOTA_COD_LOTACAO"].'|'.$v["LOTA_TIPO_LOTACAO"]=>$v["LOTA_DSC_LOTACAO"]));
        }

        $docm_cd_documento = new Zend_Form_Element_Text('DOCM_ID_DOCUMENTO');
        $docm_cd_documento->setRequired(false)
                          ->setOptions(array('style' => 'width:500px'))
                          ->setValue("")
                          ->addFilter('Alnum')
                          ->setLabel('Número do Documento:');

        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');

        $docm_nr_sequencial_doc = new Zend_Form_Element_Hidden('DOCM_NR_SEQUENCIAL_DOC');
        $docm_nr_sequencial_doc->setRequired(false)
                          ->addFilter('Int')
                          ->setValue("")
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setLabel('Cadastrante: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 500px;');

        $docm_nr_dcmto_usuario = new Zend_Form_Element_Text('DOCM_NR_DCMTO_USUARIO');
        $docm_nr_dcmto_usuario->setRequired(false)
                          ->addFilter('Alnum')
                          ->setValue("")
                          ->setLabel('Número do Documento Usuário:')
                          ->setOptions(array('maxLength' => 24));

        $docm_dh_cadastro = new Zend_Form_Element_Hidden('DOCM_DH_CADASTRO');
        $docm_dh_cadastro->setRequired(false)
                         ->setValue("")
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');

//        $docm_cd_matricula_cadastro = new Zend_Form_Element_Hidden('DOCM_CD_MATRICULA_CADASTRO');
//        $docm_cd_matricula_cadastro->setRequired(false)
//                                   ->setValue("")
//                                   ->removeDecorator('Label')
//                                   ->removeDecorator('HtmlTag');
                         
        $docm_id_tipo_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setRequired(false)
                         ->setValue("0")
                         ->setLabel('Tipo Documento')
                         ->addMultiOptions(array(':: Selecione ::'));
        foreach ($tipodoc as $tipodoc_p):
            $docm_id_tipo_doc->addMultiOptions(array($tipodoc_p["DTPD_ID_TIPO_DOC"] => $tipodoc_p["DTPD_NO_TIPO"]));
        endforeach;


        $docm_sg_secao_geradora = new Zend_Form_Element_Hidden('DOCM_SG_SECAO_GERADORA');
        $docm_sg_secao_geradora->setRequired(false)
                               ->setValue("")
                               ->removeDecorator('Label')
                               ->removeDecorator('HtmlTag');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setRequired(false)
                ->setLabel('Unidade Emissora.')
                ->setValue("")
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        
        /*De acordo com a Portaria é unidade Emissora.*/
//        foreach ($lotacao as $lotacao_p):
//            $docm_cd_lotacao_geradora->addMultiOptions(array($lotacao_p["LOTA_COD_LOTACAO"] => $lotacao_p["LOTA_SIGLA_LOTACAO"] . " - " . $lotacao_p["LOTA_DSC_LOTACAO"]));
//        endforeach;

        $docm_sg_secao_redatora = new Zend_Form_Element_Hidden('DOCM_SG_SECAO_REDATORA');
        $docm_sg_secao_redatora->setRequired(false)
                          ->setValue("")
                          ->setOptions(array('style' => 'width:500px'))
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_cd_lotacao_redatora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_REDATORA');
        $docm_cd_lotacao_redatora->setRequired(false)
                                ->setValue("")
                                ->setLabel('Unidade Redatora.')
                                ->addFilter('StripTags')
                                ->setOptions(array('style' => 'width:500px'))
                                ->addFilter('StringTrim')
                                ->addValidator('NotEmpty')
                                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');

//        foreach ($lotacao as $lotacao_p):
//            $docm_cd_lotacao_redatora->addMultiOptions(array($lotacao_p["LOTA_COD_LOTACAO"] => $lotacao_p["LOTA_SIGLA_LOTACAO"] . " - " . $lotacao_p["LOTA_DSC_LOTACAO"]));
//        endforeach;

         $docm_id_pctt = new Zend_Form_Element_Select('DOCM_ID_PCTT');
         $docm_id_pctt->setRequired(false)
                      ->setValue("")
                      ->setLabel('Assunto do Documento')
                      ->addFilter('StripTags')
                      ->setOptions(array('style' => 'width:500px'))
                      ->addFilter('StringTrim')
                      ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.')
                      ->addValidator('NotEmpty')
                      ->addMultiOptions(array('' => ''));
                      
        foreach ($arraypctt as $arraypctt_p):
            $docm_id_pctt->addMultiOptions(array($arraypctt_p["AQVP_ID_PCTT"] => $arraypctt_p["DESCRICAO_PCTT"]));
        endforeach;

        $docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
        $docm_ds_hash_red->setLabel('Inserir Documento:')
                ->setRequired(false)
                ->setValue("")

//                ->addValidator(new Zend_Validate_File_Extension(array('pdf')))
                ->addValidator('Size', false, 10240000) // limit to 1m
                ->setMaxFileSize(10240000)
                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('Somente serão aceitos arquivos com o tamanho máximo de 50 Megas.');

        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(false)
                            ->setValue("")
                            ->setLabel('Descrição:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(5, 4000));
        
        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave->setRequired(false)
                            ->setLabel('Palavras-Chave:')
                            ->setValue("")
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 735px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(3, 500))
                             ->setDescription('Separe-as usando vírgula.');


//        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll('CONF_ID_CONFIDENCIALIDADE =  0 OR CONF_ID_CONFIDENCIALIDADE =  4','CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
//        $ConfConfidencialidade_array = $SadTbConfConfidencialidade->fetchAll(null,'CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
//        $docm_id_confidencialidade = new Zend_Form_Element_Select('DOCM_ID_CONFIDENCIALIDADE');
//        $docm_id_confidencialidade->setRequired(true)
//                                  ->setLabel('Confidencialidade')
//                                  ->addMultiOptions(array(':: Selecione ::'));
//        foreach ($ConfConfidencialidade_array as $ConfConfidencialidade_array_p):
//            $docm_id_confidencialidade->addMultiOptions(array($ConfConfidencialidade_array_p["CONF_ID_CONFIDENCIALIDADE"] => $ConfConfidencialidade_array_p["CONF_DS_CONFIDENCIALIDADE"]));
//        endforeach;
        
        $docm_id_confidencialidade = new Zend_Form_Element_Hidden('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade->setValue("")
                                  ->removeDecorator('Label')
                                  ->removeDecorator('HtmlTag');

        $TpsdTipoSituacaoDoc_array = $SadTbTpsdTipoSituacaoDoc->fetchAll()->toArray();
        $docm_id_tipo_situacao_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc->setRequired(false)
                ->setValue("0")
                ->setLabel('Estado do documento')
                ->addMultiOptions(array(':: Selecione ::'));
        foreach ($TpsdTipoSituacaoDoc_array as $TpsdTipoSituacaoDoc_array_p):
            $docm_id_tipo_situacao_doc->addMultiOptions(array($TpsdTipoSituacaoDoc_array_p["TPSD_ID_TIPO_SITUACAO_DOC"] => $TpsdTipoSituacaoDoc_array_p["TPSD_DS_TIPO_SITUACAO_DOC"]));
        endforeach;

        $docm_nr_documento_red = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO_RED');
        $docm_nr_documento_red->setRequired(false)
                          ->setValue("")
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $salvar = new Zend_Form_Element_Submit('Pesquisar');

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('Campos Obrigatorio')
                    ->setAttrib('style', 'display: none;');
        
        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial->setLabel('Data inicial:')
                     ->setValue("");
       
        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setLabel('Data final:')
                   ->setValue("");
      
        $this->addElements(array($trf1_secao,
                                 $docm_cd_documento,
                                 $docm_nr_documento,
                                 $docm_nr_sequencial_doc,
                                 $docm_dh_cadastro,
                                 $docm_cd_matricula_cadastro,
                                 $docm_sg_secao_geradora,
                                 $docm_cd_lotacao_geradora,
                                 $docm_sg_secao_redatora,
                                 $docm_cd_lotacao_redatora,
                                 $docm_id_tipo_doc,
                                 $docm_nr_dcmto_usuario,
                                 $docm_id_pctt,
                                 $docm_ds_palavra_chave,
                                 $docm_id_tipo_situacao_doc,
                                 $docm_id_confidencialidade,
                                 $docm_nr_documento_red,
                                 $data_inicial,
                                 $data_final,
                                 $salvar
                                 ));

     }

}