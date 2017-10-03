<?php
class Sisad_Form_AddTipoDoc extends Zend_Form
{
    public function init()
    {
        $dtpdtipodoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $GetTipoDocNivel1 = $dtpdtipodoc->getTipoDocumentosTodosNivel1();
        $GetTipoDocTodos = $dtpdtipodoc->getTipoDocumentosTodos();
        
        $this->setAction('')
             ->setMethod('post');

        $acao = new Zend_Form_Element_Hidden('setAcao');
        $acao->setValue('SetTipoDoc');
        
        $dtpd_id_tipo_doc = new Zend_Form_Element_Hidden('DTPD_ID_TIPO_DOC');
        
        $dtpd_cd_tp_doc_nivel_1 = new Zend_Form_Element_Select('DTPD_CD_TP_DOC_NIVEL_1');
        $dtpd_cd_tp_doc_nivel_1->setRequired(true)
                 ->setLabel('Grupo:')
                 ->setDescription('Selecione caso o tipo a ser cadastrado faça parte de algum grupo de tipos de documentos!')
                 ->addMultiOptions(array('' => ':: Selecione ::', '0' => ':: CADASTRAR UM GRUPO ::'));
                 foreach($GetTipoDocNivel1 as $GetTipoDocNivel1_p){
                    $dtpd_cd_tp_doc_nivel_1->addMultiOptions(array($GetTipoDocNivel1_p["DTPD_CD_TP_DOC_NIVEL_1"] => $GetTipoDocNivel1_p["DTPD_NO_TIPO"]));
                 }
                 
        $dtpd_cd_tp_doc_nivel_2 = new Zend_Form_Element_Select('DTPD_CD_TP_DOC_NIVEL_2');
        $dtpd_cd_tp_doc_nivel_2->setRequired(true)
                 ->setLabel('Subgrupo:')
                 ->setDescription('Selecione caso o tipo a ser cadastrado faça parte de algum subgrupo de tipos de documentos!')
                 ->addMultiOptions(array('' => ':: Subgrupo :: '));
        foreach($GetTipoDocTodos as $GetTipoDocTodos_P){
                    $dtpd_cd_tp_doc_nivel_2->addMultiOptions(array($GetTipoDocTodos_P["ID"] => $GetTipoDocTodos_P["DTPD_NO_TIPO"]));
                 }
        
        $dtpd_no_tipo = new Zend_Form_Element_Text('DTPD_NO_TIPO');
        $dtpd_no_tipo->setRequired(true)
                 ->setLabel('*Tipo de Documento:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 350px'))
                 ->addValidator('StringLength', false, array(5, 200))
                ->setDescription('A inicial de cada palavra deve estar em maiúsculo.');

        $dtpd_ic_instancia = new Zend_Form_Element_Select('DTPD_IC_INSTANCIA');
        $dtpd_ic_instancia->setRequired(true)
                 ->setLabel('Grau de Atuação do Documento:')
                 ->addMultiOptions(array('' => ':: Selecione :: ',1 => 'Justiça Federal', 2 => 'Tribunal Regional Fereral',3 => 'Justiça Federal e Tribunal Regional Fereral'));

        $dtpd_ic_adm_jud = new Zend_Form_Element_Select('DTPD_IC_ADM_JUD');
        $dtpd_ic_adm_jud->setRequired(true)
                 ->setLabel('Área de Atuação do Documento:')
                 ->addMultiOptions(array('' => ':: Selecione :: ','AD' => 'Administrativa', 'JU' => 'Judicial','AM' => 'Administrativa e Judicial'));
        
        $dtpd_id_pctt = new Zend_Form_Element_Text('DTPD_ID_PCTT');
        $dtpd_id_pctt->setRequired(true)
                 ->setLabel('*Identificador do PCTT:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 500px'))
                 ->addValidator('StringLength', false, array(5, 200))
                 ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($acao,
                                $dtpd_id_tipo_doc,
                                $dtpd_cd_tp_doc_nivel_1,
                                $dtpd_cd_tp_doc_nivel_2,
                                $dtpd_no_tipo,
                                $dtpd_ic_instancia,
                                $dtpd_ic_adm_jud,
                                $dtpd_id_pctt,
                                $submit));
    }
}