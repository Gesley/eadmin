<?php
/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as regras negociais sobre notas de empenho
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Ef
 * @author Gesley Batista Rodrigues <falcon.griffith@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Ef extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Exec();

        // Define a negocio
        $this->_negocio = 'ef';

        // Define a negocio de Nota de empenho
        $this->_negocione = new Orcamento_Business_Negocio_Ne();

    }

    public function trataValor( $valor ) {
        $util = new Trf1_Orcamento_Valor ();
        return $util->formataMoedaOrcamento( $valor );
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "
            EXEC_CD_NOTA_EMPENHO,
            EXEC_CD_UG,
            EXEC_VL_JANEIRO,
            EXEC_VL_FEVEREIRO,
            EXEC_VL_MARCO,
            EXEC_VL_ABRIL,
            EXEC_VL_MAIO,
            EXEC_VL_JUNHO,
            EXEC_VL_JULHO,
            EXEC_VL_AGOSTO,
            EXEC_VL_SETEMBRO,
            EXEC_VL_OUTUBRO,
            EXEC_VL_NOVEMBRO,
            EXEC_VL_DEZEMBRO
        ";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = " * ";

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "EXEC_CD_NOTA_EMPENHO, ";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];

        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];

        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "
            EXEC_CD_NOTA_EMPENHO,
            EXEC_CD_UG
        ";

        // Devolve os campos, conforme ação
        return $campos [ $acao ];
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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND EXEC_CD_NOTA_EMPENHO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];

        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];

        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];

        // Condição para montagem do combo
        // $restricao [ 'combo' ] = " ESFE_DH_EXCLUSAO_LOGICA IS Null ";

        return $restricao [ $acao ];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array (
                'EXEC_CD_NOTA_EMPENHO' => array ( 'title' => 'Esfera', 'abbr' => '' ),
                'EXEC_CD_UG' => array ( 'title' => 'Esfera', 'abbr' => '' ),
                'EXEC_VL_JANEIRO' => array ( 'title' => 'Janeiro', 'abbr' => '' ),
                'EXEC_VL_FEVEREIRO' => array ( 'title' => 'Fevereiro', 'abbr' => '' ),
                'EXEC_VL_MARCO' => array ( 'title' => 'Março', 'abbr' => '' ),
                'EXEC_VL_ABRIL' => array ( 'title' => 'Abril', 'abbr' => '' ),
                'EXEC_VL_MAIO' => array ( 'title' => 'Maio', 'abbr' => '' ),
                'EXEC_VL_JUNHO' => array ( 'title' => 'Junho', 'abbr' => '' ),
                'EXEC_VL_JULHO' => array ( 'title' => 'Julho', 'abbr' => '' ),
                'EXEC_VL_AGOSTO' => array ( 'title' => 'Agosto', 'abbr' => '' ),
                'EXEC_VL_SETEMBRO' => array ( 'title' => 'Setembro', 'abbr' => '' ),
                'EXEC_VL_OUTUBRO' => array ( 'title' => 'Outubro', 'abbr' => '' ),
                'EXEC_VL_NOVEMBRO' => array ( 'title' => 'Novembro', 'abbr' => '' ),
                'EXEC_VL_DEZEMBRO' => array ( 'title' => 'Dezembro', 'abbr' => '' ),
         );

        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'CAMPO_NAO_EXISTENTE' );

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds ( $acao = null )
    {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id [ 'index' ] = $cache->retornaID_Listagem ( 'orcamento', $negocio );

        // Id para combo
        $id [ 'combo' ] = $cache->retornaID_Combo ( 'orcamento', $negocio );

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [ $acao ] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }



    /**
     * Trata os dados antes de gravar no banco
     * @param array $dados
     * @param string $txtName
     */
    public function incluirImportacao(array $ef, $txtName = null)
    {
        // armazenara as nes não cadastradas
        $ne = array();
        $res = array();
        $notasdeempenho = array();
        $efsem_empenho = array();

        $modelnoem = new Orcamento_Business_Negocio_Ne();

        foreach ($ef as $key => $exec) {
            $chaves[] = $exec['EXEC_CD_NOTA_EMPENHO'];
        }

        foreach ($chaves as $key => $c) {
            foreach ($ef as $key => $exec) {
                if( $c == $exec['EXEC_CD_NOTA_EMPENHO'] ){
                    $notasdeempenho[$c]['EXEC_CD_NOTA_EMPENHO'] = $exec['EXEC_CD_NOTA_EMPENHO'];
                    $notasdeempenho[$c]['EXEC_CD_UG'] = $exec['EXEC_CD_UG'];
                    $notasdeempenho[$c]['EXEC_VL_JANEIRO'] += $exec['EXEC_VL_JANEIRO'];
                    $notasdeempenho[$c]['EXEC_VL_FEVEREIRO'] += $exec['EXEC_VL_FEVEREIRO'];
                    $notasdeempenho[$c]['EXEC_VL_MARCO'] += $exec['EXEC_VL_MARCO'];
                    $notasdeempenho[$c]['EXEC_VL_ABRIL'] += $exec['EXEC_VL_ABRIL'];
                    $notasdeempenho[$c]['EXEC_VL_MAIO'] += $exec['EXEC_VL_MAIO'];
                    $notasdeempenho[$c]['EXEC_VL_JUNHO'] += $exec['EXEC_VL_JUNHO'];
                    $notasdeempenho[$c]['EXEC_VL_JULHO'] += $exec['EXEC_VL_JULHO'];
                    $notasdeempenho[$c]['EXEC_VL_AGOSTO'] += $exec['EXEC_VL_AGOSTO'];
                    $notasdeempenho[$c]['EXEC_VL_SETEMBRO'] += $exec['EXEC_VL_SETEMBRO'];
                    $notasdeempenho[$c]['EXEC_VL_OUTUBRO'] += $exec['EXEC_VL_OUTUBRO'];
                    $notasdeempenho[$c]['EXEC_VL_NOVEMBRO'] += $exec['EXEC_VL_NOVEMBRO'];
                    $notasdeempenho[$c]['EXEC_VL_DEZEMBRO'] += $exec['EXEC_VL_DEZEMBRO'];

                }
            }

        }

        $inseridos = 0;

        foreach($notasdeempenho as $nota) {

            $exNota = $modelnoem->existeNE( $nota['EXEC_CD_NOTA_EMPENHO'] );
            $exExec = $this->existeExecucao( $nota['EXEC_CD_NOTA_EMPENHO'] );

            if( $exNota['NOEM_CD_NOTA_EMPENHO'] ){

                if( $exExec['EXEC_CD_NOTA_EMPENHO'] ){
                    $this->deleteExecucao( $nota['EXEC_CD_NOTA_EMPENHO'] );
                }

                $insert = parent::incluir( $nota );
                $inseridos += 1;

            }else{
                $semEmpenho[] = $nota;
            }
        }

        $resultado = array(
            'INSERIDOS' => $inseridos,
            'SEM_EMPENHO' => $semEmpenho
        );

        return $resultado;
    }

    /**
     * Verifica se existe uma execução
     * @param int $ne
     * @return array
     */
    public function existeExecucao($ne)
    {
        $banco = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            SELECT
                EXEC_CD_NOTA_EMPENHO
                FROM CEO_TB_EXEC_EXECUCAO_NE
                WHERE EXEC_CD_NOTA_EMPENHO = '$ne' ";

        return $banco->fetchRow($sql);
    }

    /**
     * Exclui uma EXECUCAO
     * @param int $ne Numero da Nota de Empenho
     * @return bool
     */
    public function deleteExecucao ($ne)
    {
        $where = $this->_model->getAdapter()->quoteInto('EXEC_CD_NOTA_EMPENHO = ?', $ne);
        return $this->_model->delete($where);
    }

    public function deleteEf( $exec )
    {
        $sql = " DELETE FROM CEO_TB_EXEC_EXECUCAO_NE WHERE EXEC_CD_NOTA_EMPENHO = '".$exec."' ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        // Zend_debug::dump($sql); die;

        return $banco->query($sql);

        // return $this->executaQuery( $sql );
    }

}