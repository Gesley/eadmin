<?php

class Sisad_Form_Leitura_Juntada extends Zend_Form {

    public function init() {
        
    }

    /**
     * Monta formulário de pesquisa de anexos.
     */
    public function pesquisaAnexo() {


        $sadTbDocmDocumento = new Sisad_Form_Table_SadTbDocmDocumento();

        $docm_nr_documento = $sadTbDocmDocumento->getElement('DOCM_NR_DOCUMENTO');
        $docm_nr_documento
                ->setLabel('Nº do documento/processo:')
                ->setAttrib('style', 'width: 500px;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Alnum')
                ->setDescription('Digite o número do Documento/Processo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');

        $docm_id_tipo_doc = $sadTbDocmDocumento->getElement('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setLabel('Tipo do documento:');
        $service_tipo = new Services_Sisad_Tipo();
        $tipos = $service_tipo->getTipoDocumento();
        foreach ($tipos as $tipo) {
            $docm_id_tipo_doc->addMultiOptions(array($tipo['DTPD_ID_TIPO_DOC'] => $tipo['DTPD_NO_TIPO']));
        }

        $docm_id_pctt = new Zend_Form_Element_Text('DOCM_ID_PCTT');
        $docm_id_pctt
                ->setLabel('Assunto do documento:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'DOCM_ID_PCTT')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        ;


        $docm_ds_palavra_chave = $sadTbDocmDocumento->getElement('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave
                ->setLabel('Palavras chave:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style', 'width: 500px; height: 45px;');

        $pesquisar_autor_juntada = new Zend_Form_Element_Text('pesquisar_autor_juntada');
        $pesquisar_autor_juntada
                ->setLabel('Autor da inserção:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'pesquisar_autor_juntada')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $pesquisar_data_juntada = new Zend_Form_Element_Text('pesquisar_data_juntada');
        $pesquisar_data_juntada
                ->setLabel('Data da inserção:')
                ->addValidator('date', 'dd/mm/YYYY')
                ->setAttrib('class', 'datepicker');

        $pesquisar_ic_juntada = new Zend_Form_Element_Checkbox('pesquisar_ic_juntada');
        $pesquisar_ic_juntada
                ->setLabel('Possui documentos/processos inseridos:')
                ->setOptions(array('S' => 'Sim', 'N' => 'Não'))
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $btnSalvar = new Zend_Form_Element_Button('Pesquisar');
        $btnSalvar->setAttrib("class", "submitComum pesquisar");

        $this->clearElements();
        $this->addElements(array(
            $docm_nr_documento
            , $docm_id_tipo_doc
            , $docm_id_pctt
            , $docm_ds_palavra_chave
            , $pesquisar_autor_juntada
            , $pesquisar_data_juntada
            , $pesquisar_ic_juntada
            , $btnSalvar
        ));
    }

    /**
     * Monta formulário de pesquisa de apensos.
     */
    public function pesquisaApenso() {


        $sadTbDocmDocumento = new Sisad_Form_Table_SadTbDocmDocumento();

        $docm_nr_documento = $sadTbDocmDocumento->getElement('DOCM_NR_DOCUMENTO');
        $docm_nr_documento
                ->setLabel('Nº do documento/processo:')
                ->setAttrib('style', 'width: 500px;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Alnum')
                ->setDescription('Digite o número do Documento/Processo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');

        $docm_id_pctt = new Zend_Form_Element_Text('DOCM_ID_PCTT');
        $docm_id_pctt
                ->setLabel('Assunto do documento:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'DOCM_ID_PCTT')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        ;


        $docm_ds_palavra_chave = $sadTbDocmDocumento->getElement('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave
                ->setLabel('Palavras chave:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style', 'width: 500px; height: 45px;');

        $pesquisar_autor_juntada = new Zend_Form_Element_Text('pesquisar_autor_juntada');
        $pesquisar_autor_juntada
                ->setLabel('Autor da inserção:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'pesquisar_autor_juntada')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $pesquisar_data_juntada = new Zend_Form_Element_Text('pesquisar_data_juntada_apenso');
        $pesquisar_data_juntada
                ->setLabel('Data da inserção:')
                ->addValidator('date', 'dd/mm/YYYY')
                ->setAttrib('class', 'datepicker');

        $btnSalvar = new Zend_Form_Element_Button('Pesquisar');
        $btnSalvar->setAttrib("class", "submitComum pesquisar");

        $this->clearElements();
        $this->addElements(array(
            $docm_nr_documento
            , $docm_id_pctt
            , $docm_ds_palavra_chave
            , $pesquisar_autor_juntada
            , $pesquisar_data_juntada
            , $btnSalvar
        ));
    }

    /**
     * Monta formulário de pesquisa de vinculos.
     */
    public function pesquisaVinculos() {


        $sadTbDocmDocumento = new Sisad_Form_Table_SadTbDocmDocumento();

        $docm_nr_documento = $sadTbDocmDocumento->getElement('DOCM_NR_DOCUMENTO');
        $docm_nr_documento
                ->setLabel('Nº do documento/processo:')
                ->setAttrib('style', 'width: 500px;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Alnum')
                ->setDescription('Digite o número do Documento/Processo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');

        $docm_id_pctt = new Zend_Form_Element_Text('DOCM_ID_PCTT');
        $docm_id_pctt
                ->setLabel('Assunto do documento:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'DOCM_ID_PCTT')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        ;


        $docm_ds_palavra_chave = $sadTbDocmDocumento->getElement('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave
                ->setLabel('Palavras chave:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style', 'width: 500px; height: 45px;');

        $pesquisar_autor_juntada = new Zend_Form_Element_Text('pesquisar_autor_juntada');
        $pesquisar_autor_juntada
                ->setLabel('Autor da inserção:')
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres.')
                ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                ->setAttrib('class', 'pesquisar_autor_juntada')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $pesquisar_data_juntada = new Zend_Form_Element_Text('pesquisar_data_juntada_vinculo');
        $pesquisar_data_juntada
                ->setLabel('Data da inserção:')
                ->addValidator('date', 'dd/mm/YYYY')
                ->setAttrib('class', 'datepicker');

        $btnSalvar = new Zend_Form_Element_Button('Pesquisar');
        $btnSalvar->setAttrib("class", "submitComum pesquisar");

        $this->clearElements();
        $this->addElements(array(
            $docm_nr_documento
            , $docm_id_pctt
            , $docm_ds_palavra_chave
            , $pesquisar_autor_juntada
            , $pesquisar_data_juntada
            , $btnSalvar
        ));
    }

}