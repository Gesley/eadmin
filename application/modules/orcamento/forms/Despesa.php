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
 * Disponibiliza o formulário para entrada de dados sobre despesa.
 *
 * @category Orcamento
 * @package Orcamento_Form_Despesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Despesa extends Zend_Form
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        $this->setName ( 'frmDespesa' )->setMethod ( 'post' )->setAttrib (
        'id', 'frmDespesa' )->setElementFilters (
        array ( 'StripTags', 'StringTrim' ) );

        // TODO: ver modificação / inclusão de decorators
        $txtNrDespesa = new Zend_Form_Element_Hidden ( 'DESP_NR_DESPESA' );

        // Descrição adicional
        $txtDescricaoAdicional = new Zend_Form_Element_Textarea (
        'DESP_DS_ADICIONAL' );
        $txtDescricaoAdicional->setLabel ( 'Descrição da despesa:' )->setRequired (
        true )->setAttrib ( 'size', 40 );

        // Cria o campo DESP_AA_DESPESA
        $txtAno = new Zend_Form_Element_Select ( 'DESP_AA_DESPESA' );

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

        // Reflexão da despesa no ano seguinte
        $chxReflete = new Zend_Form_Element_Checkbox ( 'DESP_IC_REFLEXO_EXERCICIO' );
        $chxReflete->setChecked( true );
        $chxReflete->setLabel( 'Reflexo no ano seguinte:' );

        // UG - Unidade Gestora
        $tbUG = new Trf1_Orcamento_Negocio_Ug ();
        $cboUG = new Zend_Form_Element_Select ( 'DESP_CD_UG' );
        $cboUG->setLabel ( 'Unidade gestora:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a unidade gestora' ) )->addMultiOptions (
        $tbUG->retornaCombo () )->setRequired ( true );

        $ajaxResponsavel = new Zend_Form_Element_Text (
        'SG_DS_FAMILIA_RESPONSAVEL' );
        $ajaxResponsavel->setLabel ( 'Responsável:' )->setRequired ( true )->setAttribs (
        array ( 'size' => '100', 'maxlength' => 120 ) )->setDescription (
        'A lista será carregada após digitar 3 caracteres.' );

        $txtResponsavel = new Zend_Form_Element_Hidden ( 'DESP_CD_RESPONSAVEL' );
        $txtResponsavel->setRequired ( false );

        // Esfera
        $tbEsfera = new Trf1_Orcamento_Negocio_Esfera ();
        $cboEsfera = new Zend_Form_Element_Select ( 'DESP_CD_ESFERA' );
        $cboEsfera->setLabel ( 'Esfera:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a esfera' ) )->addMultiOptions (
        $tbEsfera->retornaCombo () )->setRequired ( true );

        // PTRes - Programa de Trabalho Resumido
        $txtPTRes = new Zend_Form_Element_Text ( 'DESP_CD_PT_RESUMIDO' );
        $txtPTRes->setLabel ( 'PTRES:' )->setRequired ( true )->setAttribs (
        array ( 'size' => '90', 'maxlength' => 20 ) )->setDescription (
        'A lista será carregada após digitar 3 caracteres.' );

        // Elemento de Despesa
        $txtElemento = new Zend_Form_Element_Text (
        'DESP_CD_ELEMENTO_DESPESA_SUB' );
        $txtElemento->setLabel ( 'Natureza da despesa:' )->setRequired ( true )->setAttribs (
        array ( 'size' => '70', 'maxlength' => 20 ) )->setDescription (
        'A lista será carregada após digitar 3 caracteres.' );

        // Tipo de Despesa
        $tbTipoDespesa = new Trf1_Orcamento_Negocio_Tipodespesa ();
        $cboTipoDespesa = new Zend_Form_Element_Select ( 'DESP_CD_TIPO_DESPESA' );
        $cboTipoDespesa->setLabel ( 'Caráter da despesa:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o caráter da despesa' ) )->addMultiOptions (
        $tbTipoDespesa->retornaCombo () )->setRequired ( true );

        // Fonte
        $tbFonte = new Trf1_Orcamento_Negocio_Fonte ();
        $cboFonte = new Zend_Form_Element_Select ( 'DESP_CD_FONTE' );
        $cboFonte->setLabel ( 'Fonte:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a fonte' ) )->addMultiOptions (
        $tbFonte->retornaCombo () )->setRequired ( true );

        // Categoria
        $tbCategoria = new Trf1_Orcamento_Negocio_Categoria ();
        $cboCategoria = new Zend_Form_Element_Select ( 'DESP_CD_CATEGORIA' );
        $cboCategoria->setLabel ( 'Categoria:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a categoria' ) )->addMultiOptions (
        $tbCategoria->retornaCombo () )->setRequired ( true );

        // Vinculacao
        $tbVinculacao = new Trf1_Orcamento_Negocio_Vinculacao ();
        $cboVinculacao = new Zend_Form_Element_Select ( 'DESP_CD_VINCULACAO' );
        $cboVinculacao->setLabel ( 'Vinculação:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a vinculação' ) )->addMultiOptions (
        $tbVinculacao->retornaCombo () )->setRequired ( true );

        // Tipo de Recurso
        $tbTipoRecurso = new Trf1_Orcamento_Negocio_Tiporecurso ();
        $cboTipoRecurso = new Zend_Form_Element_Select ( 'DESP_CD_TIPO_RECURSO' );
        $cboTipoRecurso->setLabel ( 'Tipo de recurso:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o tipo de recurso' ) )->addMultiOptions (
        $tbTipoRecurso->retornaCombo () )->setRequired ( true );

        // Tipo de Orçamento
        $tbTipoOrcamento = new Trf1_Orcamento_Negocio_Tipoorcamento ();
        $cboTipoOrcamento = new Zend_Form_Element_Select (
        'DESP_CD_TIPO_ORCAMENTO' );
        $cboTipoOrcamento->setLabel ( 'Tipo de orçamento:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o tipo de orçamento' ) )->addMultiOptions (
        $tbTipoOrcamento->retornaCombo () )->setRequired ( true );

        // Perspectiva
        $tbPerspectiva = new Orcamento_Business_Negocio_Perspectiva ();
        $cboPerspectiva = new Zend_Form_Element_Select (
        'DESP_CD_PERS_PERSPECTIVA' );
        $cboPerspectiva->setLabel ( 'Perspectiva:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a perspectiva' ) )->addMultiOptions (
        $tbPerspectiva->retornaCombo() )->setRequired ( false );

        // Macrodesafio
        $tbMacrodesafio = new Orcamento_Business_Negocio_Macrodesafio ();
        $cboMacrodesafio = new Zend_Form_Element_Select (
        'DESP_CD_MACRO_MACRODESAFIO' );
        $cboMacrodesafio->setLabel ( 'Macrodesafio:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o Macrodesafio' ) )->addMultiOptions (
        $tbMacrodesafio->retornaCombo() )->setRequired ( false );

        // Programa
        $tbPrograma = new Trf1_Orcamento_Negocio_Programa ();
        $cboPrograma = new Zend_Form_Element_Select ( 'DESP_CD_PROGRAMA' );
        $cboPrograma->setLabel ( 'Programa:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o programa' ) )->addMultiOptions (
        $tbPrograma->retornaCombo ( $txtAno->getValue () ) )->setRequired (
        false );

        // Objetivo
        $tbObjetivo = new Trf1_Orcamento_Negocio_Objetivo ();
        $cboObjetivo = new Zend_Form_Element_Select ( 'DESP_CD_OBJETIVO' );
        $cboObjetivo->setLabel ( 'Objetivo:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o objetivo' ) )->addMultiOptions (
        $tbObjetivo->retornaCombo ( $txtAno->getValue () ) )->setRequired (
        false );

        // Tipo Operacional
        $tbTipoOperacional = new Trf1_Orcamento_Negocio_Tipooperacional ();
        $cboTipoOperacional = new Zend_Form_Element_Select (
        'DESP_CD_TIPO_OPERACIONAL' );
        $cboTipoOperacional->setLabel ( 'Tipo operacional:' )->addFilter (
        'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione o tipo operacional' ) )->addMultiOptions (
        $tbTipoOperacional->retornaCombo () )->setRequired ( false );


        // Valor mensal máximo autorizado
        $txtVRMensalMaxAutorizado = new Zend_Form_Element_Text (
        'DESP_VL_MAX_MENSAL_AUTORIZADO' );
        $txtVRMensalMaxAutorizado->setLabel ( 'Valor mensal máximo autorizado:' );
        //$txtVRMensalMaxAutorizado->setRequired ( true );
        $txtVRMensalMaxAutorizado->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );

        // Despesa conferida
        $chxConf = new Zend_Form_Element_Checkbox ( 'DESP_IC_CONFERIDO' );
        $chxConf->setLabel( 'Conferido:' )->setChecked(true);

        // Despesa finalizado
        $chxFin = new Zend_Form_Element_Checkbox ( 'DESP_IC_FINALIZADO' );
        $chxFin->setLabel( 'Finalizado:' )->setChecked(true);

        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
        $cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib (
        'class', 'ceo_salvar' );

        $this->addElements (
        array ( $txtNrDespesa, $txtAno, $chxReflete, $txtDescricaoAdicional, $cboUG,
                $ajaxResponsavel, $cboEsfera, $txtPTRes, $txtElemento,
                $cboTipoDespesa, $cboFonte, $cboCategoria, $cboVinculacao,
                $cboTipoRecurso, $cboTipoOrcamento, $cboPrograma, $cboObjetivo,
                $cboTipoOperacional, $txtVRMensalMaxAutorizado, $chxConf, $chxFin, $txtResponsavel,
                $cboPerspectiva,$cboMacrodesafio,
                $cmdSubmit
                 ) );
    }

}
