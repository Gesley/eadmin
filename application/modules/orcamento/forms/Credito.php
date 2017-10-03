<?php
/**
 * Contém formuçarios da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Form
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre programa de trabalho
 * resumido.
 *
 * @category Orcamento
 * @package Orcamento_Form_Credito
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Credito extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'credito' );
        
        // Cria o campo CRED_ID_CREDITO_RECEBIDO
        $txtCodigo = new Zend_Form_Element_Text ( 'CRED_ID_CREDITO_RECEBIDO' );
        
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Código:' );
        $txtCodigo->setAttrib ( 'size', 10 );
        $txtCodigo->setAttrib ( 'maxlength', 10 );
        // $txtCodigo->addFilter ( 'Digits' );
        $txtCodigo->addValidator ( 'Digits' );
        $txtCodigo->setRequired ( true );
        
        // Cria o campo CRED_DS_DOCUMENTO
        $txtDocumento = new Zend_Form_Element_Text ( 'CRED_DS_DOCUMENTO' );
        
        // Define opções o controle $txtDocumento
        $txtDocumento->setLabel ( 'Documento gerador:' );
        $txtDocumento->setAttrib ( 'size', 20 );
        $txtDocumento->setAttrib ( 'maxlength', 50 );
        $txtDocumento->setRequired ( true );
        
        // Cria o campo CRED_DT_EMISSAO
        $txtData = new Zend_Form_Element_Text ( 'CRED_DT_EMISSAO' );
        
        // Define opções o controle $txtData
        $txtData->setLabel ( 'Data:' );
        $txtData->setAttrib ( 'size', 10 );
        $txtData->setAttrib ( 'maxlength', 10 );
        $txtData->setAttrib ( 'class', 'datepicker' );
        $txtData->setRequired ( true );
        
        // Cria o campo CRED_DS_OBSERVACAO
        $txtDescricao = new Zend_Form_Element_Textarea ( 'CRED_DS_OBSERVACAO' );
        
        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel ( 'Descrição:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->setAttrib ( 'maxlength', 500 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );
        
        // Cria o campo CRED_CD_UNID_GEST_EMITENTE
        $cboUG = new Zend_Form_Element_Select ( 'CRED_CD_UNID_GEST_EMITENTE' );
        
        // Dados da unidade gestora
        $tbUG = new Trf1_Orcamento_Negocio_Ug ();
        
        // Define opções o controle $cboUG
        $cboUG->setLabel ( 'Unidade gestora emitente:' );
        $cboUG->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboUG->addMultiOptions ( $tbUG->retornaCombo () );
        $cboUG->setRequired ( true );
        
        // Cria o campo CRED_CD_FONTE
        $cboFonte = new Zend_Form_Element_Select ( 'CRED_CD_FONTE' );
        
        // Dados da fonte
        $tbFonte = new Trf1_Orcamento_Negocio_Fonte ();
        
        // Define opções o controle $cboFonte
        $cboFonte->setLabel ( 'Fonte:' );
        $cboFonte->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboFonte->addMultiOptions ( $tbFonte->retornaCombo () );
        $cboFonte->setRequired ( true );
        
        // Cria o campo CRED_CD_PT_RESUMIDO
        $txtPTRES = new Zend_Form_Element_Text ( 'CRED_CD_PT_RESUMIDO' );
        
        // Define descrição
        $descricaoPtres = 'A lista será carregada após digitar 3 caracteres.';
        
        // Define opções o controle $txtPTRES
        $txtPTRES->setLabel ( 'PTRES:' );
        $txtPTRES->setAttrib ( 'size', 90 );
        $txtPTRES->setAttrib ( 'maxlength', 20 );
        $txtPTRES->setDescription ( $descricaoPtres );
        $txtPTRES->setRequired ( true );
        
        // Cria o campo CRED_CD_ELEMENTO_DESPESA_SUB
        $txtNatureza = new Zend_Form_Element_Text ( 
        'CRED_CD_ELEMENTO_DESPESA_SUB' );
        

        // Define descrição
        $descricaoNatureza = 'A lista será carregada após digitar 3 caracteres.';
        // Define opções o controle $txtNatureza
        $txtNatureza->setLabel ( 'Natureza da despesa:' );
        $txtNatureza->setAttrib ( 'size', 90 );
        $txtNatureza->setAttrib ( 'maxlength', 20 );
        $txtNatureza->setDescription ( $descricaoNatureza );
        $txtNatureza->setRequired ( true );
        
        // Cria o campo CRED_NR_DESPESA
        $txtDespesa = new Zend_Form_Element_Text ( 'CRED_NR_DESPESA' );
        
        // Define opções o controle $txtDespesa
        $txtDespesa->setLabel ( 'Despesa:' );
        $txtDespesa->setAttrib ( 'size', 10 );
        $txtDespesa->setAttrib ( 'maxlength', 8 );
        $txtDespesa->addValidator ( new Trf1_Orcamento_Validacao_Despesa () );
        $txtDespesa->setRequired ( true );
        
        // Cria o campo CRED_CD_TIPO_NC
        $cboTipoNC = new Zend_Form_Element_Select ( 'CRED_CD_TIPO_NC' );
        
        // Dados da fonte
        $tbTipoNC = new Trf1_Orcamento_Negocio_Tiponc ();
        
        // Define opções o controle $cboTipoNC
        $cboTipoNC->setLabel ( 'Tipo de nota de crédito:' );
        $cboTipoNC->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboTipoNC->addMultiOptions ( $tbTipoNC->retornaCombo () );
        $cboTipoNC->setRequired ( true );
        
        // Cria o campo CRED_VL_CREDITO_RECEBIDO
        $txtValor = new Zend_Form_Element_Text ( 'CRED_VL_CREDITO_RECEBIDO' );
        
        // Define opções o controle $txtValor
        $txtValor->setLabel ( 'Valor:' );
        $txtValor->setAttrib ( 'size', 25 );
        $txtValor->setAttrib ( 'maxlength', 21 );
        $txtValor->setAttrib ( 'class', 'valordespesa' );
        $txtValor->setValue ( 0 );
        $txtValor->setRequired ( true );
        
        // Cria o campo CRED_IC_ACERTADO_MANUAL
        $campo = 'CRED_IC_ACERTADO_MANUAL';
        $chkAcertoManual = new Zend_Form_Element_Checkbox ( $campo );
        
        // Define opções o controle $chkAcertoManual
        $chkAcertoManual->setLabel ( 'Acertado manualmente?' );
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $classeSubmit = Orcamento_Business_Dados::CLASSE_SALVAR;
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', $classeSubmit );
        
        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtDespesa );
        $this->addElement ( $txtDocumento );
        $this->addElement ( $txtData );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $cboUG );
        $this->addElement ( $cboFonte );
        $this->addElement ( $txtPTRES );
        $this->addElement ( $txtNatureza );        
        $this->addElement ( $cboTipoNC );
        $this->addElement ( $txtValor );
        $this->addElement ( $chkAcertoManual );
        $this->addElement ( $cmdEnviar );
    }

}