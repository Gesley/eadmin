<?php
class Soseg_Form_SolicitacaoServicosGraficos extends Zend_Form
{
    public function init()
    {
        $this->setAction('save')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs'); 
        $data = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dadosSolicitante = $data->getDadosSolicitante($userNamespace->matricula);

        $ssol_id_documento = new Zend_Form_Element_Hidden('SSOL_ID_DOCUMENTO');
        $ssol_id_documento->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $ssol_id_tipo_cad = new Zend_Form_Element_Hidden('SSOL_ID_TIPO_CAD');
        $ssol_id_tipo_cad->setValue('1')/*ON-LINE*/
                         ->addFilter('StripTags')
                         ->addFilter('StringTrim')
                         ->addValidator('NotEmpty')
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');      
        
       $ssol_nm_usuario_externo = new Zend_Form_Element_Text('SSOL_NM_USUARIO_EXTERNO');
       $ssol_nm_usuario_externo->setLabel('Solicitante:')
                               ->addFilter('StripTags')
                               ->setOptions(array('style' => 'width:500px'))
                               ->setValue($userNamespace->matricula.' - '.$userNamespace->nome)
                               ->addFilter('StringTrim')
                               ->addValidator('NotEmpty')
                               ->setAttrib('readonly', 'readonly');
        
        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade->setLabel('Unidade solicitante:')
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->setValue($userNamespace->codlotacao.' - '.$userNamespace->descicaolotacao.' - '.$userNamespace->siglalotacao.' - '.$userNamespace->siglasecao)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('readonly', 'readonly');
        
        $ssol_ed_localizacao = new Zend_Form_Element_Text('SSOL_ED_LOCALIZACAO');
        $ssol_ed_localizacao->setRequired(true)
                            ->setLabel('*Local de atendimento:')
                            ->addFilter('StripTags')
                            ->setOptions(array('style' => 'width:500px'))
                            ->setValue($dadosSolicitante[0]["SSOL_ED_LOCALIZACAO"])
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty');
        
        $ssol_ds_email_externo = new Zend_Form_Element_Text('SSOL_DS_EMAIL_EXTERNO');
        $ssol_ds_email_externo->setRequired(true)
                              ->setLabel('*E-mail:')
                              ->addFilter('StripTags')
                              ->setValue($dadosSolicitante[0]["SSOL_DS_EMAIL_EXTERNO"])
                              ->setOptions(array('style' => 'width:200px'))
                              ->addFilter('StringTrim')
                              ->addValidator('NotEmpty');
        
        $ssol_nr_telefone_externo = new Zend_Form_Element_Text('SSOL_NR_TELEFONE_EXTERNO');
        $ssol_nr_telefone_externo->setRequired(true)
                                 ->setLabel('*Telefone:')
                                 ->addFilter('StripTags')
                                 ->setValue($dadosSolicitante[0]["SSOL_NR_TELEFONE_EXTERNO"])
                                 ->setOptions(array('style' => 'width:200px'))
                                 ->addFilter('StringTrim')
                                 ->addValidator('NotEmpty');
        
        $ssol_qtde_item = new Zend_Form_Element_Text('SSOL_QT_ITEM_PEDIDO');
        $ssol_qtde_item->setRequired(true)
                                 ->setLabel('Quantidade')
                                 ->addFilter('StripTags')
                                 ->setOptions(array('style' => 'width:80px'))
                                 ->addFilters( array('StringTrim', 'Int'));
        
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicosGraficos();
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('*Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
			
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(true)
                        ->setLabel('*Serviço:')
                        ->setAttrib('style', 'width: 650PX;')
			->setDescription('A lista será carregada após digitar no mínimo três caracteres. Primeiro Escolha um Grupo de Serviço.');
       		
	
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(true)
                            ->setLabel('*Descrição do serviço:')
                            ->setDescription('Digite no mínimo 5 caracteres e no máximo 4000 caracteres. ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
                            ->setAttrib('style', 'width: 800px; height: 30px;')
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);
        
       $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
       $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
       $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
       $ssol_ds_observacao->setLabel('Observação:')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->setAttrib('style', 'width: 800px; height: 30px;')
                           ->addValidator('NotEmpty')
                           ->addValidator('StringLength', false, array(5, 500))
                           ->addFilter($Zend_Filter_HtmlEntities);

        $ssol_hh_inicio_atend = new Zend_Form_Element_Hidden('SSOL_HH_INICIO_ATEND');
        $ssol_hh_inicio_atend->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty')
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');

        $ssol_hh_final_atend = new Zend_Form_Element_Hidden('SSOL_HH_FINAL_ATEND');
        $ssol_hh_final_atend->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty')
                            ->removeDecorator('Label')
                            ->removeDecorator('HtmlTag');
        
        $docm_nr_documento_red = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO_RED');
        $docm_nr_documento_red->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $submit = new Zend_Form_Element_Submit('Salvar');
      
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios')
                    ->setAttrib('style', 'display: none;');

        $this->addElements(array($ssol_id_documento,
                                 $unidade,
                                 $ssol_nm_usuario_externo, 
                                 $ssol_nr_telefone_externo,
                                 $ssol_ds_email_externo,
                                 $ssol_ed_localizacao , 
                                 $ssol_id_tipo_cad,
                                 $sgrs_id_grupo,
                                 $sser_id_servico,
                                 $docm_ds_assunto_doc,
                                 $ssol_ds_observacao,
                                 $ssol_hh_inicio_atend, 
                                 $ssol_hh_final_atend,
                                 $docm_nr_documento_red,
                                 $ssol_qtde_item,
                                 $submit,
                                 $obrigatorio));
    }

}