<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Regra
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * 
 */
class Orcamento_Business_Negocio_Movimentacaocrednova extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        // $this->_model = new Orcamento_Model_DbTable_Movc2 ();
        $this->_model = new Orcamento_Model_DbTable_Movc ();
        
        // Define a negocio
        $this->_negocio = 'movimentacaocrednova';
    }

    public function incluir ($dados) {
        return parent::incluir($dados);
    }


    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";
        
        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "
MOVC.MOVC_CD_MOVIMENTACAO,
D1.DESP_AA_DESPESA,
CASE
    WHEN D1.DESP_AA_DESPESA = ".date('Y')." THEN 1
    ELSE 2
END AS EXERCICIO, /* ordenar pelo campo vigente */
D1.DESP_CD_UG,
MOVC.MOVC_NR_DESPESA_ORIGEM,
D1.DESP_CD_PT_RESUMIDO AS PTRES_ORIGEM,
UNOR1.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_ORIGEM,
PTR1.PTRS_SG_PT_RESUMIDO AS PTRS_SG_PT_RESUMIDO,
D1.DESP_CD_ELEMENTO_DESPESA_SUB AS NATUREZA_ORIGEM,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO) AS RESPONSAVEL_ORIGEM,
MOVC.MOVC_NR_DESPESA_DESTINO,
D2.DESP_CD_PT_RESUMIDO AS PTRES_DESTINO,
UNOR2.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_DESTINO,
D2.DESP_CD_ELEMENTO_DESPESA_SUB AS NATUREZA_DESTINO,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO) AS RESPONSAVEL_DESTINO,
TO_CHAR(MOVC.MOVC_DH_MOVIMENTACAO, '" .
         Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS MOVC_DH_MOVIMENTACAO,

DECODE ( MOVC.MOVC_ID_TIPO_MOVIMENTACAO,
		2, 'Movimentação regular',
		3, 'Alteração na proposta (LOA)  ' ) AS MOVC_ID_TIPO_MOVIMENTACAO,
     
T.TSOL_DS_TIPO_SOLICITACAO,
MOVC.MOVC_VL_MOVIMENTACAO,
CASE WHEN LENGTH(MOVC_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END AS MOVC_STATUS
        ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = '
MOVC.MOVC_CD_MOVIMENTACAO,
D1.DESP_AA_DESPESA,
D1.DESP_CD_UG,
MOVC.MOVC_NR_DESPESA_ORIGEM,
D1.DESP_CD_PT_RESUMIDO AS PTRES_ORIGEM,
D1.DESP_CD_ELEMENTO_DESPESA_SUB AS NATUREZA_ORIGEM,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO) AS RESPONSAVEL_ORIGEM,
MOVC.MOVC_NR_DESPESA_DESTINO,
D2.DESP_CD_PT_RESUMIDO AS PTRES_DESTINO,
D2.DESP_CD_ELEMENTO_DESPESA_SUB AS NATUREZA_DESTINO,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO) AS RESPONSAVEL_DESTINO,
MOVC.MOVC_CD_TIPO_SOLICITACAO,
T.TSOL_DS_TIPO_SOLICITACAO,
MOVC.MOVC_DS_JUSTIF_SOLICITACAO,
MOVC.MOVC_DS_JUSTIF_SECOR,
MOVC.MOVC_VL_MOVIMENTACAO            
            ';
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
MOVC.MOVC_CD_MOVIMENTACAO AS \"Código da movimentação\",
TO_CHAR(MOVC.MOVC_DH_MOVIMENTACAO, '" .
         Trf1_Orcamento_Definicoes::FORMATO_DATA . "')	AS \"Data e hora da solicitação\",
D1.DESP_AA_DESPESA AS \"Ano\",
D1.DESP_CD_UG                   || ' - ' || U.UNGE_DS_UG                                                       AS \"Ug\",
MOVC.MOVC_NR_DESPESA_ORIGEM     || ' - ' || EDS1.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || D1.DESP_DS_ADICIONAL AS \"Despesa de origem\",
D1.DESP_CD_PT_RESUMIDO          || ' - ' || PTR1.PTRS_DS_PT_RESUMIDO	                                       AS \"PTRES (Origem)\",
D1.DESP_CD_ELEMENTO_DESPESA_SUB || ' - ' || EDS1.EDSB_DS_ELEMENTO_DESPESA_SUB                                  AS \"Natureza (Origem)\",
RHC1.LOTA_SIGLA_LOTACAO         || ' - ' || 
    REPLACE( 
                RH_DESCRICAO_CENTRAL_LOTACAO ( RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO ), '-', ' '
            ) 
                                || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA( 
                                                                 RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO
                                                                        )                                      AS \"Responsável (Origem)\",
MOVC.MOVC_NR_DESPESA_DESTINO    || ' - ' || EDS2.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || D2.DESP_DS_ADICIONAL AS \"Despesa de destino\",
D2.DESP_CD_PT_RESUMIDO          || ' - ' || PTR2.PTRS_DS_PT_RESUMIDO                                           AS \"PTRES (Destino)\",
D2.DESP_CD_ELEMENTO_DESPESA_SUB || ' - ' || EDS2.EDSB_DS_ELEMENTO_DESPESA_SUB                                  AS \"Natureza (Destino)\",
RHC2.LOTA_SIGLA_LOTACAO         || ' - ' || 
    REPLACE(
                RH_DESCRICAO_CENTRAL_LOTACAO(RHC2.LOTA_SIGLA_SECAO, RHC2.LOTA_COD_LOTACAO), '-', ' '
            ) 
                                || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA( 
                                                                            RHC2.LOTA_SIGLA_SECAO, RHC2.LOTA_COD_LOTACAO
                                                                        )                                       AS \"Responsável (Destino)\",
MOVC.MOVC_DS_JUSTIF_SOLICITACAO											AS \"Motivo da solicitação\",                                                                        
MOVC.MOVC_DS_JUSTIF_SECOR												AS \"Motivação setorial\",
DECODE( MOVC.MOVC_ID_TIPO_MOVIMENTACAO, 2, 'Movimentação regular', 3, 'Alteração na proposta (LOA) ')		AS \"Tipo de movimentação\",
TSOL_DS_TIPO_SOLICITACAO AS \"Status da solicitação\",
MOVC.MOVC_VL_MOVIMENTACAO AS \"Valor\"
";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "MOVC.MOVC_CD_MOVIMENTACAO, ";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];
        
        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];
        
        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "";
        
        // Devolve os campos, conforme ação
        return $campos [ $acao ];
    }

    /**
     * Retorna string contendo as relações (joins) com outras tabelas
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaJoins ()
    {
        $join = "
Left JOIN 
	CEO_TB_TSOL_TIPO_SOLICITACAO T ON
		T.TSOL_CD_TIPO_SOLICITACAO = MOVC_CD_TIPO_SOLICITACAO
Left JOIN
	CEO_TB_DESP_DESPESA D1 ON
		D1.DESP_NR_DESPESA = MOVC_NR_DESPESA_ORIGEM
Left JOIN
	CEO_TB_RESP_RESPONSAVEL RSP1 ON
		RSP1.RESP_CD_RESPONSAVEL = D1.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO RHC1 ON
		RHC1.LOTA_COD_LOTACAO = RSP1.RESP_CD_LOTACAO AND
		RHC1.LOTA_SIGLA_SECAO = RSP1.RESP_DS_SECAO
Left JOIN
	CEO_TB_DESP_DESPESA D2 ON
		D2.DESP_NR_DESPESA = MOVC_NR_DESPESA_DESTINO
Left JOIN
	CEO_TB_RESP_RESPONSAVEL RSP2 ON
		RSP2.RESP_CD_RESPONSAVEL = D2.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO RHC2 ON
		RHC2.LOTA_COD_LOTACAO = RSP2.RESP_CD_LOTACAO AND
		RHC2.LOTA_SIGLA_SECAO = RSP2.RESP_DS_SECAO
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO PTR1 ON
		PTR1.PTRS_CD_PT_RESUMIDO = D1.DESP_CD_PT_RESUMIDO
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTR2 ON
		PTR2.PTRS_CD_PT_RESUMIDO			= D2.DESP_CD_PT_RESUMIDO
Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA           UNOR1 ON
        UNOR1.UNOR_CD_UNID_ORCAMENTARIA = PTR1.PTRS_CD_UNID_ORCAMENTARIA
Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA           UNOR2 ON
        UNOR2.UNOR_CD_UNID_ORCAMENTARIA = PTR2.PTRS_CD_UNID_ORCAMENTARIA
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP EDS1 ON
		EDS1.EDSB_CD_ELEMENTO_DESPESA_SUB = D1.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP EDS2 ON
		EDS2.EDSB_CD_ELEMENTO_DESPESA_SUB = D2.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA U ON
		U.UNGE_CD_UG = D1.DESP_CD_UG
            ";
        
        return $join;
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chaves
     *        Informa a chave, já tratada, se for o caso
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        $condicaoResponsaveis = '';
        
        // Zend_Debug::dump ( CEO_PERMISSAO_RESPONSAVEIS );
        
        if ( CEO_PERMISSAO_RESPONSAVEIS != '' ) {
            $d = 'DESP.DESP_CD_UG';
            $s = 'SOLD.SOLD_CD_UG';
            $s = 'D1.DESP_CD_UG';
            $cond = CEO_PERMISSAO_RESPONSAVEIS;
            
            $cond = str_replace ( $d, $s, $cond );
            
            $d = 'RHCL.';
            $s = 'RHC1.';
            
            $cond = str_replace ( $d, $s, $cond );
            
            $d = 'DESP.';
            $s = 'D1.';
            
            // $cond = str_replace ( $d, $s, $cond );
            
            $condicaoResponsaveis = $cond;
        }
        
        // Zend_Debug::dump ( $condicaoResponsaveis );
        // exit;
        
        // Condição para ação index
        $restricao [ 'index' ] = " $condicaoResponsaveis ORDER BY EXERCICIO ";
        
        // Condição para ação detalhe
        $restricao [ 'detalhe' ] = " AND MOVC_CD_MOVIMENTACAO IN ( $chaves ) ";
        $restricao [ 'detalhe' ] .= " $condicaoResponsaveis ";
        
        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];
        
        // Condição para montagem do combo
        $restricao [ 'combo' ] = "";
        
        return $restricao [ $acao ];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais regras
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica ( $chaves )
    {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula ();
        
        // Trata a chave primária (ou composta)
        $codmov = $this->separaChave ( $chaves );
        
        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO.CEO_TB_MOVC_MOVIMENTACAO_CRED MOVC
SET
    MOVC.MOVC_CD_MATRICULA_EXCLUSAO          = '$matricula',
    MOVC.MOVC_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    MOVC.MOVC_CD_MOVIMENTACAO                 IN ( $codmov ) AND
    MOVC.MOVC_DH_EXCLUSAO_LOGICA             IS Null            
            ";
        
        // Devolve a instrução sql para exclusão lógica
        return $sql;
    }

    /**
     * Restaura um ou mais registros logicamente excluídos
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ( $chaves )
    {
        // Trata a chave primária (ou composta)
        $movs = $this->separaChave ( $chaves );
        
        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_MOVC_MOVIMENTACAO_CRED
SET
    MOVC_CD_MATRICULA_EXCLUSAO          = Null,
    MOVC_DH_EXCLUSAO_LOGICA             = Null
WHERE
    MOVC_CD_MOVIMENTACAO                IN ( $movs ) AND
    MOVC_DH_EXCLUSAO_LOGICA             IS NOT Null
                ";
        
        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        
        $detalhes = array ( 
                'MOVC_CD_MOVIMENTACAO' => array ( 'title' => 'Código', 
                        'abbr' => '' ), 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'MOVC_NR_DESPESA_ORIGEM' => array ( 
                        'title' => 'Despesa (Origem)', 'abbr' => '' ), 
                'PTRES_ORIGEM' => array ( 'title' => 'PTRES (Origem)', 
                        'abbr' => '' ),
                'UNOR_ORIGEM' => array ( 'title' => 'UO (Origem)',
                        'abbr' => '' ),
                'PTRS_SG_PT_RESUMIDO' => array ( 'title' => 'Sigla', 
                        'abbr' => '' ),
                'NATUREZA_ORIGEM' => array ( 'title' => 'Natureza (Origem)',
                        'abbr' => '' ), 
                'RESPONSAVEL_ORIGEM' => array ( 
                        'title' => 'Responsável (Origem)', 'abbr' => '' ), 
                'MOVC_NR_DESPESA_DESTINO' => array ( 
                        'title' => 'Despesa (Destino)', 'abbr' => '' ), 
                'PTRES_DESTINO' => array ( 'title' => 'PTRES (Destino)', 
                        'abbr' => '' ),
                'UNOR_DESTINO' => array ( 'title' => 'UO (Destino)',
                'abbr' => '' ),
                'NATUREZA_DESTINO' => array ( 'title' => 'Natureza (Destino)',
                        'abbr' => '' ), 
                'RESPONSAVEL_DESTINO' => array ( 
                        'title' => 'Responsável (Destino)', 'abbr' => '' ), 
                'MOVC_DH_MOVIMENTACAO' => array ( 'title' => 'Data', 
                        'abbr' => '' ), 
                'MOVC_ID_TIPO_MOVIMENTACAO' => array ( 'title' => 'Tipo de movimentação', 
                        'abbr' => '' ), 
                'MOVC_VL_MOVIMENTACAO' => array ( 'title' => 'Valor', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'TSOL_DS_TIPO_SOLICITACAO' => array ( 'title' => 'Situação', 
                        'abbr' => '' ), 
                'MOVC_STATUS' => array ( 'title' => 'Status', 'abbr' => '' ) );
        
        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'CAMPO_NAO_EXISTENTE', 'EXERCICIO' );
        
        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaFormulario ( $formulario, $acao )
    {
        // Define algumas variáveis...
        $solic = Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA;
        $incluir = Orcamento_Business_Dados::ACTION_INCLUIR;
        $editar = Orcamento_Business_Dados::ACTION_EDITAR;
        $campo = 'MOVC_CD_TIPO_SOLICITACAO';
        
        // Se for inclusão...
        if ( $acao == $incluir ) {
            $formulario = $this->removeOpcoesStatus ( $formulario );
            
            // Remove campo que conterão valores padrão
            $formulario->removeElement ( 'MOVC_CD_MOVIMENTACAO' );
            $formulario->removeElement ( 'MOVC_DH_MOVIMENTACAO' );
            $formulario->removeElement ( 'MOVC_IC_MOVIMENT_REPASSADA' );
            
            $campo = 'MOVC_DS_JUSTIF_SECOR';
            Orcamento_Business_Tela_Crud::bloqueiaCampo ( $formulario, $campo );
        }
        
        return $formulario;
    }

    /**
     * Remove as opções do combo de status, exceto o item 'Solicitada'
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function removeOpcoesStatus ( $formulario )
    {
        // Define algumas variáveis...
        $solic = Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA;
        $campo = 'MOVC_CD_TIPO_SOLICITACAO';
        
        // Remove opções que não a de solicitação
        $tipos = $formulario->$campo->getMultiOptions ();
        foreach ( $tipos as $c => $opcao ) {
            if ( $c != $solic ) {
                $formulario->$campo->removeMultiOption ( $c );
            }
        }
        
        return $formulario;
    }

    /**
     * Efetua transformações nos dados, se aplicável
     *
     * @param array $dados
     *        Dados do registro a ser transformado, se aplicável
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return array $dados
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaDados ( $dados, $acao )
    {
        // Zend_Debug::dump ( 'Dados antes de transformar' );
        // Zend_Debug::dump ( $dados );
        
        // Efetua modificações
        if ( $acao == Orcamento_Business_Dados::ACTION_INCLUIR ) {
            // Campo data conterá data e hora da transação
            $dados [ 'MOVC_DH_MOVIMENTACAO' ] = new Zend_Db_Expr ( 'SYSDATE' );
            
            // Justificativa da SECOR acontece apenas na edição
            $dados [ 'MOVC_DS_JUSTIF_SECOR' ] = null;
            
            // Verifica se dada solicitação pode ser atendida automaticamente
            $negocio = new Trf1_Orcamento_Negocio_Movimentacaocred ();
            $resultado = $negocio->permiteMovimentacao ( $dados );
            
            // O não atendimento auto gera um alerta extra
            $msg = $resultado [ 'mensagem' ];
            if ( trim ( $msg ) != '' ) {
                // Grava nova mensagem em sessao
                $sessao = new Orcamento_Business_Sessao ();
                $sessao->defineMensagemExtra ( $msg, 'notice' );
            }
            
            if ( $resultado [ 'permissao' ] ) {
                // Definições...
                $msg = 'Solicitação atendida automaticamente!';
                $atendida = Orcamento_Business_Dados::TIPO_SOLICITACAO_ATENDIDA;
                
                // Novos dados se solicitação puder ser atendida automaticamente
                $dados [ 'MOVC_DS_JUSTIF_SECOR' ] = $msg;
                $dados [ 'MOVC_CD_TIPO_SOLICITACAO' ] = $atendida;
            }
        }
        
        // Zend_Debug::dump ( 'Resultado...' );
        // Zend_Debug::dump ( $resultado );
        
        // Campo valor deve ser formatado
        $valorOld = $dados [ "MOVC_VL_MOVIMENTACAO" ];
        
        $valor = new Trf1_Orcamento_Valor ();
        $valorNovo = $valor->retornaValorParaBancoRod ( $valorOld );
        $vl = new Zend_Db_Expr ( "TO_NUMBER(" . $valorNovo . ")" );
        
        $dados [ "MOVC_VL_MOVIMENTACAO" ] = $vl;
        
        // Zend_Debug::dump ( 'Dados depois de transformar' );
        // Zend_Debug::dump ( $dados );
        // exit ();
        
        // Retorna os dados
        return $dados;
    }

    /**
     * Verifica se há algum impedimento negocial para, em caso verdadeiro,
     * bloquear todos os campos do formulário
     *
     * @tutorial Esse método deve ser sobrescrito na classe pai
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @param array $dados
     *        Dados do registro
     * @return boolean
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaDeveBloquearCampos ( $acao, $formulario, $dados )
    {
        // Define algumas variáveis...
        $editar = Orcamento_Business_Dados::ACTION_EDITAR;
        $solic = Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA;
        
        if ( $acao == $editar ) {
            $status = ( int ) $dados [ 'MOVC_CD_TIPO_SOLICITACAO' ];
            
            if ( $status != $solic ) {
                // Devolve verdadeiro para travamento do formulário
                return true;
            } else {
                // Busca perfil
                $sessao = new Orcamento_Business_Sessao ();
                $perfilFull = $sessao->retornaPerfil ();
                $perfil = $perfilFull [ 'perfil' ];
                
                $pDipor = Orcamento_Business_Dados::PERMISSAO_DIPOR;
                $pDesenv = Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR;
                
                if ( $perfil != $pDipor && $perfil != $pDesenv ) {
                    // Define alias
                    $f = $formulario;
                    $campo = 'MOVC_DS_JUSTIF_SECOR';
                    
                    // Bloqueia campo de justificativa...
                    Orcamento_Business_Tela_Crud::bloqueiaCampo ( $f, $campo );
                    
                    // ...e remove demais status
                    $formulario = $this->removeOpcoesStatus ( $formulario );
                }
            }
        
        }
        
        return false;
    }

}