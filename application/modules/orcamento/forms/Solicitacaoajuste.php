<?php
/**
 * Contém formuçarios da aplicação
*
* e-Admin
* e-Orçamento
* Form
*
* @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
*/

/**
 * Disponibiliza o formulário para entrada de dados sobre esfera.
 *
 * @category Orcamento
 * @package Orcamento_Form_Esfera
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Solicitacaoajuste extends Orcamento_Form_Base
{

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Definições iniciais do formulário
        $this->retornaFormulario ( 'solicitacao' );

        // Cria o campo
        $txtCodigo = new Zend_Form_Element_Hidden ( 'SOLA_ID_SOLICITACAO_AJUSTE' );

        // Define opções o controle $txtDOrigem
        $txtSolaDesp = new Zend_Form_Element_Text ( 'SOLA_NR_DESPESA' );
        $txtSolaDesp->setLabel ( 'Despesa:' );
        $txtSolaDesp->setAttrib ( 'size', 10 );
        $txtSolaDesp->setAttrib ( 'maxlenght', 8 );
        // $txtDOrigem->addFilter ( 'Digits' );
        $txtSolaDesp->setRequired ( true );


        // Definição do valor do ano exercicio        
        $anoEexercicio = date ( 'd-m-Y' );

        // Define opções para o campo SOLA_DT_SOLICITACAO
        $txtAno = new Zend_Form_Element_Text ( 'SOLA_DT_SOLICITACAO' );

        $txtAno->setLabel ( 'Data Solicitação:' );
        $txtAno->setAttrib ( 'size', '10' );
        $txtAno->setValue ( $anoEexercicio );
        $txtAno->setAttrib ( 'readonly', 'readonly' );
        $txtAno->setRequired ( true );

        // Registros da tabela de justificativas
        $dadosJust = new Orcamento_Business_Negocio_Justificativa ();
        $cbJustificativa = new Zend_Form_Element_Select ( 'SOLA_ID_JUSTIFICATIVA' );
        $cbJustificativa->setLabel ( 'Justificativa Padronizada:' );
        $cbJustificativa->addFilter ( 'StripTags' )->addMultiOptions ( array (
                '' => 'Selecione uma justificativa:' )
            )->addMultiOptions (
            $dadosJust->retornaCombo ()
        )->setRequired ( true );


        $txtJustificativaretorno = new Zend_Form_Element_Textarea( 'SOLA_DS_JUSTIFICATIVA_RETORNO' );
        $txtJustificativaretorno->setLabel ( 'Descrição Justificativa:' );
        $txtJustificativaretorno->setAttrib ( 'size', '60' );
        $txtJustificativaretorno->setAttrib ( 'maxlenght', 500 );
        $txtJustificativaretorno->setRequired ( true );

        // recupera a sessão.
        $sessao = new Orcamento_Business_Sessao ();

        // recupera o perfil do usuário logado na sessão.
        $perfil = $sessao->retornaPerfil();

        $txtJustificativanova = new Zend_Form_Element_Textarea( 'SOLA_DS_NOVA_JUSTIFICATIVA' );
        $txtJustificativanova->setLabel ( 'Nova Justificativa:' );
        $txtJustificativanova->setAttrib ( 'size', '60' );
        $txtJustificativanova->setAttrib ( 'maxlenght', 500 );
        if( $perfil['perfil'] != 'planejamento'){
            $txtJustificativanova->setAttrib( 'style', 'pointer-events:none; border-color: rgb(204, 204, 204); background: rgb(222, 222, 222);' );
        }

        $txtJustificativasetorial = new Zend_Form_Element_Textarea( 'SOLA_DS_JUSTIFICATIVA_SETORIAL' );
        $txtJustificativasetorial->setLabel ( 'Justificativa Setorial:' );
        $txtJustificativasetorial->setAttrib ( 'size', '60' );

        // $txtJustificativanova->setRequired ( true );
        
        $vlAcrescimoSolicitado = new Zend_Form_Element_Text ( 'SOLA_VL_SOLICITADO' );
        $vlAcrescimoSolicitado->setLabel ( 'Valor Acréscimo Solicitado:' );
        $vlAcrescimoSolicitado->setRequired ( true )
        ->setAttribs ( array ( 
                                'size', 25, 
                                'class' => 'valordespesa' 
                             ) 
        )->setValue ( 0 );



        $vlAcrescimoAtendido = new Zend_Form_Element_Text ( 'SOLA_VL_ATENDIDO' );
        $vlAcrescimoAtendido->setLabel ( 'Valor Acréscimo Atendido:' );
        
        if( $perfil['perfil'] != 'planejamento'){
            $vlAcrescimoAtendido->setAttrib( 'style', 'pointer-events:none; border-color: rgb(204, 204, 204); background: rgb(222, 222, 222);' );
        }
        
        $vlAcrescimoAtendido->setAttribs ( array ( 
                                'size', 25, 
                                'class' => 'valordespesa' 
                             ) 
        )->setValue ( 0 );

        $vltotal = new Zend_Form_Element_Text ( 'SOLA_VL_PROPOSTA_ORIGINAL' );
        $vltotal->setLabel ( 'Valor original da base da pré-proposta:' );
        $vltotal->setRequired ( true )
        ->setAttribs ( array ( 
                                'size', 25, 
                                'class' => 'valordespesa' 
                             ) 
        )->setValue (
        0 );
        
        $cbSituacao = new Zend_Form_Element_Select ( 'SOLA_IC_SITUACAO' );
        $cbSituacao->setLabel ( 'Situação atendida:' );
        if( $perfil['perfil'] != 'planejamento'){
            $cbSituacao->setAttrib( 'style', 'pointer-events:none; border-color: rgb(204, 204, 204); background: rgb(222, 222, 222);' );
        }          
        $cbSituacao->addFilter ( 'StripTags' )->addMultiOptions ( array (
                '' => 'Selecione:' )
            )->addMultiOptions (
                    array( 0 => ' Em definição ', 1 => ' Atendida ', 2 => ' Recusada ' )
        )->setValue(0)->setRequired ( true );


        $hidenTipo = new Zend_Form_Element_Hidden('SOLA_TP_SOLICITACAO');

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class',
        Orcamento_Business_Dados::CLASSE_SALVAR );

        // Adiciona os controles no formulário
        $this->addElement ( $txtCodigo );
        $this->addElement ( $txtSolaDesp );
        $this->addElement ( $txtAno );
        $this->addElement ( $cbJustificativa );
        $this->addElement ( $txtJustificativaretorno );
        if( $perfil['perfil'] == 'planejamento' or $perfil['perfil'] == 'desenvolvedor' ){
            $this->addElement ( $txtJustificativanova );
        }
        $this->addElement ( $txtJustificativasetorial );
        $this->addElement ( $vltotal );
        $this->addElement ( $vlAcrescimoSolicitado );
        $this->addElement ( $vlAcrescimoAtendido );        
        $this->addElement ( $cbSituacao );
        $this->addElement ( $hidenTipo );
        $this->addElement ( $cmdEnviar );
    }

}
