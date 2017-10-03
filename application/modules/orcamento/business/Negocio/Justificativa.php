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
 * Contém as regras negociais sobre justificativa
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Justificativa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Justificativa extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init () {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Just ();

        // Define a negocio
        $this->_negocio = 'justificativa';
    }

    public function incluir ($dados) {
        // remove o codigo
        unset($dados["JUST_ID_JUSTIFICATIVA"]);

        return parent::incluir($dados);
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
    public function retornaCampos ($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
JUST_ID_JUSTIFICATIVA,
JUST_DS_TITULO,
JUST_DS_DESCRICAO,
CASE JUST_IC_SITUACAO
    WHEN '0' THEN 'Inativo'
    WHEN '1' THEN 'Ativo' END AS
JUST_IC_SITUACAO
        		";

        // Campos para a serem apresentados na editarAction
        $campos ['incluir'] = "
        		JUST_DS_TITULO,
        		JUST_DS_DESCRICAO,
        		JUST_IC_SITUACAO
        		";
        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = "*";

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
        JUST_ID_JUSTIFICATIVA                  AS \"Código\",
        JUST_DS_TITULO                             AS \"Titulo\",
        JUST_DS_DESCRICAO                     AS \"Titulo\",
        CASE JUST_IC_SITUACAO
            WHEN '0' THEN 'Inativo'
            WHEN '1' THEN 'Ativo' END
        AS \"Situação da justificativa\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "JUST_ID_JUSTIFICATIVA, ";
        $campos ['excluir'] .= $campos ['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos ['restaurar'] = $campos ['excluir'];

        // Campos para a serem apresentados num combo
        // $campos [ 'combo' ] = "";
        // Devolve os campos, conforme ação
        return $campos [$acao];
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
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {
        // Condição para ação editar
        $restricao ['detalhe'] = " AND JUST_ID_JUSTIFICATIVA IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

        // Condição para montagem do combo
        $restricao ['combo'] = " JUST_DT_EXCLUSAO_LOGICA IS Null ";

        return $restricao [$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais justificativas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlExclusaoLogica ($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();
        Zend_Debug::dump($matricula);
        die;
        // Trata a chave primária (ou composta)
        $justificativas = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_JUST_JUSTIFICATIVA
SET
    JUST_CD_MATRICULA_EXCLUSAO          = '$matricula',
    JUST_DT_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    JUST_ID_JUSTIFICATIVA                      IN ( $justificativas ) AND
    JUST_DT_EXCLUSAO_LOGICA             IS Null
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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ($chaves) {
        // Trata a chave primária (ou composta)
        $justificativas = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_JUST_JUSTIFICATIVA
SET
    JUST_CD_MATRICULA_EXCLUSAO          = Null,
    JUST_DT_EXCLUSAO_LOGICA             = Null
WHERE
    JUST_ID_JUSTIFICATIVA                      IN ( $justificativas ) AND
    JUST_DT_EXCLUSAO_LOGICA             IS NOT Null
                ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid () {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'JUST_ID_JUSTIFICATIVA' => array('title' => 'Cod', 'abbr' => ''),
            'JUST_DS_TITULO' => array('title' => 'Titulo', 'abbr' => ''),
            'JUST_DS_DESCRICAO' => array('title' => 'Descrição', 'abbr' => ''),
            'JUST_IC_SITUACAO' => array('title' => 'Status', 'abbr' => 'Informa se o registro está ou não ativo'));

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array('JUST_DT_EXCLUSAO_LOGICA', 'JUST_CD_MATRICULA_EXCLUSAO');

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds ($acao = null) {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id ['index'] = $cache->retornaID_Listagem('orcamento', $negocio);

        // Id para combo
        $id ['combo'] = $cache->retornaID_Combo('orcamento', $negocio);

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [$acao] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @return  array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCombo () {

        // Retorna instrução sql para listagem dos dados
        $sql = "
            SELECT
                JUST_ID_JUSTIFICATIVA,
                JUST_DS_TITULO
            FROM CEO_TB_JUST_JUSTIFICATIVA
            WHERE JUST_IC_SITUACAO = 1
                    ";

        // Retorna default adapter de banco
        $banco = Zend_Db_Table::getDefaultAdapter();

        // Retorna todos os registros e campos da instrução sql
        $dados = $banco->fetchPairs($sql);

        // Devolve os dados
        return $dados;
    }

    /**
     * Insere justificativa na projeção orçamentária quando uma nova solicitação de movimentação de crédito é realizada.
     *
     * @return  array
     * @author Victor Eduardo Barreto
     */
    public function incluirJustificativa ($dados) {

        try {

            $banco = Zend_Db_Table::getDefaultAdapter();
            $banco->beginTransaction();

            $sqlMov = "SELECT TMOV_DS_TIPO_MOVIMENTACAO FROM CEO.CEO_TB_TMOV_TIPO_MOVIMENTACAO "
                . "WHERE TMOV_ID_TIPO_MOVIMENTACAO = " . $dados['MOVC_ID_TIPO_MOVIMENTACAO'];

            $dsMovimentacao = $banco->fetchRow($sqlMov);

            $sqlOrigem = "INSERT INTO CEO.CEO_TB_PRJJ_JUSTIF_PROJECAO "
                . "(PRJJ_NR_DESPESA, PRJJ_DH_JUSTIFICATIVA, PRJJ_DS_JUSTIFICATIVA, PRJJ_IC_ORIGEM) "
                . "VALUES (" . $dados['MOVC_NR_DESPESA_ORIGEM'] . ",SYSDATE, '"
                . "TIPO: " . $dsMovimentacao['TMOV_DS_TIPO_MOVIMENTACAO'] . " DESPESA DE DESTINO: " . $dados['MOVC_NR_DESPESA_DESTINO']
                . " - " . $dados['DESTINO']['DS_DESPESA'] . " VALOR: " . $dados['MOVC_VL_MOVIMENTACAO'] . " "
                . " MOTIVO: " . $dados['MOVC_DS_JUSTIF_SOLICITACAO'] . "', 0)";

            $banco->query($sqlOrigem);

            $sqlDestino = "INSERT INTO CEO.CEO_TB_PRJJ_JUSTIF_PROJECAO "
                . "(PRJJ_NR_DESPESA, PRJJ_DH_JUSTIFICATIVA, PRJJ_DS_JUSTIFICATIVA, PRJJ_IC_ORIGEM) "
                . "VALUES (" . $dados['MOVC_NR_DESPESA_DESTINO'] . ",SYSDATE, '"
                . "TIPO: " . $dsMovimentacao['TMOV_DS_TIPO_MOVIMENTACAO'] . " DESPESA DE DESTINO: " . $dados['MOVC_NR_DESPESA_DESTINO']
                . " - " . $dados['DESTINO']['DS_DESPESA'] . " VALOR: " . $dados['MOVC_VL_MOVIMENTACAO'] . " "
                . " MOTIVO: " . $dados['MOVC_DS_JUSTIF_SOLICITACAO'] . "', 0)";

            $banco->query($sqlDestino);

            return $banco->commit();
        } catch (Exception $ex) {

            $banco->rollBack();
            throw $ex;
        }
    }

}
