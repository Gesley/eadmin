<?php

/**
 * Contém formuçarios da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Form
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre informativo.
 *
 * @category Orcamento
 * @package Orcamento_Form_Informativo
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Informativo extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'informativo' );

        $txtCodigo = new Zend_Form_Element_Hidden( 'INFO_NR_INFORMATIVO' );

        // Campo titulo
        $txtTitulo = new Zend_Form_Element_Text ( 'INFO_TX_TITULO_INFORMATIVO' );
        $txtTitulo->setLabel ( 'Titulo do informativo:' );
        $txtTitulo->setAttrib ( 'size', '55' );
        $txtTitulo->setAttrib ( 'maxlength', 80 );
        $txtTitulo->addFilter ( 'StringTrim' );
        $txtTitulo->setRequired ( true );

        // Campo data inicio
        $txtDataInicio = new Zend_Form_Element_Text ( 'INFO_DT_INICIO' );
        $txtDataInicio->setLabel ( 'Data de inicio:' );
        $txtDataInicio->setAttrib ( 'size', '15' );
        $txtDataInicio->setAttrib ( 'class', 'datepicker' );
        $txtDataInicio->setRequired ( true );

        // Campo data fim
        $txtDataFim = new Zend_Form_Element_Text ( 'INFO_DT_TERMINO' );
        $txtDataFim->setLabel ( 'Data de Termino:' );
        $txtDataFim->setAttrib ( 'size', '15' );
        $txtDataFim->setAttrib ( 'class', 'datepicker' );
        $txtDataFim->setRequired ( true );

        // Campo mensagem
        $txtMensagem = new Zend_Form_Element_Textarea( 'INFO_DS_INFORMATIVO' );
        $txtMensagem->setAttrib ( 'maxlength', 3000 );
        $txtMensagem->setLabel ( 'Mensagem:' );

        $txtMensagem->setRequired ( true );

        // Define opções para o campo status
        $responsavel = new Trf1_Orcamento_Negocio_Responsavel ();

        $cboResp = new Zend_Form_Element_Select ( 'INFR_CD_RESPONSAVEL' );
        $cboResp->setLabel ( 'Destinatário:' );
        $cboResp->addFilter ( 'StripTags' );
        $cboResp->addMultiOptions ( $responsavel->retornaCombo () );

        $cmdAdd = new Zend_Form_Element_Button ( 'Add' );
        $cmdAdd->setAttrib ('class', 'ceo_relatorio');
        $cmdAdd->setLabel ( 'Adicionar' )->setAttrib ( 'type', 'button' );
        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button ( 'Incluir' );
        $cmdSubmit->setLabel ( 'Incluir' )->setAttrib ( 'type', 'submit' )->setAttrib (
                'class', 'ceo_salvar' );

        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtTitulo );
        $this->addElement ( $txtDataInicio );
        $this->addElement ( $txtDataFim );
        $this->addElement ( $cboResp );
        $this->addElement ( $txtMensagem );
        $this->addElement ( $cmdAdd );
        $this->addElement ( $cmdSubmit );
    }

}
