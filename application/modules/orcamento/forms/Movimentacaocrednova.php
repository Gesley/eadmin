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
class Orcamento_Form_Movimentacaocrednova extends Orcamento_Form_Base {

    public function init () {
        // Definições iniciais do formulário
        $this->retornaFormulario('movimentacaocrednova');

        // Cria o campo MOVC_CD_MOVIMENTACAO
        $txtCodigo = new Zend_Form_Element_Text('MOVC_CD_MOVIMENTACAO');

        // Define opções o controle $txtCodigo
        // $txtCodigo->setLabel ( 'Código da movimentação:' );
        // $txtCodigo->setAttrib ( 'size', '10' );
        // $txtCodigo->setAttrib ( 'maxlength', 6 );
        // $txtCodigo->addFilter ( 'StringTrim' );
        // $txtCodigo->addValidator ( 'Digits' );
        // $txtCodigo->setRequired ( true );
        // Cria o campo MOVC_NR_DESPESA_ORIGEM
        $txtDOrigem = new Zend_Form_Element_Text('MOVC_NR_DESPESA_ORIGEM');

        // Define opções o controle $txtDOrigem
        $txtDOrigem->setLabel('Despesa de origem:');
        $txtDOrigem->setAttrib('size', 10);
        $txtDOrigem->setAttrib('maxlenght', 8);
        // $txtDOrigem->addFilter ( 'Digits' );
        $txtDOrigem->addValidator(new Trf1_Orcamento_Validacao_Despesa());
        $txtDOrigem->setRequired(true);

        $validaDuplicado = new Trf1_Orcamento_Validacao_Diferente ();
        $validaDuplicado->defineDespesaOrigem($txtDOrigem);

        // Cria o campo MOVC_NR_DESPESA_ORIGEM
        $txtDDestino = new Zend_Form_Element_Text('MOVC_NR_DESPESA_DESTINO');

        // Define opções o controle $txtDDestino
        $txtDDestino->setLabel('Despesa de destino:');
        $txtDDestino->setAttrib('size', 10);
        $txtDDestino->setAttrib('maxlenght', 8);
        $txtDDestino->addValidator($validaDuplicado);
        // $txtDDestino->addFilter ( 'Digits' );
        $txtDDestino->setRequired(true);

        // Cria o campo MOVC_VL_MOVIMENTACAO
        $txtValor = new Zend_Form_Element_Text('MOVC_VL_MOVIMENTACAO');

        // Define opções o controle $txtValor
        $txtValor->setLabel('Valor:');
        $txtValor->setAttrib('size', 10);
        $txtValor->setAttrib('maxlenght', 20);
        $txtValor->setAttrib('class', 'valordespesa');
        // $txtValor->addFilter ( 'Digits' );
        $txtValor->setRequired(true);

        // // Cria o campo MOVC_DH_MOVIMENTACAO
        // $txtData = new Zend_Form_Element_Text ( 'MOVC_DH_MOVIMENTACAO' );
        //
        // Define opções o controle $txtData
        // $txtData->setLabel ( 'Data:' );
        // $txtData->setAttrib ( 'size', 16 );
        // $txtData->setAttrib ( 'class', 'datepicker' );
        // $txtData->setRequired ( true );
        // Cria o campo MOVC_DS_JUSTIF_SOLICITACAO
        $campo = 'MOVC_DS_JUSTIF_SOLICITACAO';
        $txtJustificativa = new Zend_Form_Element_Textarea($campo);

        // Define opções o controle $txtJustificativa
        $txtJustificativa->setLabel('Motivo da solicitação:');
        $txtJustificativa->setAttrib('size', 20);
        $txtJustificativa->setAttrib('maxlenght', 3000);
        $txtJustificativa->addFilter('StringTrim');

        // Cria o campo MOVC_DS_JUSTIF_SECOR
        $txtMotivo = new Zend_Form_Element_Textarea('MOVC_DS_JUSTIF_SECOR');

        // Define opções o controle $txtMotivo
        $txtMotivo->setLabel('Motivação setorial:');
        $txtMotivo->setAttrib('size', 20);
        $txtMotivo->setAttrib('maxlenght', 3000);
        $txtMotivo->setAttrib('class', 'DIPOR');
        // $txtMotivo->setAttrib ( 'id', 'MOVC_DS_JUSTIF_SECOR' );
        $txtMotivo->addFilter('StringTrim');
        // $txtMotivo->setRequired ( true );
        // Cria o campo MOVC_ID_TIPO_MOVIMENTACAO
        $cboTipos = new Zend_Form_Element_Select('MOVC_ID_TIPO_MOVIMENTACAO');

        // Dados sobre tipos de movimentação
        $tbTipos = new Trf1_Orcamento_Negocio_Movimentacaocred ();
        $tipos = $tbTipos->retornaTiposDeMovimentacao();

        // Define opções o controle $cboTipos
        $cboTipos->setLabel('Tipo da movimentação:');
        $cboTipos->addMultiOptions($tipos);
        // $cboTipos->setAttrib ( 'size', 20 );
        $cboTipos->setAttrib('class', 'DIPOR');
        $cboTipos->setRequired(true);

        // Cria o campo MOVC_CD_TIPO_SOLICITACAO
        $cboStatus = new Zend_Form_Element_Select('MOVC_CD_TIPO_SOLICITACAO');

        // Dados sobre tipos de solicitação
        $tbStatus = new Trf1_Orcamento_Negocio_Tiposolicitacao ();
        $status = $tbStatus->retornaCombo();

        // Define opções o controle $cboStatus
        $cboStatus->setLabel('Status da solicitação:');
        // $cboStatus->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboStatus->addMultiOptions($status);
        // $cboStatus->setAttrib ( 'size', 20 );
        $cboStatus->setAttrib('class', 'DIPOR');
        $cboStatus->setRequired(true);

        // Cria o campo MOVC_IC_MOVIMENT_REPASSADA
        $campo = 'MOVC_IC_MOVIMENT_REPASSADA';
        $txtICMovRepassada = new Zend_Form_Element_Hidden($campo);

        $opcoes [''] = 'Selecione';
        $opcoes [0] = 'Não repassado';
        $opcoes [1] = 'Repassado';

        // Define opções o controle $txtICMovRepassada
        // $txtICMovRepassada->setLabel ( 'Crédito repassado:' );
        // $txtMotivo->addMultiOptions ( $opcoes );
        $txtICMovRepassada->setValue(1);

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Salvar');

        // Define opções do controle $cmdEnviar
        $cmdEnviar = new Zend_Form_Element_Button('Salvar');
        $cmdEnviar->setLabel('Incluir')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        // Adiciona os controles no formulário
        $this->addElement($txtCodigo);
        $this->addElement($txtDOrigem);
        $this->addElement($txtDDestino);
        $this->addElement($txtValor);
        // $this->addElement ( $txtData );
        $this->addElement($txtValor);
        $this->addElement($txtJustificativa);
        $this->addElement($txtMotivo);
        $this->addElement($cboTipos);
        $this->addElement($cboStatus);
        $this->addElement($cmdEnviar);
        $this->addElement($txtICMovRepassada);
    }

}
