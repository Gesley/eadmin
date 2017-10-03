<?php
class Sosti_Form_LabConsulta extends Zend_Form
{
    public function init()
    {
$this->setAction('')
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs'); 

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
        
        $atendimento = new Zend_Form_Element_Hidden('ATENDIMENTO');
        $atendimento->setValue('I')/*ON-LINE*/
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('NotEmpty')
                    ->removeDecorator('Label')
                    ->removeDecorator('HtmlTag'); 
        
        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setLabel('Solicitante: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setLabel('Unidade Solicitante:')
                                 ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $ssol_ed_localizacao = new Zend_Form_Element_Text('SSOL_ED_LOCALIZACAO');
        $ssol_ed_localizacao->setLabel('Local de atendimento:')
                            ->addFilter('StripTags')
                            ->setOptions(array('style' => 'width:540px'))
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty');
        
        $ssol_ds_email_externo = new Zend_Form_Element_Text('SSOL_DS_EMAIL_EXTERNO');
        $ssol_ds_email_externo->setLabel('E-mail:')
                              ->addFilter('StripTags')
                              ->setOptions(array('style' => 'width:200px'))
                              ->addFilter('StringTrim')
                              ->addValidator('NotEmpty');
        
        $ssol_nr_telefone_externo = new Zend_Form_Element_Text('SSOL_NR_TELEFONE_EXTERNO');
        $ssol_nr_telefone_externo->setLabel('Telefone:')
                                 ->addFilter('StripTags')
                                 ->setOptions(array('style' => 'width:200px'))
                                 ->addFilter('StringTrim')
                                 ->addValidator('NotEmpty');
        
        $ssol_sg_tipo_tombo = new Zend_Form_Element_Text('SSOL_SG_TIPO_TOMBO');
        $ssol_sg_tipo_tombo->setValue('T')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->addValidator('NotEmpty')
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        
        $docm_nr_documento = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setLabel('Nº da Solicitação:')
                          ->setAttrib('style', 'width: 540px;')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->addValidator('Alnum');
        
        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
        $ssol_nr_tombo->setLabel('Nº do tombo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $de_mat = new Zend_Form_Element_Textarea('DE_MAT');
        $de_mat->setLabel('Descrição do Tombo')
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setValue('Primeiro informe Nº do Tombo')
               ->setAttrib('readonly', 'readonly')
               ->setAttrib('style', 'width: 540px; height: 30px;')
               ->setAttrib('class', 'erroInputSelect');
       
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao( $userNamespace->siglasecao,  $userNamespace->codlotacao );
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(false)
                      ->setLabel('*Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;        
        
        
//        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
//        $SserServico = $SosTbSserServico->fetchAll('SSER_ID_GRUPO = 1')->toArray();
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
                        ->setLabel('*Serviço:')
                        ->setAttrib('style', 'width: 650PX;');
                        //->setAttrib('disabled', 'disabled');
        //$sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
//        foreach ($SserServico as $SserServico_p):
//            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
//        endforeach;

     
        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(false)
                            ->setLabel('Descrição do serviço:')
                            ->addFilter('StripTags')
                            ->setAttrib('style', 'width: 540px; height: 30px;')
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty');
        
       $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
       $ssol_ds_observacao->setLabel('Observação:')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->setAttrib('style', 'width: 540px; height: 30px;')
                          ->addValidator('NotEmpty');
       
       $status_solicitacao = new Zend_Form_Element_Radio('STATUS_SOLICITACAO');
       $status_solicitacao->setLabel('Status da Solicitação')
                          ->addMultiOption("1014", "Avaliadas")
                          ->addMultiOption("1000", "Baixadas")
                          ->addMultiOption("9999", "Em atendimento")->setAttrib("checked", "checked");
       
       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial->setLabel('Data inicial:');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setLabel('Data final:');

       $submit = new Zend_Form_Element_Submit('Pesquisar');
       
       
       $submit2 = new Zend_Form_Element_Submit('Pesquisar2');
       $submit2 ->setLabel('Pesquisar')
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
       
       $this->addElements(array($submit2,
                                $ssol_id_documento,
                                $docm_nr_documento,
                                $docm_cd_matricula_cadastro,
                                $docm_cd_lotacao_geradora,
                                /*$ssol_nr_telefone_externo,
                                $ssol_ds_email_externo,
                                $ssol_ed_localizacao , */
                                $ssol_id_tipo_cad,
                                $status_solicitacao,
                                $sgrs_id_grupo,
                                $sser_id_servico,
                                $ssol_nr_tombo,
                                $data_inicial,
                                $data_final,
                                $de_mat, 
                                $docm_ds_assunto_doc,
                                $ssol_ds_observacao,
                                $submit));
    }
}