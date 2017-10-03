<?php
class Sisad_Form_CaixaDocumentos extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
		/*
		 * Numero
		 * Unidade
		 * Categoria
		 * Palavras chave
		 * Partes
		 * Tipo do documento
		 * Assunto
		 */
        $userNamespace = new Zend_Session_Namespace('userNs'); 

        $docm_nr_documento = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setValue('')
                          ->setLabel('Nº do Documento / Processo')
                          ->setAttrib('style', 'width: 490px;')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim')
                          ->addValidator('Alnum')
                          ->addValidator('StringLength', false, array(5, 28))
                          ->setDescription('Digite o número do Documento/Processo ou ano seguido dos dígitos indicadores da sequência final. Ex.: 201227');
   
       
		$docm_cd_lotacao_geradora = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
        $docm_cd_lotacao_geradora->setValue('')
                                ->setLabel('Unidade Administrativa:')
                                ->setAttrib('style', 'text-transform: uppercase; width: 540px;')
								->setDescription('A lista será carregada após digitar no mínimo três caracteres.');
                      
		
		$movi_cd_secao_unid_origem = new Zend_Form_Element_Hidden('MOVI_CD_SECAO_UNID_ORIGEM');
        $movi_cd_secao_unid_origem->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
		$osctbTipoDocumento = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $tipodoc = $osctbTipoDocumento ->getTipoDocumento();
		$docm_id_tipo_doc = new Zend_Form_Element_Select('DOCM_ID_TIPO_DOC');
        $docm_id_tipo_doc->setRequired(false)
                         ->setLabel('Tipo Documento');
        $docm_id_tipo_doc->addMultiOptions(array('' => ''));
        foreach ($tipodoc as $tipodoc_p):
            $docm_id_tipo_doc->addMultiOptions(array($tipodoc_p["DTPD_ID_TIPO_DOC"] => $tipodoc_p["DTPD_NO_TIPO"]));
        endforeach;
        
        #ADICIONADO AO CODIGO DIA 20/06
        #INICIO
        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial->setValue('')
                ->setLabel('Data inicial:');

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setValue('')
                ->setLabel('Data final:');
        #FIM
        
        $docm_id_pctt = new Zend_Form_Element_Text('DOCM_ID_PCTT');
        $docm_id_pctt->setRequired(false)
                      ->setLabel('Assunto do Documento')
                      ->setOptions(array('style' => 'width:490px'))
                      ->setDescription('A lista será carregada após digitar no mínimo três caracteres.');
                      
		
       $cate_id_categoria = new Zend_Form_Element_MultiCheckbox('CATE_ID_CATEGORIA');
       $cate_id_categoria->setRequired(false)
                         ->setLabel('Categorias:');
       
	   $papd_cd_matricula_interessado = new Zend_Form_Element_Select('PAPD_CD_MATRICULA_INTERESSADO');
       $papd_cd_matricula_interessado->setRequired(false)
                       ->setLabel('Parte:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty'); 
	   
	   
	   
	    
	    $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $docm_ds_palavra_chave = new Zend_Form_Element_Textarea('DOCM_DS_PALAVRA_CHAVE');
        $docm_ds_palavra_chave->setRequired(false)
                            ->setLabel('Palavras Chave')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->setAttrib('style', 'width: 490px; height: 45px;')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(3, 500))
                             ->addFilter($Zend_Filter_HtmlEntities);
	   
	   
	   
	   
       $submit = new Zend_Form_Element_Submit('Filtrar');
       $submit2 = new Zend_Form_Element_Submit('Filtrar2');
       $submit2 ->setLabel('Filtrar')
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
       
       
       $this->addElements(array($submit2,
                                $docm_nr_documento,
                                $docm_cd_lotacao_geradora,
                                $movi_cd_secao_unid_origem,
                                $docm_id_tipo_doc,
                                $docm_id_pctt,
                                $papd_cd_matricula_interessado,
                                $data_inicial,
                                $data_final,
                                $docm_ds_palavra_chave,
                                $cate_id_categoria,
                                $submit));
    }

}