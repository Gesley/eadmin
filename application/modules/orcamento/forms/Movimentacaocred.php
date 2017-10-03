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
 * Disponibiliza o formulário para entrada de dados sobre solicitações de
 * movimentações de créditos.
 *
 * @category Orcamento
 * @package Orcamento_Form_Novamovimentacaocred
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Movimentacaocred extends Orcamento_Form_Base {
	public function init() {
		$this->setName ( 'frmMovimentacaoCred' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmMovimentacaoCred' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDMovimentacao = new Zend_Form_Element_Text ( 'MOVC_CD_MOVIMENTACAO' );
		$txtIDMovimentacao->setLabel ( 'Movimentação:' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 6 ) );
		
		$txtNRDespesaOrigem = new Zend_Form_Element_Text ( 'MOVC_NR_DESPESA_ORIGEM' );
		$txtNRDespesaOrigem->setLabel ( 'Despesa de origem:' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 8 ) )->addFilter ( 'Digits' )->addValidator ( new Trf1_Orcamento_Validacao_Despesa () );
		
		$validaDuplicado = new Trf1_Orcamento_Validacao_Diferente ();
		$validaDuplicado->defineDespesaOrigem ( $txtNRDespesaOrigem );
		
		$txtNRDespesaDestino = new Zend_Form_Element_Text ( 'MOVC_NR_DESPESA_DESTINO' );
		$txtNRDespesaDestino->setLabel ( 'Despesa de destino:' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 8 ) )->addFilter ( 'Digits' )->addValidator ( $validaDuplicado );
		
		$txtVLMovimentacao = new Zend_Form_Element_Text ( 'MOVC_VL_MOVIMENTACAO' );
		$txtVLMovimentacao->setLabel ( 'Valor:' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 20, 'class' => 'valordespesa' ) );
		
		$txtDHMovimentacao = new Zend_Form_Element_Text ( 'MOVC_DH_MOVIMENTACAO' );
		$txtDHMovimentacao->setLabel ( 'Data:' )->setRequired ( true )->setAttribs ( array ('size' => '16', 'class' => 'datepicker' ) );
		
		$txtDSJusSolicitacao = new Zend_Form_Element_Textarea ( 'MOVC_DS_JUSTIF_SOLICITACAO' );
		$txtDSJusSolicitacao->setLabel ( 'Motivo da solicitação:' )->setRequired ( true )->setAttribs ( array ('size' => '20', 'maxlength' => 3000 ) )->addFilter ( 'StringTrim' );
		
		$txtDSJusSECOR = new Zend_Form_Element_Textarea ( 'MOVC_DS_JUSTIF_SECOR' );
		$txtDSJusSECOR->setLabel ( 'Motivação setorial:' )->setAttribs ( array ('size' => '20', 'maxlength' => 3000, 'class' => 'DIPOR', 'id' => 'MOVC_DS_JUSTIF_SECOR' ) )->addFilter ( 'StringTrim' );
		
		$tbTipoMovimentacao = new Trf1_Orcamento_Negocio_Movimentacaocred ();
		$cboCDTipoMovimentacao = new Zend_Form_Element_Select ( 'MOVC_ID_TIPO_MOVIMENTACAO' );
		$cboCDTipoMovimentacao->setLabel ( 'Tipo da movimentação:' )->setRequired ( true )->addMultiOptions ( $tbTipoMovimentacao->retornaTiposDeMovimentacao () )->setAttrib ( 'class', 'DIPOR' );
		
		$tbTpSolicitacao = new Trf1_Orcamento_Negocio_Tiposolicitacao ();
		$cboCDTipoSolicitacao = new Zend_Form_Element_Select ( 'MOVC_CD_TIPO_SOLICITACAO' );
		$cboCDTipoSolicitacao->setLabel ( 'Status da solicitação:' )->setRequired ( true )->addMultiOptions ( array ('' => 'Selecione' ) )->setMultiOptions ( $tbTpSolicitacao->retornaCombo () )->setAttrib ( 'class', 'DIPOR' );
		
		$txtICMovRepassada = new Zend_Form_Element_Hidden ( 'MOVC_IC_MOVIMENT_REPASSADA' );
		$txtICMovRepassada->setLabel ( 'Crédito repassado:' )->setValue ( 1 ); /* ->setMultiOptions ( array ('' => 'Selecione', '0' => 'Não repassado', '1' => 'Repassado' ) ); */
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDMovimentacao, $txtNRDespesaOrigem, $txtNRDespesaDestino, $txtVLMovimentacao, $txtDHMovimentacao, $txtDSJusSolicitacao, $txtDSJusSECOR, $cboCDTipoMovimentacao, $cboCDTipoSolicitacao, $txtICMovRepassada, $cmdSubmit ) );
	}
}