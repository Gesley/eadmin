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
 * Contém as regras negociais sobre importação de nota de crédito
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Importarnc
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Importaref extends Orcamento_Business_Negocio_Base {

    /**
     * Instancia as variaveis na inicialização
     *
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Impd ();

        // Define a negocio
        $this->_negocio = 'Importaref';

        // Instancia a sessão
        $this->_sessao = new Orcamento_Business_Sessao ();

        // Instancia business de nc
        $this->_negocioef = new Orcamento_Business_Negocio_Ef ();
    }

    /**
     * Sobrescreve a classe incluir da business
     * @return @array
     */
    public function incluir($name, $nc) {

        foreach ($nc as $data) {
            $matricula = $this->_sessao->retornaPerfil();

            unset($data['MAX_FILE_SIZE']);

            $dados = array(
                'IMPD_CD_MATRICULA' => strtoupper($matricula['usuario']),
                'IMPD_DS_ARQUIVO_ORIGEM' => $name,
                'IMPD_TX_LINHA' => utf8_encode($data['IMPD_TX_LINHA']),
                'IMPD_DH_IMPORTACAO' => new Zend_Db_Expr('SYSDATE'),
                'IMPD_DS_CLASSE_ARQUIVO' => substr($data['NOCR_CD_NOTA_CREDITO'], 4, 2),
                'IMPD_NR_ERRO' => 0
            );            

            parent::incluir($dados);
        }
        
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
            'IMPD_ID_LINHA' => array('title' => 'Código',
                'abbr' => ''),
            'IMPD_DS_ARQUIVO_ORIGEM' => array('title' => 'Nome do Arquivo',
                'abbr' => ''),
            'IMPD_TX_LINHA' => array('title' => 'Linha do Arquivo',
                'abbr' => ''),
            'IMPD_DH_IMPORTACAO' => array('title' => 'Data Importação',
                'abbr' => ''),
            'IMPD_DS_CLASSE_ARQUIVO' => array('title' => 'Tipo de Arquivo',
                'abbr' => ''),
            'IMPD_CD_MATRICULA' => array('title' => 'Responsavel Importação',
                'abbr' => ''));

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array( 'CAMPO_NAO_EXISTENTE' ); // esconde o código
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
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "   IMPD_ID_LINHA,
                                IMPD_DS_ARQUIVO_ORIGEM,
                                IMPD_TX_LINHA,
                                IMPD_DS_CLASSE_ARQUIVO,
                                IMPD_DH_IMPORTACAO,
                                IMPD_CD_MATRICULA
                                ";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['todos'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = " 
                                IMPD_ID_LINHA          AS \"Código\",
                                IMPD_DS_ARQUIVO_ORIGEM AS \"Nome do Arquivo\",
                                IMPD_TX_LINHA          AS \"Linha do Arquivo\",
                                IMPD_DS_CLASSE_ARQUIVO AS \"Tipo de Arquivo\",
                                IMPD_DH_IMPORTACAO     AS \"Data da Importação\"
            ";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "IMPD_ID_LINHA, ";
        $campos ['excluir'] .= $campos ['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos ['restaurar'] = $campos ['excluir'];

        // Campos para a serem apresentados num combo
        $campos ['combo'] = "";

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
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        $restricao ['index'] = " AND IMPD_DS_CLASSE_ARQUIVO = 'NC'";

        // Condição para ação editar
        $restricao ['detalhe'] = " AND IMPD_ID_LINHA IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

        // Condição para montagem do combo
        // $restricao [ 'combo' ] = " INFO_CD_MATRICULA_EXCLUSAO IS Null ";

        return $restricao [$acao];
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds($acao = null) {

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
     * Realiza a exclusão lógica de uma ou mais esferas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlExclusaoLogica ( $chaves )
    {
       // nesse caso é exclusao fisica na tabela impd
       return $this->retornaExclusaoFisica( $chaves );       
    }    

     /**
     * Realiza a exclusão lógica de uma ou mais esferas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaExclusaoFisica ( $chaves )
    {   
        // start table
        $banco = Zend_Db_Table::getDefaultAdapter();
        
        // Trata a chave primária (ou composta)
        $codigos = $this->separaChave ( $chaves );
        
        // traz os dados importados
        $sqlImportacao = " SELECT IMPD_TX_LINHA FROM CEO_TB_IMPD_IMPORTACAO_DADOS WHERE IMPD_ID_LINHA IN ( $codigos ) ";        
        $dadosImportados = $banco->fetchAll($sqlImportacao);
        
        // monta o array de ncs a serem excluidas, caso seja mais de uma
        foreach ($dadosImportados as $key => $value) {
            $ug = substr($value['IMPD_TX_LINHA'], 49, 6);
            $nc[] = substr($value['IMPD_TX_LINHA'], 29, 12).$ug;           
            
        }
        $nc = $this->separaChave ( $nc );

        // Exclui um ou mais registros da importacao
        $sql = "
DELETE FROM 
            CEO_TB_IMPD_IMPORTACAO_DADOS 
WHERE IMPD_ID_LINHA IN ( $codigos )
                ";

        // exclui um ou mais registros da nota de crédito
        $sqlnota = "
DELETE FROM 
            CEO_TB_NOCR_NOTA_CREDITO 
WHERE NOCR_CD_NOTA_CREDITO IN ( $nc )";
        
        $banco->query($sqlnota);
                
        return $sql;
        
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
}
