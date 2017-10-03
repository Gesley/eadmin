<?php

class Sisad_Form_Documento_Documento extends Zend_Form {

    /**
     * Informa se foi selecionado um formulario
     * @var bool $_formSelecionado
     */
    public $_formSelecionado = false;

    public function init() {
        
    }

    /**
     * Monta formulário de ediçao.
     */
    public function edit($documento, $objetivo = 'mostrar', $action = 'saveedit', $name = 'editar_documento', $method = 'post') {
        $this->setMethod($method)
                ->setAction($action)
                ->setName($name);
        if (!is_numeric($documento['DOCM_ID_DOCUMENTO'])) {
            throw new Exception('É necessário passar por parametro um array com o id do documento');
        }
        //services
        $service_pctt = new Services_Sisad_Pctt();
        $service_confidencialidade = new Services_Sisad_Confidencialidade();
        $service_situacao = new Services_Sisad_Situacao();

        //forms
        $formDocm = new Sisad_Form_Table_SadTbDocmDocumento();

        //filtros
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();

        //campos
        $docm_id_documento = $formDocm->getElement('DOCM_ID_DOCUMENTO');
        //assunto do documento
        $docm_id_pctt = $formDocm->getElement('DOCM_ID_PCTT');
        $docm_id_pctt->setRequired(true)
                ->setLabel('Assunto do Documento')
                ->addFilter('StripTags')
                ->setOptions(array('style' => 'width:500px'))
                ->addFilter('StringTrim')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.')
                ->addValidator('NotEmpty');
        $pcttsAjax = $service_pctt->getPctts('ajax');
        foreach ($pcttsAjax as $pcttAjax) {
            $docm_id_pctt->addMultiOption($pcttAjax['AQVP_ID_PCTT'], $pcttAjax['DESCRICAO_PCTT']);
        }

        //Situação
        $situacoes = $service_situacao->getSituacoes();
        $docm_id_tipo_situacao_doc = $formDocm->getElement('DOCM_ID_TIPO_SITUACAO_DOC');
        $docm_id_tipo_situacao_doc->setRequired(true)
                ->setLabel('Estado do documento:');
        foreach ($situacoes as $situacao):
            $docm_id_tipo_situacao_doc->addMultiOption($situacao['TPSD_ID_TIPO_SITUACAO_DOC'], $situacao['TPSD_DS_TIPO_SITUACAO_DOC']);
        endforeach;

        //confidencialidade do documento
        $docm_id_confidencialidade = $formDocm->getElement('DOCM_ID_CONFIDENCIALIDADE');
        $docm_id_confidencialidade->setRequired(true)
                ->setLabel('Confidencialidade')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');
        $confidencialidades = $service_confidencialidade->getConfidencialidades();
        foreach ($confidencialidades as $confidencialidade) {
            $docm_id_confidencialidade->addMultiOption($confidencialidade['CONF_ID_CONFIDENCIALIDADE'], $confidencialidade['CONF_DS_CONFIDENCIALIDADE']);
        }

        //campos com tratamento especial
        if ($objetivo == 'mostrar') {
            //palavras chave
            $docm_ds_palavra_chave = $formDocm->getElement('DOCM_DS_PALAVRA_CHAVE');
            $docm_ds_palavra_chave->setRequired(true)
                    ->setLabel('Palavras Chave:')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('style', 'width: 500px; height: 45px;')
                    ->addValidator('NotEmpty');
        } elseif($objetivo == 'editar') {
            //palavras chave
            $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
            $docm_ds_palavra_chave = $formDocm->getElement('DOCM_DS_PALAVRA_CHAVE');
            $docm_ds_palavra_chave->setRequired(true)
                    ->setLabel('Palavras Chave:')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('style', 'width: 500px; height: 45px;')
                    ->addValidator('NotEmpty')
                    ->addFilter($Zend_Filter_HtmlEntities);
        }

        //botoes
        //alterar
        $btnAlterar = new Zend_Form_Element_Submit('Alterar');
        $btnAlterar->setAttrib('class', 'submitComum');
        $this->addElements(array(
            $docm_id_documento,
            $docm_id_pctt,
            $docm_ds_palavra_chave,
            $docm_id_tipo_situacao_doc,
            $docm_id_confidencialidade,
            $btnAlterar
        ));

        $this->populate($documento);

        $this->_formSelecionado = true;
    }

}