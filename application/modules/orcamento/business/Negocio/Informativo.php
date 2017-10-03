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
 * Contém as regras negociais sobre informativo
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_informativo
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Informativo extends Orcamento_Business_Negocio_Base {

    /**
     * Instancia as variaveis na inicialização
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Info();

        // Define a negocio
        $this->_negocio = 'informativo';
    }

    /**
     * Configura os dados antes da inclusão
     *
     * @param array $dados
     *        Dados a serem configurados
     * @return array $resposta
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluir($dados) {
        // codigo do informativo
        $codigoInfo = parent::incluir($dados);

        if (is_array($dados["responsaveis"])) {

            foreach ($dados['responsaveis'] as $resp) {

                $dadosResponsavel = array(
                    'INFR_CD_INFORMATIVO' => $codigoInfo["codigo"],
                    'INFR_CD_RESPONSAVEL' => $resp,
                );

                $negocioResposta = new Orcamento_Business_Negocio_InformativoResp();
                $resposta = $negocioResposta->incluir($dadosResponsavel);

                unset($dadosResponsavel);
            }
        } else {
            $dadosResponsavel = array(
                'INFR_CD_INFORMATIVO' => $codigoInfo["codigo"],
                'INFR_CD_RESPONSAVEL' => $dados["INFR_CD_RESPONSAVEL"],
            );

            $negocioResposta = new Orcamento_Business_Negocio_InformativoResp();
            $resposta = $negocioResposta->incluir($dadosResponsavel);
        }

        return $resposta;
    }

    /**
     * Configura os dados antes da edição
     *
     * @param array $dados
     *        Dados a serem configurados
     * @return array $resposta
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editar($dados) {
        $codigoInfo = parent::editar($dados);

        // remove os responsaveis atuais
        $modelInfr = new Orcamento_Model_DbTable_Infr();
        $del = $modelInfr->delete("INFR_CD_INFORMATIVO =" . $codigoInfo["codigo"]);

        if (is_array($dados["responsaveis"])) {

            foreach ($dados['responsaveis'] as $resp) {

                $dadosResponsavel = array(
                    'INFR_CD_INFORMATIVO' => $codigoInfo["codigo"],
                    'INFR_CD_RESPONSAVEL' => $resp,
                );

                $negocioResposta = new Orcamento_Business_Negocio_InformativoResp();
                $resposta = $negocioResposta->incluir($dadosResponsavel);

                unset($dadosResponsavel);
            }
        } else {
            $dadosResponsavel = array(
                'INFR_CD_INFORMATIVO' => $codigoInfo["codigo"],
                'INFR_CD_RESPONSAVEL' => $dados["INFR_CD_RESPONSAVEL"],
            );

            $negocioResposta = new Orcamento_Business_Negocio_InformativoResp();
            $resposta = $negocioResposta->incluir($dadosResponsavel);
        }

        return $resposta;
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
    public function transformaFormulario($formulario, $acao) {
        if ($acao == Orcamento_Business_Dados::ACTION_INCLUIR) {
            $formulario->removeElement('INFO_NR_INFORMATIVO');
        }

        return $formulario;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid() {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'INFO_NR_INFORMATIVO' => array('title' => 'Código',
                'abbr' => ''),
            'INFO_TX_TITULO_INFORMATIVO' => array('title' => 'Titulo',
                'abbr' => ''),
            'INFO_DS_INFORMATIVO' => array('title' => 'Descrição',
                'abbr' => ''),
            'INFO_DT_INICIO' => array('title' => 'Data de Inicio',
                'abbr' => ''),
            'INFO_DT_TERMINO' => array('title' => 'Data de Termino',
                'abbr' => ''),
            'INFO_STATUS' => array(
                'title' => 'Status do informativo',
                'abbr' => 'Informa se o registro foi ou não excluído'));

        // Combina as opções num array
        $opcoes['detalhes'] = $detalhes;
        $opcoes['controle'] = $this->_negocio;
        $opcoes['ocultos'] = array( /* 'INFO_NR_INFORMATIVO', */'INFM_CD_MATRICULA_LEITURA', 'UNGE_SG_SECAO');

        // Devolve o array de opções
        return $opcoes;
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
    public function retornaCampos($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos['index'] = "
                                INFO_NR_INFORMATIVO,
                                INFO_TX_TITULO_INFORMATIVO,
                                INFO_DT_INICIO,
                                INFO_DT_TERMINO,
                                CASE
                                    WHEN INFO_DH_EXCLUSAO_LOGICA IS NULL THEN 'Ativo'
                                    ELSE 'Inativo'
                                END AS INFO_STATUS
                                ";

        // Campos para a serem apresentados na editarAction
        $campos['editar'] = $campos['todos'];

        // Campos para a serem apresentados na detalheAction
        $campos['detalhe'] = "
            INFO_NR_INFORMATIVO         AS \"Codigo\",
            INFO_TX_TITULO_INFORMATIVO  AS \"Título\",
            INFO_DS_INFORMATIVO         AS \"Descrição\",
            INFO_DT_INICIO              AS \"Inicio\",
            INFO_DT_TERMINO             AS \"Término\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos['excluir'] = "INFO_NR_INFORMATIVO, ";
        $campos['excluir'] .= $campos['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos['restaurar'] = $campos['excluir'];

        // Campos para a serem apresentados num combo
        $campos['combo'] = "";

        // Devolve os campos, conforme ação
        return $campos[$acao];
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
    public function retornaRestricoes($acao = 'todos', $chaves = null) {

        // Verifica os se esta na tela de excluidos
        $filtroIndex = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        if ($filtroIndex == 'excluidos') {
            $filtro = 'AND INFO_CD_MATRICULA_EXCLUSAO IS Not Null';
        } else {
            $filtro = 'AND INFO_CD_MATRICULA_EXCLUSAO IS Null';
        }

        // Condição para index
        $restricao['index'] = $filtro;

        // Condição para excluidos
        $restricao['excluidos'] = $filtro;

        // Condição para ação editar
        $restricao['detalhe'] = " AND INFO_NR_INFORMATIVO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao['editar'] = $restricao['detalhe'];

        // Condição para ação excluir
        $restricao['excluir'] = $restricao['detalhe'];

        // Condição para ação restaurar
        $restricao['restaurar'] = $restricao['detalhe'];

        // Condição para montagem do combo
        $restricao['combo'] = " INFO_CD_MATRICULA_EXCLUSAO IS Null ";

        return $restricao[$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais informativos
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlExclusaoLogica($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $informativos = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
                    UPDATE
                        CEO_TB_INFO_INFORMATIVO
                    SET
                        INFO_CD_MATRICULA_EXCLUSAO  = '$matricula',
                        INFO_DH_EXCLUSAO_LOGICA         = SYSDATE
                    WHERE
                        INFO_NR_INFORMATIVO                      IN ( $informativos )
                        AND INFO_DH_EXCLUSAO_LOGICA   IS Null
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
    public function retornaSqlRestauracaoLogica($chaves) {
        // Trata a chave primária (ou composta)
        $codigos = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
                    UPDATE
                        CEO_TB_INFO_INFORMATIVO
                    SET
                        INFO_CD_MATRICULA_EXCLUSAO  = NULL,
                        INFO_DH_EXCLUSAO_LOGICA         = NULL
                    WHERE
                        INFO_NR_INFORMATIVO                      IN ( $codigos )
                        AND INFO_DH_EXCLUSAO_LOGICA   IS NOT NULL
                ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds($acao = null) {

        // Instancia o cache
        $cache = new Trf1_Cache();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id['index'] = $cache->retornaID_Listagem('orcamento', $negocio);

        // Id para combo
        $id['combo'] = $cache->retornaID_Combo('orcamento', $negocio);

        // Determina qual valor será retornado
        $retorno = ($acao != null ? $id[$acao] : $id);

        // Devolve o id, conforme $acao informada
        return $retorno;
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
    public function excluiCaches() {
        try {
            // Instancia o cache
            $cache = new Trf1_Cache();

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
     * Retorna listagem de informativos
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaInformativos() {
        $sql = $this->baseInformativos();

        $banco = Zend_Db_Table::getDefaultAdapter();

        $informativos = $banco->fetchAll($sql);

        return $informativos;
    }

    /**
     * Retorna quantidade de registros de informativos
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaQtdeInformativos() {
        $sql = $this->baseInformativos();

        $qtde = $this->retornaQtdeRegistros($sql);

        return $qtde;
    }

    /**
     * Retorna um sql de informativos base
     *
     * @return string sql
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function baseInformativos() {
        // Instancia a sessão
        $sessao = new Orcamento_Business_Sessao();

        // Responsável do usuário logado
        $perfilFull = $sessao->retornaPerfil();
        $resp = $perfilFull['responsavel'];
        $ug = $perfilFull['ug'];

        if ($resp != 'todos') {
            // Define condição
            $condicao = " SG_FAMILIA_RESPONSAVEL = '$resp' AND
                          UNGE_SG_SECAO = '$ug' AND
                        ";
        } else {
            // Define condição
            //$condicao = " LOTA_SIGLA_SECAO = '$ug' ";
            $condicao = "";
        }

        // Retorna matrícula do usuário logado
        $matricula = strtoupper($sessao->retornaMatricula());

        // Verifica se há informativos vigentes conforme condicao
        $sql = $this->sqlInformativos($condicao, $matricula);

        return $sql;
    }

    /**
     * Query de informativos
     *
     * @return string sql
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function sqlInformativos($condicao, $matricula) {
        // Sql de informativos vigentes
        $sqlVigentes = $this->sqlInformativosVigentes($matricula);

        $sql = "
        SELECT DISTINCT
            INFM_CD_INFORMATIVO_MATRICULA,
            INFO_NR_INFORMATIVO,
            INFO_TX_TITULO_INFORMATIVO,
            INFO_DS_INFORMATIVO,
            --INFR_CD_RESPONSAVEL,
            --INFM_CD_MATRICULA_LEITURA,
         --   RESP_DS_SECAO,
         --   UNGE_SG_SECAO,
            --SG_FAMILIA_RESPONSAVEL,
            HOJE,
            INFO_DT_INICIO,
            INFO_DT_TERMINO,
            INFO_CD_MATRICULA_EXCLUSAO
        FROM (
                $sqlVigentes
             )
        WHERE
            $condicao
            INFO_CD_MATRICULA_EXCLUSAO IS NULL
                ";

        return $sql;
    }

    /**
     * Retorna somente informativos vigentes
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function sqlInformativosVigentes($matricula) {
        $sql = "
           SELECT
            INFM.INFM_CD_INFORMATIVO_MATRICULA,
            INFO.INFO_NR_INFORMATIVO,
            INFO.INFO_TX_TITULO_INFORMATIVO,
            INFO.INFO_DS_INFORMATIVO,
            INFR.INFR_CD_RESPONSAVEL,
            INFM.INFM_CD_MATRICULA_LEITURA,
            RESP.RESP_DS_SECAO,
            UNGE.UNGE_SG_SECAO,
            RH_SIGLAS_FAMILIA_CENTR_LOTA (
                RHCL.LOTA_SIGLA_SECAO,
                RHCL.LOTA_COD_LOTACAO
            )                               AS SG_FAMILIA_RESPONSAVEL,
            TO_DATE(SYSDATE) AS HOJE,
            INFO_DT_INICIO,
            INFO_DT_TERMINO,
            INFO.INFO_CD_MATRICULA_EXCLUSAO
        FROM
            CEO_TB_INFO_INFORMATIVO INFO
        Left JOIN
            CEO_TB_INFR_INFORMATIVO_RESP INFR ON
                INFR.INFR_CD_INFORMATIVO = INFO.INFO_NR_INFORMATIVO
        Left JOIN
            CEO_TB_INFM_INFORMATIVO_MATRI INFM ON
                INFM.INFM_CD_INFORMATIVO = INFO.INFO_NR_INFORMATIVO
        Left JOIN
            CEO_TB_RESP_RESPONSAVEL RESP ON
                RESP.RESP_CD_RESPONSAVEL = INFR.INFR_CD_RESPONSAVEL
        Left JOIN
            CEO_TB_UNGE_UNIDADE_GESTORA  UNGE ON
                UNGE.UNGE_CD_UG = RESP.UNGE_CD_UG
        Left JOIN
            RH_CENTRAL_LOTACAO RHCL ON
                RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND
                RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
        WHERE
            INFO.INFO_DH_EXCLUSAO_LOGICA IS NULL AND
            TO_DATE(SYSDATE) BETWEEN INFO_DT_INICIO AND INFO_DT_TERMINO AND
            INFO.INFO_NR_INFORMATIVO NOT IN (
            	SELECT INFM_CD_INFORMATIVO FROM CEO_TB_INFM_INFORMATIVO_MATRI
                WHERE INFM_CD_MATRICULA_LEITURA = '$matricula' AND INFM_CD_INFORMATIVO IS NOT NULL)
                ";

        return $sql;
    }

    /**
     * Retorna informativo por responsavel
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaInformativo($codInfo) {
        $sql = "
            SELECT DISTINCT
                *
            FROM CEO_TB_INFO_INFORMATIVO
                INNER JOIN
                    CEO_TB_INFR_INFORMATIVO_RESP
                        ON INFO_NR_INFORMATIVO = INFR_CD_INFORMATIVO
            WHERE
                INFO_NR_INFORMATIVO =" . $codInfo;

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    public function retornaResponsaveis($codinfo) {
        $sql = "
        SELECT
           INFR_CD_INFORMATIVO,
           INFR_CD_RESPONSAVEL
        FROM CEO_TB_INFR_INFORMATIVO_RESP INFR
            INNER JOIN CEO_TB_INFO_INFORMATIVO INFO
                ON INFO.INFO_NR_INFORMATIVO = INFR.INFR_CD_INFORMATIVO
        WHERE INFO_NR_INFORMATIVO =" . $codinfo;

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna listagem de informativos
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaInformativosNaolidos($perfil) {

        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        $sql = "
        SELECT DISTINCT
            INFM_CD_INFORMATIVO_MATRICULA,
            INFO_NR_INFORMATIVO,
            INFO_TX_TITULO_INFORMATIVO,
            INFO_DS_INFORMATIVO,
            -- INFR_CD_RESPONSAVEL,
            -- INFM_CD_MATRICULA_LEITURA,
            RESP_DS_SECAO,
            -- SG_FAMILIA_RESPONSAVEL,
            HOJE,
            INFO_DT_INICIO,
            INFO_DT_TERMINO,
            CASE WHEN INFM_CD_MATRICULA_LEITURA IS NULL
                THEN 'Não Aceito' ELSE 'Aceito'
            END AS STATUS
        FROM (

        SELECT
            INFM.INFM_CD_INFORMATIVO_MATRICULA,
            INFO.INFO_NR_INFORMATIVO,
            INFO.INFO_TX_TITULO_INFORMATIVO,
            INFO.INFO_DS_INFORMATIVO,
            INFR.INFR_CD_RESPONSAVEL,
            INFM.INFM_CD_MATRICULA_LEITURA,
            RESP.RESP_DS_SECAO,
            RH_SIGLAS_FAMILIA_CENTR_LOTA (
                RHCL.LOTA_SIGLA_SECAO,
                RHCL.LOTA_COD_LOTACAO
            )                               AS SG_FAMILIA_RESPONSAVEL,
            TO_DATE(SYSDATE) AS HOJE,
            INFO_DT_INICIO,
            INFO_DT_TERMINO
        FROM
            CEO_TB_INFO_INFORMATIVO INFO
        Left JOIN
            CEO_TB_INFR_INFORMATIVO_RESP INFR ON
                INFR.INFR_CD_INFORMATIVO = INFO.INFO_NR_INFORMATIVO
        Left JOIN
            CEO_TB_INFM_INFORMATIVO_MATRI INFM ON
                --INFM.INFM_CD_INFORMATIVO_RESP = INFR.INFR_CD_RESPONSAVEL
                INFM.INFM_CD_INFORMATIVO = INFO.INFO_NR_INFORMATIVO
        Left JOIN
            CEO_TB_RESP_RESPONSAVEL RESP ON
                RESP.RESP_CD_RESPONSAVEL = INFR.INFR_CD_RESPONSAVEL
        Left JOIN
            RH_CENTRAL_LOTACAO RHCL ON
                RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND
                RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
        WHERE
            INFO.INFO_DH_EXCLUSAO_LOGICA IS NULL AND
            TO_DATE(SYSDATE) BETWEEN INFO_DT_INICIO AND INFO_DT_TERMINO

             )
        WHERE
            SG_FAMILIA_RESPONSAVEL = '$perfil'
            AND INFM_CD_MATRICULA_LEITURA IS NULL

            $condicaoResponsaveis

            ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

}
