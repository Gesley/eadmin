<?php

class Os_Form_SolicitacaoOs extends Zend_Form 
{

    public function init() 
    {
        $this->setAction('save')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setAttrib('id', 'form')
                ->setMethod('post');

        $userNs = new Zend_Session_Namespace('userNs');
        $data = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dadosSolicitante = $data->getDadosSolicitante($userNs->matricula);

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

        $ssol_nm_usuario_externo = new Zend_Form_Element_Text('SSOL_NM_USUARIO_EXTERNO');
        $ssol_nm_usuario_externo//->setRequired(true)
                ->setLabel('*Solicitante:')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->setValue($userNs->matricula . ' - ' . $userNs->nome)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('readonly', 'readonly');

        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade//->setRequired(true)
                ->setLabel('*Unidade solicitante:')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->setValue($userNs->codlotacao . ' - ' . $userNs->descicaolotacao . ' - ' . $userNs->siglalotacao . ' - ' . $userNs->siglasecao)
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
        $ssol_ds_email_externo->setLabel('*E-mail:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->setValue($dadosSolicitante[0]["SSOL_DS_EMAIL_EXTERNO"])
                ->setOptions(array('style' => 'width:200px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('EmailAddress');

        $ssol_nr_telefone_externo = new Zend_Form_Element_Text('SSOL_NR_TELEFONE_EXTERNO');
        $ssol_nr_telefone_externo->setLabel('*Telefone:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->setValue($dadosSolicitante[0]["SSOL_NR_TELEFONE_EXTERNO"])
                ->setOptions(array('style' => 'width:200px'))
                ->addFilter('StringTrim')
                ->setAttrib('size', '24')
                ->addValidator('NotEmpty');

        $ssol_sg_tipo_tombo = new Zend_Form_Element_Text('SSOL_SG_TIPO_TOMBO');
        $ssol_sg_tipo_tombo->setValue('T')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
        $ssol_nr_tombo->setLabel('Nº do Tombo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $de_mat = new Zend_Form_Element_Textarea('DE_MAT');
        $de_mat->setLabel('Descrição do Tombo')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setValue('Primeiro informe Nº do Tombo')
                ->setAttrib('disabled', 'disabled')
                ->setAttrib('style', 'width: 800px; height: 30px;')
                ->setAttrib('class', 'erroInputSelect');

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao('TR', 2);
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                ->setLabel('*Categoria de Serviço:');
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p) {
            if ($SgrsGrupoServico_p["CXEN_ID_CAIXA_ENTRADA"] == "2") {
                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
            }
        }

        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(false)
                ->setLabel('*Serviços:')
                ->setRequired(true);
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p) {
            $sser_id_servico->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        }
        $sser_id_servico->setAttrib('style', 'width: 650PX;');

        $sses_dt_inicio_video = new Zend_Form_Element_Text('SSES_DT_INICIO_VIDEO');
        $sses_dt_inicio_video->setRequired(false)
                ->setLabel('*Data e hora de início da videoconferência:')
                ->setAttrib('style', 'width: 120px;')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyyHH:mm:ss')))
                ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $docm_ds_assunto_doc = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC');
        $docm_ds_assunto_doc->setRequired(true)
                ->setLabel('*Descrição do serviço:')
                ->setDescription('Digite no mínimo 5 caracteres e no máximo 32000 caracteres.')
                ->setAttrib('style', 'width: 800px; height: 30px;')
                ->addValidator('StringLength', false, array(5, 64000))
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
        
        $ssol_solicitacoes_os = new Zend_Form_Element_Hidden('SOLICITACOES_OS');
        $ssol_solicitacoes_os->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $ssol_flag_garantia = new Zend_Form_Element_Checkbox('SSOL_FLAG_GARANTIA');
        $ssol_flag_garantia->setLabel('Solicitar Garantia:')
                ->setCheckedValue(true);
        
        $ssol_garantia_observacao = new Zend_Form_Element_Textarea('SSOL_GARANTIA_OBSERVACAO');
        $ssol_garantia_observacao->setLabel('*Justificativa da Solicitação de Garantia:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style', 'width: 800px; height: 30px;')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', false, array(5, 500))
                ->addFilter($Zend_Filter_HtmlEntities);
        
        $papd_cd_matricula_interessado = new Zend_Form_Element_Text('PAPD_CD_MATRICULA_INTERESSADO');
        $papd_cd_matricula_interessado->setRequired(false)
                ->setLabel('Acompanhar andamento de solicitação: ')
                ->setAttrib('value', 'ldflasfsdal')
                ->setAttrib('style', 'width: 540px;')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $anexos = new Zend_Form_Element_File('ANEXOS');
        $anexos->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'Multi', 'maxlength' => 20, 'multiple' => true))
                                 ->addValidator('File_Upload', true, array('messages'=>'YOUR MESSAGE HERE'))
->addValidator(new App_Form_Validate_Anexos())
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');

        $this->addElements(array($ssol_id_documento,
            $unidade,
            $ssol_nm_usuario_externo,
            $ssol_nr_telefone_externo,
            $ssol_ds_email_externo,
            $ssol_ed_localizacao,
            $ssol_id_tipo_cad,
            $sser_id_servico,
            $sgrs_id_grupo,
            $ssol_nr_tombo,
            $de_mat,
            $sses_dt_inicio_video,
            $docm_ds_assunto_doc,
            $ssol_ds_observacao,
            $ssol_solicitacoes_os,
            $ssol_hh_inicio_atend,
            $ssol_hh_final_atend,
            $ssol_flag_garantia,
            $ssol_garantia_observacao,
            $papd_cd_matricula_interessado,
            $anexos));
    }

}