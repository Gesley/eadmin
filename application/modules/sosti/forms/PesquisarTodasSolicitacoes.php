<?php

class Sosti_Form_PesquisarTodasSolicitacoes extends Zend_Form {

    public function init () {
        $this->setAction('')
            ->setMethod('post');

        $userNamespace = new Zend_Session_Namespace('userNs');

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rhCentralLotacao->getSecoestrf1();

        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('TRF1/Seção:')
            ->setRequired(false)
            ->setAttrib('style', 'width: 500px; ')
            ->addMultiOptions(array('' => ''));
        foreach ($secao as $v) {
            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
        }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Seção/Subseção')
            ->setAttrib('style', 'width: 500px; ')
            ->setRequired(false)
            ->addMultiOptions(array('' => 'Primeiro escolha a TRF1/Seção'));

        $ssol_id_documento = new Zend_Form_Element_Hidden('SSOL_ID_DOCUMENTO');
        $ssol_id_documento->setValue('')
            ->removeDecorator('Label')
            ->removeDecorator('HtmlTag');

        $ssol_id_tipo_cad = new Zend_Form_Element_Hidden('SSOL_ID_TIPO_CAD');
        $ssol_id_tipo_cad->setValue('1')/* ON-LINE */
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->removeDecorator('Label')
            ->removeDecorator('HtmlTag');

        $atendimento = new Zend_Form_Element_Hidden('ATENDIMENTO');
        $atendimento->setValue('I')/* ON-LINE */
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->removeDecorator('Label')
            ->removeDecorator('HtmlTag');

        $docm_cd_matricula_cadastro = new Zend_Form_Element_Text('DOCM_CD_MATRICULA_CADASTRO');
        $docm_cd_matricula_cadastro->setLabel('Solicitante: ')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
        
        $docm_solicitante_externo = new Zend_Form_Element_Text('SSOL_NM_USUARIO_EXTERNO');
        $docm_solicitante_externo->setLabel('Solicitante Externo: ')
            ->addValidator('StringLength', false, array(5, 4000))
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;');

        $docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setLabel('Unidade Solicitante:')
            ->setAttrib('style', 'text-transform: uppercase; width: 540px;')
            ->setValue('Favor selecionar primeiro TRF1/Seção')
            ->setAttrib('disabled', 'disabled')
        ;

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
//            ->addValidator(new Zend_Validate_Digits())
//            # ->addValidator('Alnum') EDITADO PARA ACEITAR /
            ->addValidator('StringLength', false, array(5, 28))
            ->setDescription('Digite o número da solicitação completo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');

        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
        $ssol_nr_tombo->setLabel('Nº do tombo:')
            ->setAttrib('style', 'width: 540px;')
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
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao($userNamespace->siglasecao, $userNamespace->codlotacao);

        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(false)
            ->setLabel('Grupo de Serviço:');

        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;


        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
            ->setLabel('*Serviço:')
            ->setAttrib('style', 'width: 650PX;');


//        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
//        $SserServico = $SosTbSserServico->fetchAll('SSER_ID_GRUPO = 1')->toArray();
//        $servico = new Zend_Form_Element_Radio('SERVICO');
//        $servico->setLabel('Serviço:')
//                       ->setRequired(true)
//                       ->setValue('nomecompleto')
//                       ->addMultiOption("nomecompleto", "Lista de Serviços")
//                       ->addMultiOption("partenome", "Digitar nome do serviço");
//                        ->setAttrib("checked", "checked");
//        $sser_id_servico = new Zend_Form_Element_Multiselect('SSER_ID_SERVICO');
//        $sser_id_servico
//                        ->setRequired(false)
//                        ->setLabel('Serviço (Nome Completo):')
//                        ->setAttrib('style', 'width: 650px; height: 300px;')
//                        ->setDescription('Este é um campo de multiseleção. Pressione a tecla CTRL para selecionar vários itens.'); 
//                        ->setAttrib('disabled', 'disabled');
//        $sser_id_servico->addMultiOptions(array('' => 'Primeiro Escolha Grupo de Serviço'));
//        foreach ($SserServico as $SserServico_p):
//            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
//        endforeach;
//
//        $sser_ds_servico = new Zend_Form_Element_Text('SSER_DS_SERVICO');
//        $sser_ds_servico->setRequired(false)
//                             ->setLabel('Serviço (Digite parte ou nome completo):')
//                             ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
//     
        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(false)
            ->setLabel('Descrição do serviço:')
            ->addFilter('StripTags')
            ->setAttrib('style', 'width: 540px; height: 30px;')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setDescription('Separe as palavras mais importantes da Descrição do serviço usando vírgula.')
            ->addValidator('StringLength', false, array(5, 4000));
        ;

        $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
        $ssol_ds_observacao->setLabel('Observação:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('style', 'width: 540px; height: 30px;')
            ->addValidator('NotEmpty')
            ->setDescription('Separe as palavras mais importantes da Observação usando vírgula.')
            ->addValidator('StringLength', false, array(5, 500));
        ;

        $status_solicitacao = new Zend_Form_Element_Radio('STATUS_SOLICITACAO');
        $status_solicitacao->setLabel('Status da Solicitação')
            ->addMultiOption("1019", "Recusadas")
            ->addMultiOption("1026", "Canceladas")
            ->addMultiOption("1014", "Avaliadas")
            ->addMultiOption("1000", "Baixadas")
            ->addMultiOption("9999", "Em atendimento")
            ->addMultiOption("", "Todos")->setAttrib("checked", "checked");

        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial->setLabel('Data inicial:');

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setLabel('Data final:');

        $SadTbFadmFaseAdm = new Application_Model_DbTable_SadTbFadmFaseAdm();
        $FadmFaseAdm = $SadTbFadmFaseAdm->fetchAll("FADM_NM_SISTEMA = 'SOSTI'", "FADM_DS_FASE");

        $mofa_id_fase = new Zend_Form_Element_Select('MOFA_ID_FASE');
        $mofa_id_fase->setValue('')
            ->setRequired(false)
            ->setLabel('Selecione a Fase:');

        $mofa_id_fase->addMultiOptions(array('' => ''));
        foreach ($FadmFaseAdm as $FaseAdm):
            $mofa_id_fase->addMultiOptions(array($FaseAdm['FADM_ID_FASE'] => /* $FaseAdm['FADM_ID_FASE'].' - '. */ $FaseAdm["FADM_DS_FASE"]));
        endforeach;


        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(false)
            ->setLabel('Descrição do Histórico:')
            ->setOptions(array('style' => 'width:500px'))
            ->addValidator('StringLength', false, array(5, 4000))
            ->addValidator('NotEmpty')
            ->addFilter('StripTags')
            ->setDescription('Separe as palavras mais importantes da Descrição do Histórico usando vírgula. Informe no mínimo 5 caracteres.')
            ->addFilter('StringTrim');

        $submit = new Zend_Form_Element_Submit('Pesquisar');


        $submit2 = new Zend_Form_Element_Submit('Pesquisar2');
        $submit2->setLabel('Pesquisar')
            ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');

        $this->addElements(array($submit2,
            $trf1_secao,
            $secao_subsecao,
            $ssol_id_documento,
            $docm_nr_documento,
            $docm_cd_matricula_cadastro,
            $docm_solicitante_externo,
            $docm_cd_lotacao_geradora,
            /* $ssol_nr_telefone_externo,
              $ssol_ds_email_externo,
              $ssol_ed_localizacao , */
            $ssol_id_tipo_cad,
            $status_solicitacao,
            $sgrs_id_grupo,
            $servicoAjax,
            $servico,
            $sser_id_servico,
            $sser_ds_servico,
            $data_inicial,
            $data_final,
            $ssol_nr_tombo,
            $de_mat,
            $docm_ds_assunto_doc,
            $ssol_ds_observacao,
            $mofa_id_fase,
            $mofa_ds_complemento));

        $this->addDisplayGroup(array('MOFA_ID_FASE', 'MOFA_DS_COMPLEMENTO'), 'grupo_historico', array("legend" => "Pesquisar no Histórico"));
        $this->addElement($submit);
    }

}
