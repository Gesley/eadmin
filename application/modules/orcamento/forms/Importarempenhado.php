<?php

/**
 * Criação do forms base para importação.
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Criação do forms base para importação.
 *
 * @category Orcamento
 * @package Orcamento_Form_Importarempenhado
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Importarempenhado extends Orcamento_Form_ImportacaoBase {
    
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'importarfinanceiro' );
        
        $txtCodigo = new Zend_Form_Element_Text ( 'IMPO_ID_IMPORTACAO' );      
        $txtCodigo->setLabel ( 'Código:' );
        $txtCodigo->setAttrib ( 'size', 5 );
        
        $txtAno = new Zend_Form_Element_Text ( 'IMPA_AA_IMPORTACAO' );
        $txtAno->setLabel ( 'Ano exercicio:' );
        $txtAno->setAttrib ( 'size', 4 );
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->addValidator ( 'Digits' );      
        $txtAno->setRequired ( true ); 

        $txtCodigoArquivo = new Zend_Form_Element_Text ( 'IMPO_ID_IMPORT_ARQUIVO' );
        $txtCodigoArquivo->setLabel ( 'Codigo da Importação:' );
        $txtCodigoArquivo->setAttrib ( 'size', 5 );
        $txtCodigoArquivo->setRequired ( true );        
        
        $tbUG = new Trf1_Orcamento_Negocio_Ug ();
        $txtUg = new Zend_Form_Element_Select ( 'IMPO_CD_UG' );
        $txtUg->setLabel ( 'Unidade gestora:' )->addFilter ( 'StripTags' )
        ->addMultiOptions ( array ('' => 'Selecione' ) )
        ->addMultiOptions ( $tbUG->retornaCombo () )->setRequired ( true );

        $intConta = new Zend_Form_Element_Text ( 'IMPO_CD_CONTA_CONTABIL' );    
        $intConta->setLabel ( 'Conta Contábil:' );
        $intConta->setAttrib ( 'size', 10 );
        $intConta->setAttrib ( 'maxlength', 9 );
        $intConta->addFilter ( 'StringTrim' );
        $intConta->setRequired ( true );

        $txtResultado = new Zend_Form_Element_Text ( 'IMPO_CD_RESULTADO_PRIMARIO' );    
        $txtResultado->setLabel ( 'Resultado Primário:' );
        $txtResultado->setAttrib ( 'size', 5 );
        $txtResultado->setAttrib ( 'maxlength', 1 );
        $txtResultado->addFilter ( 'StringTrim' );
        $txtResultado->setRequired ( true );
    
        // Cria o campo CRED_CD_FONTE
        $txtFonte = new Zend_Form_Element_Select ( 'IMPO_CD_FONTE' );        
        $tbFonte = new Trf1_Orcamento_Negocio_Fonte ();
        $txtFonte->setLabel ( 'Fonte:' );
        $txtFonte->addMultiOptions ( array ( '' => 'Selecione' ) );
        $txtFonte->addMultiOptions ( $tbFonte->retornaCombo () );
        $txtFonte->setRequired ( true );

        // Esfera
        $tbEsfera = new Trf1_Orcamento_Negocio_Esfera ();
        $txtEsfera = new Zend_Form_Element_Select ( 'IMPO_CD_ESFERA' );
        $txtEsfera->setLabel ( 'Esfera:' )->addFilter ( 'StripTags' )->addMultiOptions (
        array ( '' => 'Selecione a esfera' ) )->addMultiOptions (
        $tbEsfera->retornaCombo () )->setRequired ( true );        
        
        // Elemento de Despesa
        $txtElemento = new Zend_Form_Element_Text (
        'IMPO_CD_NATUREZA_DESPESA' );
        $txtElemento->setLabel ( 'Natureza da despesa:' )->setRequired ( true )->setAttribs (
        array ( 'size' => '70', 'maxlength' => 20 ) )->setDescription (
        'A lista será carregada após digitar 3 caracteres.' );        

        // PTRes - Programa de Trabalho Resumido
        $txtPtres = new Zend_Form_Element_Text ( 'IMPO_CD_PTRES' );
        $txtPtres->setLabel ( 'PTRES:' )->setRequired ( true )->setAttribs (
        array ( 'size' => '90', 'maxlength' => 20 ) )->setDescription (
        'A lista será carregada após digitar 3 caracteres.' );

        $floTotalJan = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_JAN' );
        $floTotalJan->setLabel ( 'Total Janeiro:' );
        $floTotalJan->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalJan->setAttrib ( 'maxlength', 40 );
        $floTotalJan->addFilter ( 'StringTrim' );
        $floTotalJan->setRequired ( true );

        $floTotalFev = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_FEV' );
        $floTotalFev->setLabel ( 'Total Fevereiro:' );
        $floTotalFev->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalFev->setAttrib ( 'maxlength', 40 );
        $floTotalFev->addFilter ( 'StringTrim' );
        $floTotalFev->setRequired ( true );

        $floTotalMar = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_MAR' );
        $floTotalMar->setLabel ( 'Total Março:' );
        $floTotalMar->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalMar->setAttrib ( 'maxlength', 40 );
        $floTotalMar->addFilter ( 'StringTrim' );
        $floTotalMar->setRequired ( true );

        $floTotalAbr = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_ABR' );
        $floTotalAbr->setLabel ( 'Total Abril:' );
        $floTotalAbr->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalAbr->setAttrib ( 'maxlength', 40 );
        $floTotalAbr->addFilter ( 'StringTrim' );
        $floTotalAbr->setRequired ( true );

        $floTotalMai = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_MAI' );
        $floTotalMai->setLabel ( 'Total Maio:' );
        $floTotalMai->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalMai->setAttrib ( 'maxlength', 40 );
        $floTotalMai->addFilter ( 'StringTrim' );
        $floTotalMai->setRequired ( true );

        $floTotalJun = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_JUN' );
        $floTotalJun->setLabel ( 'Total Junho:' );
        $floTotalJun->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalJun->setAttrib ( 'maxlength', 40 );
        $floTotalJun->addFilter ( 'StringTrim' );
        $floTotalJun->setRequired ( true );

        $floTotalJul = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_JUL' );
        $floTotalJul->setLabel ( 'Total Julho:' );
        $floTotalJul->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalJul->setAttrib ( 'maxlength', 40 );
        $floTotalJul->addFilter ( 'StringTrim' );
        $floTotalJul->setRequired ( true );

        $floTotalAgo = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_AGO' );
        $floTotalAgo->setLabel ( 'Total Agosto:' );
        $floTotalAgo->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalAgo->setAttrib ( 'maxlength', 40 );
        $floTotalAgo->addFilter ( 'StringTrim' );
        $floTotalAgo->setRequired ( true );

        $floTotalSet = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_SET' );
        $floTotalSet->setLabel ( 'Total Setembro:' );
        $floTotalSet->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalSet->setAttrib ( 'maxlength', 40 );
        $floTotalSet->addFilter ( 'StringTrim' );
        $floTotalSet->setRequired ( true );

        $floTotalOut = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_OUT' );
        $floTotalOut->setLabel ( 'Total Outubro:' );
        $floTotalOut->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalOut->setAttrib ( 'maxlength', 40 );
        $floTotalOut->addFilter ( 'StringTrim' );
        $floTotalOut->setRequired ( true );

        $floTotalNov = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_NOV' );
        $floTotalNov->setLabel ( 'Total Novembro:' );
        $floTotalNov->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalNov->setAttrib ( 'maxlength', 40 );
        $floTotalNov->addFilter ( 'StringTrim' );
        $floTotalNov->setRequired ( true );

        $floTotalDez = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL_DEZ' );
        $floTotalDez->setLabel ( 'Total Dezembro:' );
        $floTotalDez->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => 'valordespesa' ) )->setValue (
        0 );
        $floTotalDez->setAttrib ( 'maxlength', 40 );
        $floTotalDez->addFilter ( 'StringTrim' );
        $floTotalDez->setRequired ( true );

        $floTotal = new Zend_Form_Element_Text ( 'IMPO_VL_TOTAL' );
        $floTotal->setLabel ( 'Total:' );
        $floTotal->setAttrib ( 'size', 20)->setAttribs ( array ( 'size', 25, 'class' => '' ) )->setValue (
        0 );
        $floTotal->setAttrib ( 'maxlength', 40 );
        $floTotal->addFilter ( 'StringTrim' );
        $floTotal->setRequired ( true );

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
        
        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', 
        Orcamento_Business_Dados::CLASSE_SALVAR );
        
        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtAno );
        // $this->addElement ( $txtCodigoArquivo );
        $this->addElement ( $txtUg );
        $this->addElement ( $intConta );
        $this->addElement ( $txtResultado );
        $this->addElement ( $txtFonte );
        $this->addElement ( $txtEsfera );
        $this->addElement ( $txtPtres );
        $this->addElement ( $txtElemento );
        $this->addElement ( $floTotalJan );
        $this->addElement ( $floTotalFev );
        $this->addElement ( $floTotalMar );
        $this->addElement ( $floTotalAbr );
        $this->addElement ( $floTotalMai );
        $this->addElement ( $floTotalJun );
        $this->addElement ( $floTotalJul );
        $this->addElement ( $floTotalAgo );
        $this->addElement ( $floTotalSet );
        $this->addElement ( $floTotalOut );
        $this->addElement ( $floTotalNov );
        $this->addElement ( $floTotalDez );
        $this->addElement ( $floTotal );
        $this->addElement ( $cmdEnviar );
    }

}