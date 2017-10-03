<?php
class Sosti_Form_AtendimentoSecoesFiltroCaixa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $snat_cd_nivel = new Zend_Form_Element_Select('SNAT_CD_NIVEL');
        $snat_cd_nivel->setRequired(true);
        $snat_cd_nivel->setLabel('Nivel de Atendimento:');

        $ssol_id_documento = new Zend_Form_Element_Hidden('SSOL_ID_DOCUMENTO');
        $ssol_id_documento->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $ssol_id_tipo_cad = new Zend_Form_Element_Hidden('SSOL_ID_TIPO_CAD');
        $ssol_id_tipo_cad->setValue('')/*ON-LINE*/
                         ->addFilter('StripTags')
                         ->addFilter('StringTrim')
                         ->addValidator('NotEmpty')
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');  
        
        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setValue('')
                                    ->setLabel('Solicitante: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $docm_cd_matricula_cadastro_value = new Zend_Form_Element_Hidden('DOCM_CD_MATRICULA_CADASTRO_VALUE');
        $docm_cd_matricula_cadastro_value->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        
        $ssol_cd_matricula_atendente = new Zend_Form_Element_Text('SSOL_CD_MATRICULA_ATENDENTE');
        $ssol_cd_matricula_atendente->setValue('')
                                   ->setLabel('Atendente: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $ssol_cd_matricula_atendente_value = new Zend_Form_Element_Hidden('SSOL_CD_MATRICULA_ATENDENTE_VALUE');
        $ssol_cd_matricula_atendente_value->setValue('')
                            ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $docm_sg_secao_geradora = new Zend_Form_Element_Hidden('DOCM_SG_SECAO_GERADORA');
        $docm_sg_secao_geradora->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setValue('')
                                ->setLabel('Unidade Solicitante:')
                                 ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $docm_cd_lotacao_geradora_value = new Zend_Form_Element_Hidden('DOCM_CD_LOTACAO_GERADORA_VALUE');
        $docm_cd_lotacao_geradora_value->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        
        $ssol_ed_localizacao = new Zend_Form_Element_Text('SSOL_ED_LOCALIZACAO');
        $ssol_ed_localizacao->setValue('')
                            ->setLabel('Local de atendimento:')
                            ->addFilter('StripTags')
                            ->setOptions(array('style' => 'width:540px'))
                            ->addFilter('StringTrim')
                            ->addValidator('NotEmpty');
        
        $ssol_ds_email_externo = new Zend_Form_Element_Text('SSOL_DS_EMAIL_EXTERNO');
        $ssol_ds_email_externo->setValue('')
                              ->setLabel('E-mail:')
                              ->addFilter('StripTags')
                              ->setOptions(array('style' => 'width:200px'))
                              ->addFilter('StringTrim')
                              ->addValidator('NotEmpty');
        
        $ssol_nr_telefone_externo = new Zend_Form_Element_Text('SSOL_NR_TELEFONE_EXTERNO');
        $ssol_nr_telefone_externo->setValue('')
                                ->setLabel('Telefone:')
                                 ->addFilter('StripTags')
                                 ->setOptions(array('style' => 'width:200px'))
                                 ->addFilter('StringTrim')
                                 ->addValidator('NotEmpty');
        
//        $ssol_sg_tipo_tombo = new Zend_Form_Element_Text('SSOL_SG_TIPO_TOMBO');
//        $ssol_sg_tipo_tombo->setValue('T')
//                           ->addFilter('StripTags')
//                           ->addFilter('StringTrim')
//                           ->addValidator('NotEmpty')
//                           ->removeDecorator('Label')
//                           ->removeDecorator('HtmlTag');
        
        $docm_nr_documento = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setValue('')
                          ->setLabel('Nº da Solicitação:')
                          ->setAttrib('style', 'width: 540px;')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->addValidator('Alnum')
                          ->addValidator('StringLength', false, array(5, 28))
                          ->setDescription('Digite o número da solicitação completo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');
       
//        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
//        $ssol_nr_tombo->setLabel('Nº do tombo:')
//                      ->addFilter('StripTags')
//                      ->addFilter('StringTrim')
//                      ->addValidator('NotEmpty');
//
//        $de_mat = new Zend_Form_Element_Textarea('DE_MAT');
//        $de_mat->setLabel('Descrição do Tombo')
//               ->addFilter('StripTags')
//               ->addFilter('StringTrim')
//               ->addValidator('NotEmpty')
//               ->setValue('Primeiro informe Nº do Tombo')
//               ->setAttrib('readonly', 'readonly')
//               ->setAttrib('style', 'width: 540px; height: 30px;')
//               ->setAttrib('class', 'erroInputSelect');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao( $userNamespace->siglasecao,  $userNamespace->codlotacao );
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setValue('') 
                      ->setRequired(false)
                      ->setLabel('*Grupo de Serviço:');
        
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;        
        
        
//        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
//        $SserServico = $SosTbSserServico->fetchAll('SSER_ID_GRUPO = 1')->toArray();
        
        $servico = new Zend_Form_Element_Radio('SERVICO');
        $servico->setLabel('Serviço:')
                       ->setRequired(true)
                       ->setValue('nomecompleto')
                       ->addMultiOption("nomecompleto", "Lista de Serviços")
                       ->addMultiOption("partenome", "Digitar nome do serviço");
        
        $sser_id_servico = new Zend_Form_Element_Multiselect('SSER_ID_SERVICO');
        $sser_id_servico
                        ->setRequired(false)
                        ->setLabel('Serviço (Nome Completo):')
                        ->setAttrib('style', 'width: 650px; height: 300px;')
                        ->setDescription('Este é um campo de multiseleção. Pressione a tecla CTRL para selecionar vários itens.'); 
        
        $sser_ds_servico = new Zend_Form_Element_Text('SSER_DS_SERVICO');
        $sser_ds_servico->setRequired(false)
                             ->setLabel('Serviço (Digite parte ou nome completo):')
                             ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
                        //->setAttrib('disabled', 'disabled');
        //$sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
//        foreach ($SserServico as $SserServico_p):
//            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
//        endforeach;

     
//        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
//        $docm_ds_assunto_doc->setRequired(false)
//                            ->setLabel('Descrição do serviço:')
//                            ->addFilter('StripTags')
//                            ->setAttrib('style', 'width: 540px; height: 30px;')
//                            ->addFilter('StringTrim')
//                            ->addValidator('NotEmpty');
        
//       $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
//       $ssol_ds_observacao->setLabel('Observação:')
//                          ->addFilter('StripTags')
//                          ->addFilter('StringTrim')
//                          ->setAttrib('style', 'width: 540px; height: 30px;')
//                          ->addValidator('NotEmpty');
       
       $status_solicitacao = new Zend_Form_Element_Radio('STATUS_SOLICITACAO');
       $status_solicitacao->setValue('')
                         ->setLabel('Status da Solicitação')
                          ->addMultiOptions(array('9999' => 'Em atendimento',
                                                  '1000' => 'Baixadas',
                                                  '1014' => 'Avaliadas'));
       
       
       $SadTbFadmFaseAdm =  new  Application_Model_DbTable_SadTbFadmFaseAdm();
       $FadmFaseAdm = $SadTbFadmFaseAdm->fetchAll("FADM_NM_SISTEMA = 'SOSTI'", "FADM_DS_FASE");
       
       
       $mofa_id_fase = new Zend_Form_Element_Select('MOFA_ID_FASE');
       $mofa_id_fase->setValue('')
                    ->setRequired(false)
                    ->setLabel('Última Fase:');
        
       $mofa_id_fase->addMultiOptions(array('' => ''));
       foreach ($FadmFaseAdm as $FaseAdm):
           $mofa_id_fase->addMultiOptions(array($FaseAdm['FADM_ID_FASE'] => /*$FaseAdm['FADM_ID_FASE'].' - '. */ $FaseAdm["FADM_DS_FASE"]));
       endforeach; 
       
       $data_inicial_cadastro = new Zend_Form_Element_Text('DATA_INICIAL_CADASTRO');
       $data_inicial_cadastro->setValue('')
                            ->setLabel('Data inicial - Cadastro:');
       
       $data_final_cadastro = new Zend_Form_Element_Text('DATA_FINAL_CADASTRO');
       $data_final_cadastro->setValue('')
                        ->setLabel('Data final - Cadastro:');
       
       
       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial->setValue('')
                    ->setLabel('Data inicial - Última Fase:');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setValue('')
                    ->setLabel('Data final - Última Fase:');
       
       $cate_id_categoria = new Zend_Form_Element_MultiCheckbox('CATE_ID_CATEGORIA');
       $cate_id_categoria
                        ->setRequired(false)
                        ->setLabel('Categorias:');
       
       $somente_principal = new Zend_Form_Element_Checkbox('SOMENTE_PRINCIPAL');
       $somente_principal
                        ->setRequired(false)
                        ->setLabel('Mostrar Solicitações Vinculadas (Filhas)')
                        ->setCheckedValue('S')
                        ->setUncheckedValue('N')
                        ;
        
       $submit = new Zend_Form_Element_Submit('Listar');
       
       $submit2 = new Zend_Form_Element_Submit('Filtrar2');
       $submit2 ->setLabel('Listar')
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
       
       $submit->setAttrib('class', 'listar');
       $this->addElements(array($submit2,
                                $snat_cd_nivel,
                                $ssol_id_documento,
                                $docm_nr_documento,
                                $docm_cd_matricula_cadastro,
                                $docm_cd_matricula_cadastro_value,
                                $ssol_cd_matricula_atendente,
                                $ssol_cd_matricula_atendente_value,
                                $docm_cd_lotacao_geradora,
                                $docm_sg_secao_geradora,
                                $docm_cd_lotacao_geradora_value,
                                $ssol_id_tipo_cad,
                                $mofa_id_fase,
                                $sgrs_id_grupo,
                                $servico,
                                $sser_id_servico,
                                $sser_ds_servico,
                                $cate_id_categoria,
                                $somente_principal,
                                $data_inicial_cadastro,
                                $data_final_cadastro,
                                $data_inicial,
                                $data_final,
                                $submit));
    }

}