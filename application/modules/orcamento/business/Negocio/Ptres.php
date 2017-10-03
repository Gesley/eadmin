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
 * Contém as regras negociais sobre PTRES
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Ptres
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Ptres extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init () {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Ptrs ();

        // Define a negocio
        $this->_negocio = 'ptres';
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampos ($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
    PTRS_AA_EXERCICIO,    
    PTRS_CD_PT_RESUMIDO,
    PTRS_SG_PT_RESUMIDO,
    PTRS_DS_PT_RESUMIDO,
    PTRS_CD_PT_COMPLETO,
    PTRS_CD_UNID_ORCAMENTARIA,
CASE WHEN LENGTH(PTRS_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS PTRS_STATUS
                                ";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['index'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
PTRS_AA_EXERCICIO               AS \"Ano\",        
PTRS_CD_PT_RESUMIDO             AS \"Código do PTRES\",
PTRS_SG_PT_RESUMIDO             AS \"Sigla\",
PTRS_DS_PT_RESUMIDO             AS \"Descrição\",
PTRS_CD_PT_COMPLETO             AS \"Programa de trabalho completo\",
PTRS_DS_PROGRAMA_ACAO             AS \"Descrição do Programa / Ação\",
PTRS_CD_UNID_ORCAMENTARIA       AS \"Código da unidade orçamentária\",
CASE WHEN LENGTH(PTRS_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS \"Status do registro\",
PTRS_CD_MATRICULA_EXCLUSAO      AS \"Excluído por\",
PTRS_DH_EXCLUSAO_LOGICA         AS \"Data da exclusão\"
                                ";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "PTRS_CD_PT_RESUMIDO, ";
        $campos ['excluir'] .= $campos ['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos ['restaurar'] = $campos ['excluir'];

        // Campos para a serem apresentados num combo
        $campos ['combo'] = "
PTRS_CD_PT_RESUMIDO,
PTRS_SG_PT_RESUMIDO || ' - ' || PTRS_DS_PT_RESUMIDO
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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {

        // Verifica os se esta na tela de excluidos
        $filtroIndex = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        if($filtroIndex == 'excluidos'){
            $filtro = 'AND PTRS_CD_MATRICULA_EXCLUSAO IS Not Null';
        }else{
            $filtro = 'AND PTRS_CD_MATRICULA_EXCLUSAO IS Null';
        }
       

        // Sem condição...
        $restricao ['todos'] = " AND AND PTRS_CD_MATRICULA_EXCLUSAO IS Null ";

        // Condição para index
        $restricao ['index'] = $filtro;

        // Condição para excluidos
        $restricao ['excluidos'] = $filtro;

        // Condição para ação editar
        $restricao ['detalhe'] = " AND PTRS_CD_PT_RESUMIDO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

        // Condição para montagem do combo
        $restricao ['combo'] = " PTRS_DH_EXCLUSAO_LOGICA IS Null ";

        return $restricao [$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais esferas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlExclusaoLogica ($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $ptres = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PTRS_PROGRAMA_TRABALHO
SET
    PTRS_CD_MATRICULA_EXCLUSAO          = '$matricula',
    PTRS_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    PTRS_CD_PT_RESUMIDO                 IN ( $ptres ) AND
    PTRS_DH_EXCLUSAO_LOGICA             IS Null
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
        $ptres = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PTRS_PROGRAMA_TRABALHO
SET
    PTRS_CD_MATRICULA_EXCLUSAO          = Null,
    PTRS_DH_EXCLUSAO_LOGICA             = Null
WHERE
    PTRS_CD_PT_RESUMIDO                 IN ( $ptres ) AND
    PTRS_DH_EXCLUSAO_LOGICA             IS NOT Null
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
            'PTRS_AA_EXERCICIO' => array('title' => 'Ano',
                'abbr' => 'Ano'),
            'PTRS_CD_PT_RESUMIDO' => array('title' => 'PTRES',
                'abbr' => 'Código do Programa de Trabalho Resumido'),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla',
                'abbr' => 'Sigla do PTRES'),
            'PTRS_DS_PT_RESUMIDO' => array('title' => 'Descrição',
                'abbr' => 'Descrição do PTRES'),
            'PTRS_CD_PT_COMPLETO' => array('title' => 'PT',
                'abbr' => 'Código do Programa de Trabalho',
                'format' => 'Ptcompleto'),
            'PTRS_DS_PROGRAMA_ACAO' => array('title' => 'Descrição do Programa / Ação',
                'abbr' => 'Descrição do Programa / Ação'),
            'PTRS_CD_UNID_ORCAMENTARIA' => array('title' => 'UO',
                'abbr' => 'Código da Unidade Orçamentária'),
            'PTRS_STATUS' => array('title' => 'Status',
                'abbr' => 'Informa se o registro foi ou não excluído'));

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array('CAMPO_NAO_EXISTENTE');

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
     * Função para buscar um dado PTRES, apartir de pelo menos um dos campos da
     * consulta (código, sigla, descrição ou código completo) para exibição em
     * campos, tipicamente populados via ajax, nas diversas funcionalidades do
     * sistema.
     *
     * @param string $texto
     *               Texto digitado pelo usuário para busca dos dados sobre
     *               um ou mais PTRES
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function getPtresAjax ($texto) {
        // Instrução sql
        $sql = "
            SELECT
                UPPER(
                    TRIM(PTRS_CD_PT_RESUMIDO) || ' - ' ||
                    TRIM(PTRS_SG_PT_RESUMIDO) || ' - ' ||
                    TRIM(PTRS_DS_PT_RESUMIDO) || ' (' ||
                    TRIM(PTRS_CD_PT_COMPLETO) || ')'
                ) AS label,
                PTRS_CD_PT_RESUMIDO AS COD,
                PTRS_DS_PT_RESUMIDO AS descricao,
                PTRS_CD_UNID_ORCAMENTARIA AS UO
            FROM
                CEO_TB_PTRS_PROGRAMA_TRABALHO
            WHERE
                --PTRS_DH_EXCLUSAO_LOGICA IS Null AND
                UPPER(
                    TRIM(PTRS_CD_PT_RESUMIDO) || ' - ' ||
                    TRIM(PTRS_SG_PT_RESUMIDO) || ' - ' ||
                    TRIM(PTRS_DS_PT_RESUMIDO) || ' - (' ||
                    TRIM(PTRS_CD_PT_COMPLETO) || ')'
                ) LIKE UPPER('%$texto%')
            ORDER BY
                PTRS_CD_PT_RESUMIDO
                            ";

        // Retorna default adapter de banco
        $banco = Zend_Db_Table::getDefaultAdapter();

        // Executa a query informada, retornando os registros conforme $texto
        $dados = $banco->fetchAll($sql);

        // Devolve os dados encontrados
        return $dados;
    }

}
