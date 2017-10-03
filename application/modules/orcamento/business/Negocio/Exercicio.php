<?php

/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre exercicio
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Exercicio
 * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Exercicio extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @var Orcamento_Model_DbTable_Exercicio
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init () {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Exercicio();

        // Define a negocio
        $this->_negocio = 'exercicio';
    }

    /**
     * Inclui o exercicio e a fase ao mesmo tempo
     *
     * @var $dados array de dados
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirExercicioFase ($dados) {
        // Definições do exercicio
        $sqlExercicio = "
                INSERT INTO CEO_TB_ANOE_ANO_EXERCICIO ( 
                            ANOE_AA_ANO, 
                            ANOE_CD_MATRICULA_INCLUSAO, 
                            ANOE_DS_OBSERVACAO )
                VALUES ( 
                            '{$dados['ANOE_AA_ANO']}', 
                            '{$dados['ANOE_CD_MATRICULA_INCLUSAO']}', 
                            '{$dados['ANOE_DS_OBSERVACAO']}'  )
                ";

        // Definições da fase
        $sqlFase = "
                INSERT INTO CEO_TB_FANE_FASE_ANO_EXERCICIO ( 
                            FANE_ID_FASE_ANO_EXERCICIO, 
                            FANE_NR_ANO, 
                            FANE_ID_FASE_EXERCICIO )
                VALUES ( 
                            CEO_SQ_FANE.NEXTVAL,
                            '{$dados['ANOE_AA_ANO']}', 
                            '" . Trf1_Orcamento_Definicoes::FASE_EXERCICIO . "'  )
                ";
        // Array de de sqls para a função executa query                            
        $sqls = array($sqlExercicio, $sqlFase);

        return $this->executaQuery($sqls);
    }

    /**
     * Edita o exercicio e a fase ao mesmo tempo
     *
     * @var $dados array de dados
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarExercicioFase ($dados) {
        // Atualização em exercicio
        $sqlExercicio = "
            UPDATE CEO_TB_ANOE_ANO_EXERCICIO SET 
                ANOE_CD_MATRICULA_INCLUSAO = '{$dados['ANOE_CD_MATRICULA_INCLUSAO']}', 
                ANOE_DS_OBSERVACAO = '{$dados['ANOE_DS_OBSERVACAO']}'
                WHERE ANOE_AA_ANO = '{$dados['ANOE_AA_ANO']}'
                ";

        // Atualizaçao em fase
        $sqlFase = "
                UPDATE CEO_TB_FANE_FASE_ANO_EXERCICIO SET
                    FANE_ID_FASE_EXERCICIO = '{$dados['FANE_ID_FASE_EXERCICIO']}'
                WHERE FANE_NR_ANO = '{$dados['ANOE_AA_ANO']}'
                ";

        // Array de de sqls para a função executa query
        $sqls = array($sqlExercicio, $sqlFase);

        return $this->executaQuery($sqls);
    }

    /**
     * Função responsável por coíar a valores de despesa com demandante 3 para o 4.
     */
    public function copiaValores ($dados) {

        try {

            $banco = Zend_Db_Table::getDefaultAdapter();
            $banco->beginTransaction();

            // recupera valores e ajuste ao limite.
            $SQVLD3 = "SELECT VLDE_NR_DESPESA, VLDE_VL_DESPESA "
                . " FROM CEO_TB_VLDE_VALOR_DESPESA "
                . " JOIN CEO_TB_DESP_DESPESA on (CEO_TB_DESP_DESPESA.DESP_NR_DESPESA = CEO_TB_VLDE_VALOR_DESPESA.VLDE_NR_DESPESA)"
                . " WHERE DESP_AA_DESPESA = " . $dados['ANOE_AA_ANO']
                . " AND VLDE_CD_DEMANDANTE  = " . Orcamento_Business_Dados::DEMANDANTE_AJUSTE_LIMITE;

            $VLD3 = $banco->fetchAll($SQVLD3);

            // remove valores de orçamento aprovado.
            $SQLVLD4 = "SELECT VLDE_NR_DESPESA "
                . " FROM CEO_TB_VLDE_VALOR_DESPESA "
                . " JOIN CEO_TB_DESP_DESPESA on (CEO_TB_DESP_DESPESA.DESP_NR_DESPESA = CEO_TB_VLDE_VALOR_DESPESA.VLDE_NR_DESPESA)"
                . " WHERE DESP_AA_DESPESA = " . $dados['ANOE_AA_ANO']
                . " AND VLDE_CD_DEMANDANTE  = " . Orcamento_Business_Dados::DEMANDANTE_DIPOR_APROVADO;

            $VLD4 = $banco->fetchAll($SQLVLD4);

            // deleta valores de orçamento aprovado.
            foreach ($VLD4 as $D4) {

                $SQLDELD4 = "DELETE FROM CEO_TB_VLDE_VALOR_DESPESA "
                    . " WHERE VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_DIPOR_APROVADO
                    . " AND VLDE_NR_DESPESA =" . $D4['VLDE_NR_DESPESA'];
                $banco->query($SQLDELD4);
            }

            // Insere valores de orçamento aprovado.
            foreach ($VLD3 as $D3) {

                $SQLINVLD4 = "INSERT INTO CEO_TB_VLDE_VALOR_DESPESA "
                    . "(VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, VLDE_VL_DESPESA, VLDE_DH_DESPESA) "
                    . "VALUES (?,?,?, SYSDATE)";

                $STMTVLD4 = $banco->prepare($SQLINVLD4);

                $STMTVLD4->execute(array(
                    $D3['VLDE_NR_DESPESA'],
                    Orcamento_Business_Dados::DEMANDANTE_DIPOR_APROVADO,
                    $D3['VLDE_VL_DESPESA'],
                ));
            }

            $banco->commit();
        } catch (Exception $ex) {

            $banco->rollBack();
            throw new $ex;
        }
    }

    /**
     * Verifica se o registro ja existe
     *
     * @var $exercicio - Ano exercicio
     * @var $fase - Fase do exercicio
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function registroExistente ($exercicio, $fase) {
        return $this->_model->retornaRegistroExistente($exercicio, $fase);
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao Nome ada ação (action) em questão
     * @return string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCampos ($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
        ANOE_AA_ANO,
        ANOE_DS_OBSERVACAO,
        FASE.FASE_NM_FASE_EXERCICIO
        ";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['index'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
        ANOE_AA_ANO                  AS \"Ano Exercício\",
        ANOE_DS_OBSERVACAO           AS \"Descrição\",
        ANOE_CD_MATRICULA_INCLUSAO   AS \"Data de Inclusão\",
        ANOE_CD_MATRICULA_EXCLUSAO   AS \"Data de Exclusão\",
        ANOE_DH_EXCLUSAO_LOGICA      AS \"Exclusão Lógica\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "ANOE_AA_ANO, ";
        $campos ['excluir'] .= $campos ['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos ['restaurar'] = $campos ['excluir'];

        // Campos para a serem apresentados num combo
        $campos ['combo'] = "
        ANOE_AA_ANO,
        ANOE_DS_OBSERVACAO
                               ";
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
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {
        // Condição para ação editar
        $restricao ['editar'] = " AND ANOE_AA_ANO IN ( $chaves ) ";

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['editar'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['editar'];

        // Condição para montagem do combo
        $restricao ['combo'] = " ";

        return $restricao [$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais exercicios
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica ($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $exercicios = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_ANOE_ANO_EXERCICIO
SET
    ANOE_CD_MATRICULA_EXCLUSAO          = '$matricula',
    ANOE_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    ANOE_AA_ANO                      IN ( $exercicios ) AND
    ANOE_DH_EXCLUSAO_LOGICA          IS Null
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
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ($chaves) {
        // Trata a chave primária (ou composta)
        $exercicios = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_ANOE_ANO_EXERCICIO
SET
    ANOE_CD_MATRICULA_EXCLUSAO          = Null,
    ANOE_DH_EXCLUSAO_LOGICA             = Null
WHERE
    ANOE_AA_ANO IN ( $exercicios ) 
    AND ANOE_DH_EXCLUSAO_LOGICA         IS NOT Null
                ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCacheIds ($acao = null) {
        // Busca perfil
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil();
        $perfil = $perfilFull ['perfil'];

        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $cacheIndex = $cache->retornaID_Listagem('orcamento', $negocio);
        $cacheCombo = $cache->retornaID_Combo('orcamento', $negocio);

        $id ['index'] = $cacheIndex;
        $id ['index'] .= "_$perfil";

        // Id para combo
        $id ['combo'] = $cacheCombo;
        $id ['combo'] .= "_$perfil";

        $i = 0;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_CONSULTA;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_DIEFI;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_DIPOR;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_SECCIONAL;
        $id [$i++] = $cacheIndex . '_' . Orcamento_Business_Dados::PERMISSAO_SECRETARIA;

        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_CONSULTA;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_DIEFI;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_DIPOR;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_SECCIONAL;
        $id [$i++] = $cacheCombo . '_' . Orcamento_Business_Dados::PERMISSAO_SECRETARIA;

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [$acao] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param	int $exercicio Ano Exercicio
     * @param   int $status Status do Ano Exercicio
     * @return	array
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaRegistro ($exercicio, $fase = null) {
        $condStatus = "";
        if ($condStatus) {
            $condStatus = " AND E.FASE_ID_FASE_EXERCICIO = $fase";
        }

        $sql = "
SELECT DISTINCT
    A.ANOE_AA_ANO,
    A.ANOE_DS_OBSERVACAO,
    F.FANE_ID_FASE_EXERCICIO
FROM 
CEO_TB_ANOE_ANO_EXERCICIO A
INNER JOIN 
    CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO F
        ON F.FANE_NR_ANO = A.ANOE_AA_ANO
INNER JOIN 
    CEO.CEO_TB_FASE_FASE_EXERCICIO E
        ON E.FASE_ID_FASE_EXERCICIO = F.FANE_ID_FASE_EXERCICIO
WHERE A.ANOE_DH_EXCLUSAO_LOGICA IS NULL
    AND A.ANOE_AA_ANO = $exercicio
        
        $condStatus";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Exclui o cache negocial, basicamente a listagem e combo
     *
     * @param string $controle
     *        Nome da controle
     * @param array $cacheIds
     *        Array contendo todos os ids a serem excluídos
     * @throws Zend_Exception
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluiCaches () {
        try {
            // Instancia o cache
            $cache = new Trf1_Cache ();

            $cacheIds = $this->retornaCacheIds();

            if ($cacheIds) {
                // Remove os caches, conforme ids informados
                foreach ($cacheIds as $cacheId) {
                    // Exclui o cache conform id da listagem
                    $cache->excluirCache($cacheId);
                }
            }

            // Retorna uma ou mais tags dos caches
            $cacheTags = $this->retornaCacheTags();

            if ($cacheTags) {
                // Remove os caches, conforme ids informados
                foreach ($cacheIds as $cacheId) {
                    // Exclui o cache conform id da listagem
                    $cache->excluirCache($cacheId);
                }
            }
        } catch (Exception $e) {
            // Gera o erro
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * Retorna um registro excluido lógicamente
     *
     * @param string $ano
     *        Ano exercicio a ser consultado
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaExercicioExcluido ($ano) {
        $sql = "
SELECT 
    A.ANOE_AA_ANO, 
    A.ANOE_CD_MATRICULA_INCLUSAO,
    A.ANOE_DS_OBSERVACAO, 
    A.ANOE_CD_MATRICULA_EXCLUSAO,
    A.ANOE_DH_EXCLUSAO_LOGICA
FROM CEO.CEO_TB_ANOE_ANO_EXERCICIO a
WHERE anoe_aa_ano = '$ano'
    AND anoe_cd_matricula_exclusao IS NOT NULL    
    AND anoe_dh_exclusao_logica IS NOT NULL
            ";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchRow($sql);
    }

    /**
     * Retorna um registro excluido lógicamente
     *
     * @param string $ano
     *        Ano exercicio a ser consultado
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaProximoAnoExercicio () {
        $sql = "
SELECT MAX (A.ANOE_AA_ANO + 1) as ANOE_AA_ANO
FROM CEO.CEO_TB_ANOE_ANO_EXERCICIO a
-- WHERE A.ANOE_CD_MATRICULA_EXCLUSAO IS NOT NULL    
-- AND A.ANOE_DH_EXCLUSAO_LOGICA IS NOT NULL
            ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna um registro com o ano e status informado
     *
     * @param string $ano
     *        Ano exercicio a ser consultado
     * @param int $status
     *        Status do exercicio
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaAnoExercicioComStatus ($ano, $status) {
        $sql = "
SELECT 
    A.ANOE_AA_ANO,
    E.FASE_NM_FASE_EXERCICIO
FROM CEO.CEO_TB_ANOE_ANO_EXERCICIO A
INNER JOIN CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO F
    ON A.ANOE_AA_ANO = F.FANE_NR_ANO
INNER JOIN CEO.CEO_TB_FASE_FASE_EXERCICIO E
    ON F.FANE_ID_FASE_EXERCICIO = e.FASE_ID_FASE_EXERCICIO
WHERE A.ANOE_AA_ANO = '$ano'
    AND F.FANE_ID_FASE_EXERCICIO = $status
            ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Realiza a exclusão fisica de um exercicio
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function exclusaoFisica ($exercicio) {
        // Exclui um ou mais registros
        $sql = "
DELETE FROM CEO_TB_ANOE_ANO_EXERCICIO
WHERE ANOE_AA_ANO = $exercicio";
        // Devolve a instrução sql para exclusão lógica
        return $this->executaQuery($sql);
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     * 
     * @return	array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCombo () {
        // Busca perfil
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil();
        $perfil = $perfilFull ['perfil'];

        // Instancia cache
        $cache = new Trf1_Cache ();

        // Retorna id de cache de listagem
        $cacheId = $this->retornaCacheIds('combo');

        // Verifica existência dos dados em cache
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            $condicao = "";
            if ($perfil != Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR &&
                $perfil != Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO) {
                $condicao = " AND FASE.FANE_ID_FASE_EXERCICIO <> " . Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
            }

            $negocioFase = new Orcamento_Business_Negocio_FaseAnoExercicio ();
            $sqlFase = $negocioFase->retornaSqlFaseExercicio();

            // Retorna instrução sql para listagem dos dados
            $sql = "
SELECT
    ANOE.ANOE_AA_ANO,
    ANOE.ANOE_AA_ANO || ' - ' || FASE.FASE_NM_FASE_EXERCICIO || ' - ' || ANOE.ANOE_DS_OBSERVACAO AS DS_DESCRICAO
FROM
    CEO_TB_ANOE_ANO_EXERCICIO ANOE
Left JOIN
    (
    " . $sqlFase . " 
    ) FASE ON
        FASE.FANE_NR_ANO = ANOE.ANOE_AA_ANO
WHERE
    ANOE_DH_EXCLUSAO_LOGICA IS Null
    $condicao
                    ";

            // Zend_Debug::dump ( $sql );
            // exit;
            // Retorna default adapter de banco
            $banco = Zend_Db_Table::getDefaultAdapter();

            // Retorna todos os registros e campos da instrução sql
            $dados = $banco->fetchPairs($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        // Devolve os dados
        return $dados;
    }

    public function retornaExercicios()
    {
        $sql = "SELECT ANOE_AA_ANO FROM CEO_TB_ANOE_ANO_EXERCICIO ORDER BY ANOE_AA_ANO DESC";
        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }
    
}
