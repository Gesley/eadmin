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
 * Pergunta a despesa para buscar Saldo, Extrato ou Projeção; conforme a tela
 * que chama este formulário
 *
 * @category Orcamento
 * @package Orcamento_Form_Despesapergunta
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Despesapergunta extends Orcamento_Form_Base
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
        $this->retornaFormulario ( 'DespesaPergunta' );

        $txtDespesa = new Zend_Form_Element_Text ( 'DESP_NR_DESPESA' );
        $txtDespesa->setLabel ( 'Despesa:' );
        $txtDespesa->setRequired ( true );
        $txtDespesa->setAttrib ( 'size', 10 );
        $txtDespesa->setAttrib ( 'autofocus', 'autofucus' );
        $txtDespesa->addValidator ( 'Digits' );

        // Cria o botão de adicionar
        $cmdAdicionar = new Zend_Form_Element_Button ( 'DESP_DESPESA_ADD' );
        $cmdAdicionar->setLabel ( 'Adicionar' );
        $cmdAdicionar->setAttrib ( 'class', 'ceo_consultar' );

        // Adiciona os controles no formulário
        $this->addElement ( $txtDespesa );
        $this->addElement ( $cmdAdicionar );
    }

}
