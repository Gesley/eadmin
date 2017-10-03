<?php
class Sosti_Form_Atendimentointerno extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setAttrib('enctype', 'multipart/form-data')
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
        $docm_cd_matricula_cadastro->setRequired(true)
                                   ->setLabel('*Solicitante: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setRequired(true)
                                 ->setLabel('*Unidade Solicitante:')
                                 ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $ssol_ed_localizacao = new Zend_Form_Element_Text('SSOL_ED_LOCALIZACAO');
        $ssol_ed_localizacao->setRequired(true)
                            ->setLabel('*Local de atendimento:')
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
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao( $userNamespace->siglasecao , $userNamespace->codlotacao );
        //$SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao( 'AC' , 176 );
        
        
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('*Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
        
//        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
//        $SserServico = $SosTbSserServico->fetchAll('SSER_ID_GRUPO = 1')->toArray();
        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(true)
                        ->setLabel('*Serviço:')
                        ->setAttrib('style', 'width: 650PX;');
                        //->setAttrib('disabled', 'disabled');
        //$sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
//        foreach ($SserServico as $SserServico_p):
//            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
//        endforeach;
     
        $sses_dt_inicio_video = new Zend_Form_Element_Text('SSES_DT_INICIO_VIDEO');
        $sses_dt_inicio_video->setRequired(false)
                ->setLabel('*Data e hora de início da videoconferência:')
                ->setAttrib('style', 'width: 120px;')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyyHH:mm:ss')))
                ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');
        
        
        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(true)
                            ->setLabel('*Descrição do serviço:')
                            ->setDescription('Digite no mínimo 5 caracteres e no máximo 32000 caracteres.')
                            ->setAttrib('style', 'width: 800px; height: 30px;')
                            ->addValidator('StringLength', false, array(5, 64000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
       $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
       $ssol_ds_observacao->setLabel('Observação:')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->setDescription('Digite no mínimo 5 caracteres e no máximo 350 caracteres.')
                          ->setAttrib('style', 'width: 540px; height: 30px;')
                          ->addValidator('NotEmpty')
                          ->addValidator('StringLength', false, array(5, 500));;
       
       $submit = new Zend_Form_Element_Submit('Salvar');
       
       $this->addElements(array($ssol_id_documento,
                                $docm_cd_matricula_cadastro,
                                $docm_cd_lotacao_geradora,
                                $ssol_nr_telefone_externo,
                                $ssol_ds_email_externo,
                                $ssol_ed_localizacao , 
                                $ssol_id_tipo_cad,
                                $sgrs_id_grupo,
                                $sser_id_servico,
                                $ssol_nr_tombo,
                                $de_mat,
                                $sses_dt_inicio_video,
                                $docm_ds_assunto_doc,
                                $ssol_ds_observacao));
    }

}