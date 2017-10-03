<?php
class Orcamento_Form_Novadespesa extends Zend_Form {
	
	protected $perfil;
	
	public function __construct($perfilusuario = null) {
		if ($perfilusuario == null) {
			$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
			$this->perfil = $sessaoOrcamento->perfil;
		} else {
			$this->perfil = $perfilusuario;
		}
		parent::__construct ( $this->perfil );
	}
	
	public function init() {
		$this->setName ( 'frmNovaDespesa' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmNovaDespesa' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtCodigo = new Zend_Form_Element_Text ( 'SOLD_NR_SOLICITACAO' );
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Código:' );
        $txtCodigo->setAttrib ( 'size', 5 );
        $txtCodigo->setAttrib ( 'maxlength', 1 );
        $txtCodigo->addFilter ( 'StringTrim' );
        $txtCodigo->addValidator ( 'Digits' );
        $txtCodigo->setAttrib ( 'readonly', 'readonly' );

		// Ano
		$txtAno = new Zend_Form_Element_Select ( 'SOLD_AA_SOLICITACAO' );
		
		// Dados sobre exercícios
		$tbAno = new Orcamento_Business_Negocio_Exercicio ();
		$exercicios = $tbAno->retornaCombo ();
		
		// Define opções o controle $txtAno
		$txtAno->setLabel ( 'Ano:' );
		$txtAno->addFilter ( 'StringTrim' );
		$txtAno->addFilter ( 'StripTags' );
		$txtAno->addMultiOptions ( array ( '' => 'Selecione' ) );
		$txtAno->addMultiOptions ( $exercicios );
		$txtAno->setRequired ( true );
		
		$txtNrDespesa = new Zend_Form_Element_Text ( 'SOLD_NR_DESPESA' );
		$txtNrDespesa->setLabel ( 'Número da despesa:' )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 8 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtJustDesp = new Zend_Form_Element_Textarea ( 'SOLD_DS_DESPESA' );
		$txtJustDesp->setLabel ( 'Descrição Despesa:' )->setRequired ( false )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 3000 )->addFilter ( 'StringTrim' );

		$txtIDNovaDespesa = new Zend_Form_Element_Text ( 'SOLD_NR_SOLICITACAO' );
		$txtIDNovaDespesa->setLabel ( 'Solicitação:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 4 )->addFilter ( 'Digits' )->addValidator ( 'Digits' )->setAttrib ( 'class', 'DIPOR' );
		
		$tbUG = new Trf1_Orcamento_Negocio_Ug ();
		$cboUG = new Zend_Form_Element_Select ( 'SOLD_CD_UG' );
		$cboUG->setLabel ( 'Unidade gestora:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbUG->retornaCombo () )->setRequired ( true );
		
		$txtTipoResponsavel = new Zend_Form_Element_Text ( 'SOLD_CD_RESPONSAVEL' );
		$txtTipoResponsavel->setLabel ( 'Responsável' )->setRequired ( true )->setAttribs ( array ('size' => '100', 'maxlength' => 120 ) )->setDescription ( 'A lista será carregada após digitar 3 caracteres.' );
		
		$tbTipoDespesa = new Trf1_Orcamento_Negocio_Tipodespesa ();
		$cboTipoDespesa = new Zend_Form_Element_Select ( 'SOLD_CD_TIPO_DESPESA' );
		$cboTipoDespesa->setLabel ( 'Caráter da despesa:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbTipoDespesa->retornaCombo () )->setRequired ( true );
		
		$campoStatus = "Status";
		if( $this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR ){
			$campoStatus = "Situação Atendida";
		}


		$tbTipoSol = new Trf1_Orcamento_Negocio_Tiposolicitacao ();
		$cboTipoSolicitacao = new Zend_Form_Element_Select ( 'SOLD_CD_TIPO_SOLICITACAO' );
		$cboTipoSolicitacao->setLabel ( $campoStatus.':' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbTipoSol->retornaCombo () )->setRequired ( true )->setAttrib ( 'class', 'SECOR' );
		
		$txtPtres = new Zend_Form_Element_Text ( 'SOLD_CD_PT_RESUMIDO' );
		$txtPtres->setLabel ( 'PTRES:' )->setRequired ( false )->setAttrib ( 'size', '10' );
		
		$txtNatureza = new Zend_Form_Element_Text ( 'SOLD_CD_ELEMENTO_DESPESA_SUB' );
		$txtNatureza->setLabel ( 'Natureza da despesa:' )->setRequired ( false )->setAttrib ( 'size', '10' );
		
		$txtTipoDespesa = new Zend_Form_Element_Text ( 'SOLD_CD_TIPO_DESPESA' );
		$txtTipoDespesa->setLabel ( 'Tipo de Despesa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$campoValor = "Valor";
		if( $this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR ){
			$campoValor = "Valor Solicitado";
		}
		
		$txtValor = new Zend_Form_Element_Text ( 'SOLD_VL_SOLICITADO' );
		$txtValor->setLabel ( $campoValor.':' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 21, 'class' => 'valordespesa' ) );
		
		$txtValorAtendido = new Zend_Form_Element_Text ( 'SOLD_VL_ATENDIDO' );
		$txtValorAtendido->setLabel ( 'Valor atendido:' )->setRequired ( false )->setAttribs ( array ('size' => '10', 'maxlength' => 21, 'class' => 'valordespesa' ) );
		
		$campoSolicitante = "Justificativa";
		if( $this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR ){
			$campoSolicitante = "Justificativa do Solicitante";
		}

		$txtJustSolicitante = new Zend_Form_Element_Textarea ( 'SOLD_DS_JUSTIFICATIVA_SOLICIT' );
		$txtJustSolicitante->setLabel ( $campoSolicitante.':' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 3000 )->addFilter ( 'StringTrim' );

        // Registros da tabela de justificativas
        $dadosJust = new Orcamento_Business_Negocio_Justificativa ();
        $cbJustificativa = new Zend_Form_Element_Select ( 'SOLA_ID_JUSTIFICATIVA' );
        $cbJustificativa->setLabel ( 'Justificativa Padronizada:' );
        $cbJustificativa->addFilter ( 'StripTags' )->addMultiOptions ( array (
                '' => 'Selecione uma justificativa:' )
            )->addMultiOptions (
            $dadosJust->retornaCombo ()
        );		
		
		$campoJustificativaSetorial = "Motivação setorial";
		if( $this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR ){
			$campoJustificativaSetorial = "Justificativa Setorial";
		}

		$txtJustSECOR = new Zend_Form_Element_Textarea ( 'SOLD_DS_JUSTIFICATIVA_SECOR' );
		$txtJustSECOR->setLabel ( $campoJustificativaSetorial.':' )->setRequired ( false )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 3000 )->addFilter ( 'StringTrim' )->setAttrib ( 'class', 'DIPOR' )->setAttrib ( 'id', 'SOLD_DS_JUSTIFICATIVA_SECOR' );
		
		$txtDtSoli = new Zend_Form_Element_Text ( 'SOLD_DT_SOLICITACAO' );
		$txtDtSoli->setLabel ( 'Data. solicitação:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'class', 'datepicker' );
		
		$cbPrioridade = new Zend_Form_Element_Select ( 'SOLD_NR_PRIORIDADE' );
		$cbPrioridade->setLabel ( 'Prioridade:' )->addMultiOptions ( array (3 => 'Baixa', 2 => 'Média', 1 => 'Alta' ) )->setRequired ( true );
		
		$txtCdLotacao = new Zend_Form_Element_Hidden ( 'RESP_CD_LOTACAO' );
		$txtCdLotacao->setRequired ( true )->removeDecorator ( 'label' );
		
		$hiddenRec = new Zend_Form_Element_Hidden ( 'SOLD_NR_REC_DESCENTRALIZAR' );
		$hiddenRec->removeDecorator ( 'label' );

		$cbRdDescentralizado = new Zend_Form_Element_Select ( 'SOLD_IC_REC_DESCENTRALIZADO' );
		$cbRdDescentralizado->setLabel ( 'Recurso a descentralizar:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( array (1 => 'Não', 0 => 'Sim' ) )->setAttrib ( 'class', 'SECOR' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		//aqui vai ser dipor
		if (($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) || ($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR)) {
			//se for dipor
			$txtJustSECOR->setRequired ( true );
			$txtValorAtendido->setRequired ( true );
			$cbRdDescentralizado->setRequired ( true );
			$this->addElements ( array ( $txtCodigo, $txtAno, $txtNrDespesa, $cboUG, $txtTipoResponsavel, $txtPtres, $txtNatureza, $cboTipoDespesa, $txtValor, $cbJustificativa,  $txtJustSolicitante, $cbPrioridade, $cboTipoSolicitacao, $txtValorAtendido, $txtJustSECOR, $cbRdDescentralizado, $hiddenRec, $cmdSubmit, $txtCdLotacao ) );
		} else {
			// outros
			$this->addElements ( array ( $txtCodigo, $txtAno, $txtNrDespesa, $txtJustDesp, $cboUG, $txtTipoResponsavel, $txtPtres, $txtNatureza, $cboTipoDespesa, $txtValor, $cbJustificativa, $txtJustSolicitante, $cbPrioridade, $cboTipoSolicitacao, $txtValorAtendido, $txtJustSECOR, $cmdSubmit, $txtCdLotacao ) );
		}
	}
}