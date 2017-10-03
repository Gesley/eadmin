<?php

class Sosti_Form_SolicitacaoUserTi extends Zend_Form {

    public function init() {
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

//        $StcaTipoCadastro = new Application_Model_DbTable_SosTbStcaTipoCadastro();
//        $grupo = $StcaTipoCadastro->fetchAll()->toArray();
//        
//        $ssol_id_tipo_cad = new Zend_Form_Element_Select('SSOL_ID_TIPO_CAD');
//        $ssol_id_tipo_cad->setLabel('Tipo Cadastro:')
//                         ->addFilter('StripTags')
//                         ->addFilter('StringTrim')
//                         ->addValidator('NotEmpty');
//        foreach ($grupo as $grupo_p):
//            $ssol_id_tipo_cad->addMultiOptions(array($grupo_p["STCA_ID_TIPO_CAD"] => $grupo_p["STCA_DS_TIPO_CAD"]));
//        endforeach;

        $ssol_id_tipo_cad = new Zend_Form_Element_Hidden('SSOL_ID_TIPO_CAD');
        $ssol_id_tipo_cad->setValue('1')/* ON-LINE */
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $ssol_nm_usuario_externo = new Zend_Form_Element_Text('SSOL_NM_USUARIO_EXTERNO');
        $ssol_nm_usuario_externo//->setRequired(true)
                ->setLabel('Solicitante:')
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->setValue($userNs->matricula . ' - ' . $userNs->nome)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('readonly', 'readonly');

        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade//->setRequired(true)
                ->setLabel('Unidade solicitante:')
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
        $ssol_ds_email_externo->setLabel('E-mail:')
                ->addFilter('StripTags')
                ->setValue($dadosSolicitante[0]["SSOL_DS_EMAIL_EXTERNO"])
                ->setOptions(array('style' => 'width:200px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $ssol_nr_telefone_externo = new Zend_Form_Element_Text('SSOL_NR_TELEFONE_EXTERNO');
        $ssol_nr_telefone_externo->setLabel('*Telefone:')
                ->addFilter('StripTags')
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

        //$SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico(); 
//        $SgrsGrupoServico = $SosTbSgrsGrupoServico->getGrupoServicoBySecsubsec(2, true);
        //$SgrsGrupoServico = $SosTbSgrsGrupoServico->fetchAll('SGRS_ID_GRUPO = 1')->toArray();
        //$SgrsGrupoServico = $SosTbSgrsGrupoServico->getGrupoServicoBySecsubsec($userNamespace->codsecsubseclotacao, true);
        //Zend_Debug::dump($SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AC', 3));



        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao('TR', 2);
        //$SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao( 'AC' , 176 );

        /**
         *  Retira o grupo de Gestão para as Seções
         *  Retira o grupo Desenvolvimento.
         *  Retira o grupo Gestão da infra.
         */
        if ($userNs->siglasecao != 'TR') {
            $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 118);
        }
//        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 119);
        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 120);
        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 121);
        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 2);

        /* Se existir mais de um Grupo de serviço
         * A combo select grupos de serviços carrega
         * o ajax com a opção selecione
         */

        /*
         * Contando os grupos de serviços
         */
        $contador = count($SgrsGrupoServico);

        /*
         * Se for maior que um seleciona a combo com os valores e a opção
         *  selecione
         */
        if ($contador > 1) {
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
                    ->setAttrib('style', 'width: 650PX;');
        }
        /*
         * Se o grupo de serviço for apenas um grupo,
         * carrega a combo select serviços sem selecionar um grupo de serviços
         */
        else if ($contador == 1) {
            $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
            $sgrs_id_grupo->setRequired(true)
                    ->setLabel('*Grupo de Serviço:');

            foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
            endforeach;


            $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
            $sser_id_servico->setRequired(true)
                    ->setLabel('*Serviço:')
                    ->setAttrib('style', 'width: 650PX;');
        }


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

//        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
//        $docm_ds_palavra_chave->setRequired(true)
//                            ->setLabel('*Palavras Chave:')
//                             ->addFilter('StripTags')
//                             ->addFilter('StringTrim')
//                             ->setAttrib('style', 'width: 735px; height: 45px;')
//                             ->addValidator('NotEmpty')
//                             ->addValidator('StringLength', false, array(3,500));

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $ssol_ds_observacao = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
        $ssol_ds_observacao->setLabel('Observação:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style', 'width: 800px; height: 30px;')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', false, array(5, 500))
                ->setDescription('Digite no mínimo 5 caracteres e no máximo 350 caracteres.')
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

        $porordemde = new Zend_Form_Element_Text('PORORDEMDE');
        $porordemde->setValue('')
                ->setRequired(false)
                ->setLabel('Por ordem de:')
                ->setDescription('Abrir solicitação por ordem de terceiros. Buscar pelo nome e selecionar a pessoa. Ex.: Maria')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;');

        $papd_cd_matricula_interessado = new Zend_Form_Element_Text('PAPD_CD_MATRICULA_INTERESSADO');
        $papd_cd_matricula_interessado->setRequired(false)
                ->setLabel('Acompanhar andamento de solicitação: ')
                ->setAttrib('value', 'ldflasfsdal')
                ->setAttrib('style', 'width: 540px;')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');


        $this->addElements(array($ssol_id_documento,
            $unidade,
            $ssol_nm_usuario_externo,
            $porordemde,
            $ssol_nr_telefone_externo,
            $ssol_ds_email_externo,
            $ssol_ed_localizacao,
            $ssol_id_tipo_cad,
            $sgrs_id_grupo,
            $sser_id_servico,
            $ssol_nr_tombo,
            $de_mat,
            $sses_dt_inicio_video,
            $docm_ds_assunto_doc,
            $ssol_ds_observacao,
            //$docm_ds_palavra_chave,
            $ssol_hh_inicio_atend,
            $ssol_hh_final_atend,
            $papd_cd_matricula_interessado));
    }

}
