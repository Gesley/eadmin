<?php
/**
 * Contém formuçarios da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Form
 * 
 * @author Sandro Maceno [smaceno@stefanini.com]
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre programa de trabalho
 * resumido.
 *
 * @category Orcamento
 * @package Orcamento_Form_Alinea
 * @author Sandro Maceno [smaceno@stefanini.com]
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Alinea extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Sandro Maceno [smaceno@stefanini.com]
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'alinea' );
        
             // Cria o campo oculto ALIN_ID_ALINEA
        $campoId = new Zend_Form_Element_Hidden('ALIN_ID_ALINEA');
        
        // Cria o campo ALIN_ID_INCISO
        $cboTipoInciso = new Zend_Form_Element_Select ( 'ALIN_ID_INCISO' );
        
        // Dados da fonte
        $tbTipoNC = new Orcamento_Business_Negocio_Inciso ();
        
        // Define opções o controle $cboTipoNC
        $cboTipoInciso->setLabel ( 'Inciso:' );
        $cboTipoInciso->addMultiOptions ( array ( '' => 'Selecione' ) );
        $cboTipoInciso->addMultiOptions ( $tbTipoNC->retornaComboComposta () );
        $cboTipoInciso->setRequired ( true );
        $cboTipoInciso->setAttribs(
                array(
                    'style' => 'width:400px;'
                ));
        
        // Cria o campo ALIN_ID_ALINEA
        $txtCodigo = new Zend_Form_Element_Text ( 'ALIN_VL_ALINEA' );
        
        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel ( 'Alínea:' );
        $txtCodigo->setAttrib ( 'size', '2' );
        $txtCodigo->addValidator ( 'Alpha' );
        $txtCodigo->setAttrib ( 'maxlength',  2);
        $txtCodigo->addFilter ( 'StringTrim' );
        $txtCodigo->setRequired ( true );
        
        // Cria o campo ALIN_DS_ALINEA
        $txtDescricao = new Zend_Form_Element_Text ( 'ALIN_DS_ALINEA' );
        
        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel ( 'Descrição Alínea:' );
        $txtDescricao->setAttrib ( 'size', 50 );
        $txtDescricao->setAttrib ( 'maxlength', 400 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );
        
        // Valor da data $alin_dt_inclusao        
        $alin_dt_inclusao = new Zend_Form_Element_Hidden('ALIN_DT_INCLUSAO');
        $alin_dt_inclusao->setValue(date('d/m/y'));
        $alin_dt_inclusao->setRequired(false); 
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $classeSubmit = Orcamento_Business_Dados::CLASSE_SALVAR;
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', $classeSubmit );
        
        // Adiciona os controles no formulário
        $this->addElement ( $cboTipoInciso );
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $campoId );
        $this->addElement ( $alin_dt_inclusao );
        $this->addElement ( $cmdEnviar );
    }

}