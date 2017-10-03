<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Contém as regras negociais sobre Alinea
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Alinea
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Alinea extends Orcamento_Business_Negocio_Base {

    /**
     * Mensagens
     */
    const QUANTIDADE_REGISTRO = '0';
    const MENSAGEM_033 = 'Alínea já cadastrado anteriormente.';
    const MENSAGEM_029 = 'Não é possível cadastrar uma mesma alínea para um mesmo inciso.';
    const MENSAGEM_030 = 'Não é permitido excluir alínea que contenha regra associada a ela.';

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Alinea();

        // Define a negocio
        $this->_negocio = 'alinea';
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao Nome da ação (action) em questão
     * @return string
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaCampos($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos['index'] = "
                ALIN_ID_ALINEA,
                INCI_VL_INCISO,          
                INCI_DS_INCISO,
                ALIN_VL_ALINEA,
                ALIN_DS_ALINEA
            ";

        // Campos para a serem apresentados na editarAction
        $campos['editar'] = "
                ALIN_ID_ALINEA,
                ALIN_ID_INCISO,
                CONCAT(CONCAT(INCI_VL_INCISO,' - '), INCI_DS_INCISO) \"Inciso\",          
                INCI_DS_INCISO,
                ALIN_VL_ALINEA,
                ALIN_DS_ALINEA
            ";

        // Campos para a serem apresentados na detalheAction
        $campos['detalhe'] = "
                INCI_VL_INCISO AS \"Inciso\",          
                INCI_DS_INCISO AS \"Descrição Inciso\",
                ALIN_VL_ALINEA AS \"Alínea\",
                ALIN_DS_ALINEA AS \"Descrição Alínea\"
            ";

        // Campos para a serem apresentados na excluirAction
        $campos['excluir'] = "ALIN_ID_ALINEA, ";
        $campos['excluir'] .= $campos['detalhe'];

        // Devolve os campos, conforme ação
        return $campos[$acao];
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @param string $chaves Informa a chave, já tratada, se for o caso
     * @return string
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        // Sem condição...
        $restricao ['todos'] = " ";

        $restricao ['detalhe'] = " AND ALIN_ID_ALINEA IN ( {$chaves} ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        return $restricao [$acao];
    }

    /**
     * Retorna as condições restritivas, se houver INNER JOIN
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaJoins($acao = 'todos') {

        $join['index'] = " INNER JOIN CEO_TB_INCI_INCISO ON
                ALIN_ID_INCISO = INCI_ID_INCISO";

        $join['detalhe'] = $join['index'];
        $join['editar'] = $join['index'];
        $join['excluir'] = $join['index'];

        return $join[$acao];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaOpcoesGrid() {

        // define a ação
        $acao = Zend_Controller_Front::
                getInstance()->getRequest()->getActionName();

        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'INCI_VL_INCISO' => array('title' => 'Inciso',
                'abbr' => 'Inciso'),
            'INCI_DS_INCISO' => array('title' => 'Descrição Inciso',
                'abbr' => 'Descrição Inciso'),
            'ALIN_VL_ALINEA' => array('title' => 'Alínea',
                'abbr' => 'Alínea'),
            'ALIN_DS_ALINEA' => array('title' => 'Descrição Alínea',
                'abbr' => 'Descrição do Alínea'));

        // ---------------------------------------------------------------------
        // BOTOES DO GRID
        $acaoMassa['index'] = $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array('ALIN_ID_ALINEA');

        // botões de ação em massa
        $opcoes ['acoesEmMassa'] = array('incluir', 'detalhe', 'editar', 'excluir');

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Sandro Maceno <smaceno@stefanini.com>
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
     * Válidação de regra negocial
     * 
     * RN092 – CÓGIDO ALÍNEA
     * O código da alínea é alfabético pode ser inserido letras de A à Z, 
     * este código é único para um inciso, ou seja se cadastrarmos uma alínea 
     * A para o inciso 1, não poderemos mais cadastrar alíneas A 
     * para o inciso 1, apenas para outros incisos.
     * 
     * 
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function validacaoRN($dados) {

        // verifica a regra 092
        $regra092 = $this->consultarRN092($dados);

        // caso regra não esteja ok, retorna mensagem 029
        // caso true no regra092 significa que possui registro
        if (self::QUANTIDADE_REGISTRO < $regra092) {
            return self::MENSAGEM_029;
        }



        // retorna ok para a validação e seguirá gravação
        return true;
    }

    /**
     * Efetua validação da regra 093 para update
     * 
     * @param array $dados
     * @return boolean
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function validacaoRN093Update($dados) {

        // verifica a regra 092
        $regra092 = $this->consultarRN092Update($dados);

        // caso regra não esteja ok, retorna mensagem 028
        // caso true no regra092 significa que possui registro
        if (true === $regra092) {
            return self::MENSAGEM_029;
        }

        // retorna ok para a validação e seguirá gravação
        return true;
    }

    /**
     * Retorna o próximo ID
     * 
     * @return int
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function consultarRN092($dados) {

        // valor do alinea
        $dadosInciso = $dados['ALIN_ID_INCISO'];
        $dadosVlAlinea = $dados['ALIN_VL_ALINEA'];

        $sql = "
            SELECT
                COUNT(*)
            FROM
                CEO_TB_ALIN_ALINEA
            WHERE
                ALIN_ID_INCISO = '{$dadosInciso}'
                AND
                ALIN_VL_ALINEA = '{$dadosVlAlinea}'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $proximo = $banco->fetchOne($sql);

        // verifica se existe mais de um cadastro do inciso em romano
        // retorna true se possui registro ou false se não possui
        return $proximo;
    }

    /**
     * Retorna o próximo ID
     * 
     * @return int
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function consultarRN092Update($dados) {

        // valor do alinea
        $dadosInciso = $dados['inciso'];
        $dadosVlAlinea = $dados['novo'];
        $atual = $dados['id'];

        $sql = "
            SELECT
                COUNT(*)
            FROM
                CEO_TB_ALIN_ALINEA
            WHERE
                ALIN_ID_INCISO = '{$dadosInciso}'
                AND
                ALIN_VL_ALINEA = '{$dadosVlAlinea}'
                AND
                ALIN_ID_ALINEA != '{$atual}'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $retorno = $banco->fetchOne($sql);

        // verifica se existe mais de um cadastro do inciso em romano
        // retorna true se possui registro ou false se não possui
        return $retorno > 0 ? true : false;
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param	none
     * @return	array
     * @author	Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaComboComposta() {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo('alinea');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            //Não existindo o cache, busca do banco
            $sql = "
        SELECT
            ALIN_ID_ALINEA,
            CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA) 
            AS ALIN_DS_ALINEA 
        FROM
            CEO_TB_ALIN_ALINEA
        ORDER BY ALIN_ID_ALINEA ASC";

            $banco = Zend_Db_Table::getDefaultAdapter();
            $dados = $banco->fetchPairs($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Efetua verificação se já existe alínea cadastrada em uma regra cnj
     * 
     * @param array $codigos
     * @return boolean
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarExistRegraAssociada($codigos) {

        $in = implode(", ", (array) $codigos);

        $sql = "
            SELECT COUNT(REGC_ID_REGRA)
                FROM CEO_TB_REGC_REGRA_CNJ
            WHERE REGC_ID_ALINEA IN ({$in})
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $total = $banco->fetchOne($sql);

        return $total > 0 ? true : false;
    }

}
