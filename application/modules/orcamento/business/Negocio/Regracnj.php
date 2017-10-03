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
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Regracnj
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Regracnj 
    extends Orcamento_Business_Negocio_Base {
    # variável para instancia do modelo de regra.

    /**
     * Mensagens
     */
    const QUANTIDADE_REGISTRO = '0';
    const MENSAGEM_081 = 'Não é possível incluir anos futuros as regras CNJ, apenas o ano corrente ou os passados.';
    const MENSAGEM_122 = 'Regra já cadastrada anteriormente.';
    
    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init () {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Regracnj ();

        // Define a negocio
        $this->_negocio = 'regracnj';
        
    }
    
    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaCampos ($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";
                
        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
            REGC_ID_REGRA,
            TO_CHAR(TO_DATE(REGC_AA_REGRA, 'rr'), 'yyyy') AS REGC_AA_REGRA,
            REGC_VL_NATUREZA_DESP_INICIAL,
            REGC_VL_NATUREZA_DESP_FINAL, 
            REGC_IC_CATEGORIA,
            CASE REGC_IC_TB_IMPACTO
                WHEN 1 THEN 'Financeiro '
                WHEN 2 THEN 'Suplementação '
                WHEN 3 THEN 'Liquidado '
                WHEN 4 THEN 'Dotação Inicial '
                WHEN 5 THEN 'Cancelamento '
                WHEN 6 THEN 'Contingenciamento '
                WHEN 7 THEN 'Provisão '
                WHEN 8 THEN 'Destaque '
                WHEN 9 THEN 'Empenhado '
                WHEN 10 THEN 'Pago '
                WHEN 11 THEN 'Restos a Pagar '
                ELSE ' '
            END REGC_IC_TB_IMPACTO,
            CONCAT(CONCAT(INCI_VL_INCISO,' - '), INCI_DS_INCISO ) 
            AS REGC_ID_INCISO,
            CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA ) 
            AS REGC_ID_ALINEA
        ";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = "
            REGC_ID_REGRA,
            TO_CHAR(TO_DATE(REGC_AA_REGRA, 'rr'), 'yyyy'),
            REGC_VL_NATUREZA_DESP_INICIAL,
            REGC_VL_NATUREZA_DESP_FINAL,
            REGC_IC_CATEGORIA,
            REGC_IC_TB_IMPACTO,
            INCI_ID_INCISO REGC_ID_INCISO,
            ALIN_ID_ALINEA REGC_ID_ALINEA
        ";

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
            REGC_ID_REGRA AS \"Código\",
            TO_CHAR(TO_DATE(REGC_AA_REGRA, 'rr'), 'yyyy') AS \"Ano\",
            CASE REGC_IC_TB_IMPACTO
                WHEN 1 THEN 'Financeiro '
                WHEN 2 THEN 'Suplementação '
                WHEN 3 THEN 'Liquidado '
                WHEN 4 THEN 'Dotação Inicial '
                WHEN 5 THEN 'Cancelamento '
                WHEN 6 THEN 'Contingenciamento '
                WHEN 7 THEN 'Provisão '
                WHEN 8 THEN 'Destaque '
                WHEN 9 THEN 'Empenhado '
                WHEN 10 THEN 'Pago '
                WHEN 11 THEN 'Restos a Pagar '
                ELSE ' '
            END \"Tabela\",
            REGC_VL_NATUREZA_DESP_INICIAL AS \"Natureza da despesa inicial\",
            REGC_VL_NATUREZA_DESP_FINAL AS \"Natureza da despesa final\",
            REGC_IC_CATEGORIA AS \"Categoria\",
            CONCAT(CONCAT(INCI_VL_INCISO,' - '), INCI_DS_INCISO ) AS \"Inciso\",
            CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA ) AS \"Alínea\"
        ";
        
        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "REGC_ID_REGRA, ";
        $campos ['excluir'] .= "REGC_ID_REGRA AS \"Código\",
            TO_CHAR(TO_DATE(REGC_AA_REGRA, 'rr'), 'yyyy') AS \"Ano\",
            CASE REGC_IC_TB_IMPACTO
                WHEN 1 THEN 'Financeiro '
                WHEN 2 THEN 'Suplementação '
                WHEN 3 THEN 'Liquidado '
                WHEN 4 THEN 'Dotação Inicial '
                WHEN 5 THEN 'Cancelamento '
                WHEN 6 THEN 'Contingenciamento '
                WHEN 7 THEN 'Provisão '
                WHEN 8 THEN 'Destaque '
                WHEN 9 THEN 'Empenhado '
                WHEN 10 THEN 'Pago '
                WHEN 11 THEN 'Restos a Pagar '
                ELSE ' '
            END \"Tabela\",
            REGC_VL_NATUREZA_DESP_INICIAL AS \"Natureza da despesa inicial\",
            REGC_VL_NATUREZA_DESP_FINAL AS \"Natureza da despesa final\",
            CONCAT(CONCAT(INCI_VL_INCISO,' - '), INCI_DS_INCISO ) AS \"Inciso\",
            CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA ) AS \"Alínea\"
        ";

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
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {
        
        // Condição para ação editar
        $restricao ['detalhe'] = " AND REGC_ID_REGRA IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];
        
        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

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
        
        $join['index'] = " INNER JOIN CEO_TB_ALIN_ALINEA
            ON REGC_ID_ALINEA = ALIN_ID_ALINEA
            INNER JOIN CEO_TB_INCI_INCISO
            ON INCI_ID_INCISO = ALIN_ID_INCISO
        ";
        
        $join['editar'] = $join ['index'];
        
        $join['detalhe'] = $join ['index'];
        
        $join['excluir'] = $join ['index'];
        
        return $join[$acao];
    }
    
    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaOpcoesGrid () {
        
        // define a ação
        $acao = Zend_Controller_Front::
                getInstance()->getRequest()->getActionName();
        
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'REGC_ID_REGRA' => array('title' => 'Cod', 'abbr' => ''),
            'REGC_AA_REGRA' => array('title' => 'Ano', 'abbr' => ''),
            'REGC_VL_NATUREZA_DESP_INICIAL' 
                => array('title' 
                => 'Natureza da despesa inicial', 'abbr' 
                => ''),
            'REGC_VL_NATUREZA_DESP_FINAL' 
                => array('title' 
                => 'Natureza da despesa final', 'abbr' 
                => ''),
            'REGC_IC_CATEGORIA' => array('title' => 'Categoria', 'abbr' => ''),
            'REGC_IC_TB_IMPACTO' => array('title' => 'Tabela', 'abbr' => ''),
            'REGC_ID_INCISO' => array('title' => 'Inciso', 'abbr' => ''),
            'REGC_ID_ALINEA' => array('title' => 'Alínea', 'abbr' => '')
        );
        
        // ---------------------------------------------------------------------
        // BOTOES DO GRID
        $acaoMassa['index'] = array('incluir','detalhe','editar','excluir');
        
        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = $oculto[$acao];
        
        // botões de ação em massa
        $opcoes ['acoesEmMassa'] = $acaoMassa[$acao];
        
        // Devolve o array de opções
        return $opcoes;
    }
    
/**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Sandro Maceno <smaceno@stefanini.com>
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
     * Validação da regras utilizada na inclusão e edição das regras cnj
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function validacaoRN($dados) {

        // verifica a regra 081
        $regra081 = $this->consultarRN081($dados);
        if (true === $regra081) {
            return self::MENSAGEM_081;
        }
        
        // verifica a regra 122
        $regra122 = $this->consultarRN122($dados);
        
        if (true === $regra122) {
            return self::MENSAGEM_122;
        }
        
        // retorna ok para a validação e seguirá gravação
        return true;
    }
    
    /**
     * Validação da regras utilizada na inclusão e edição das regras cnj
     *
     * @author Sandro Maceno <smaceno@stefanini.com>e
     */
    public function validacaoRNUpdate($dados) {
        
        // verifica a regra 081
        $regra081 = $this->consultarRN081($dados);
        if (true === $regra081) {
            return self::MENSAGEM_081;
        }
        
        // verifica a regra 122
        $regra122 = $this->consultarRN122Update($dados);
        
        if (true === $regra122) {
            return self::MENSAGEM_122;
        }
        
        // retorna ok para a validação e seguirá gravação
        return true;
    }
    
    /*
     * Não é possível incluir anos futuros as regras CNJ, 
     * apenas o ano corrente ou os passados.
     * 
     * @param array $dados
     * @return boolean
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function consultarRN081($dados) {

         // Data passada via dados
        $data = $dados['REGC_AA_REGRA'];
        
        $dataAtual = date("Y");

        if($data <= $dataAtual){
            return false;
        }
        return true;
    }
    
    /*
     * RN122 – REGRA CNJ ÚNICA
     * Uma regra do CNJ é única para um mesmo ano, natureza de despesa inicial, 
     * natureza de despesa final, tabela inciso e alínea, ou seja, apenas se 
     * todos os campos forem iguais que o sistema não deve permitir seu 
     * cadastramento.
     * 
     * @param array $dados
     * @return boolean
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function consultarRN122($dados) {

        // Data passada via dados
        $data = substr($dados['REGC_AA_REGRA'], 2, 2);
        $tabela = $dados['REGC_IC_TB_IMPACTO'];
        $naturezainicial = substr($dados['REGC_VL_NATUREZA_DESP_INICIAL'],0, 8);
        $naturezafinal = substr($dados['REGC_VL_NATUREZA_DESP_FINAL'], 0, 8);
        $categoria = $dados['REGC_IC_CATEGORIA'];
        $alinea = $dados['REGC_ID_ALINEA'];
        
        if (empty($categoria)){
            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    CEO_TB_REGC_REGRA_CNJ
                WHERE
                    REGC_AA_REGRA = '{$data}'
                    AND
                    REGC_IC_TB_IMPACTO = '{$tabela}'
                    AND
                    REGC_VL_NATUREZA_DESP_INICIAL = '{$naturezainicial}'
                    AND
                    REGC_VL_NATUREZA_DESP_FINAL = '{$naturezafinal}'
                    AND
                    REGC_ID_ALINEA = '{$alinea}'
            ";
        } else {     
            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    CEO_TB_REGC_REGRA_CNJ
                WHERE
                    REGC_AA_REGRA = '{$data}'
                    AND
                    REGC_IC_TB_IMPACTO = '{$tabela}'
                    AND
                    REGC_ID_ALINEA = '{$alinea}'
                    AND
                    REGC_IC_CATEGORIA = '{$categoria}'
            ";
        }
        
        
        $banco = Zend_Db_Table::getDefaultAdapter();
        $count = $banco->fetchOne($sql);
        
        // verifica se existe mais de um cadastro na base
        // retorna true se possui registro ou false se não possui
        return $count > 0 ? true : false;
        
    }
    
     /*
     * 
     * @param array $dados
     * @return boolean
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function consultarRN122Update($dados) {

         // Data passada via dados
        $atual = $dados['REGC_ID_REGRA'];
        $alinea = $dados['REGC_ID_ALINEA'];
        $data = substr($dados['REGC_AA_REGRA'], 2, 2);
        $naturezainicial = substr($dados['REGC_VL_NATUREZA_DESP_INICIAL'],0, 8);
        $naturezafinal = substr($dados['REGC_VL_NATUREZA_DESP_FINAL'], 0, 8);
        $tabela = $dados['REGC_IC_TB_IMPACTO'];
        $categoria = $dados['REGC_IC_CATEGORIA'];

        if (empty($categoria)) {
            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    CEO_TB_REGC_REGRA_CNJ
                WHERE
                    REGC_AA_REGRA = '{$data}'
                    AND
                    REGC_IC_TB_IMPACTO = '{$tabela}'
                    AND
                    REGC_VL_NATUREZA_DESP_INICIAL = '{$naturezainicial}'
                    AND
                    REGC_VL_NATUREZA_DESP_FINAL = '{$naturezafinal}'
                    AND
                    REGC_ID_REGRA != '{$atual}'
            ";
        } else {     
            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    CEO_TB_REGC_REGRA_CNJ
                WHERE
                    REGC_AA_REGRA = '{$data}'
                    AND
                    REGC_IC_TB_IMPACTO = '{$tabela}'
                    AND
                    REGC_ID_ALINEA = '{$alinea}'
                    AND
                    REGC_IC_CATEGORIA = '{$categoria}'
                    AND
                    REGC_ID_REGRA != '{$atual}'
            ";
        }
        
        Zend_Debug::dump($sql);
        
        $banco = Zend_Db_Table::getDefaultAdapter();
        $count = $banco->fetchOne($sql);
        
        // verifica se existe mais de um cadastro na base
        // retorna true se possui registro ou false se não possui
        return $count > 0 ? true : false;
        
    }
    
    /**
     * Verifica se existe cadastro
     * 
     * @param array $dados
     * @return boolean
     * 
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function verificarcomboregracnj($dados) {
        
        $inciso = $dados['id'];
        
        $sql = "SELECT
                    ALIN_ID_ALINEA, 
                    CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA ) 
                    AS ALIN_VL_ALINEA
                FROM
                    CEO_TB_ALIN_ALINEA, CEO_TB_INCI_INCISO
                WHERE
                  ALIN_ID_INCISO = '{$inciso}' 
                    AND ALIN_ID_INCISO = INCI_ID_INCISO
                ";
        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);
        return $retorno;
    }
    
    public function verificarcomboregracnjform() {
        
        $sql = "SELECT
                    ALIN_ID_INCISO, ALIN_ID_ALINEA, 
                    CONCAT(CONCAT(ALIN_VL_ALINEA,' - '), ALIN_DS_ALINEA ) 
                    AS ALIN_VL_ALINEA
                FROM
                    CEO_TB_ALIN_ALINEA
                    INNER JOIN CEO_TB_INCI_INCISO 
                    ON ALIN_ID_INCISO = INCI_ID_INCISO
                ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);
        return $retorno;
    }
    
    public function montarComboFormAlinea() {
        
        $array = $this->verificarcomboregracnjform();
        $matriz = array();
        
        foreach($array as $valor)  {
            $idInciso = $valor['ALIN_ID_INCISO'];
            $idAlinea = $valor['ALIN_ID_ALINEA'];
            $descAlinea = $valor['ALIN_VL_ALINEA'];
            $matriz[$idInciso][$idAlinea] = $descAlinea;
        }
        return $matriz;
    }
    
    public function trataDadosComValidacao($dadosPost) { 
        
        $dados = array();
        $dados['REGC_ID_REGRA'] = $dadosPost['REGC_ID_REGRA'];
        $dados['REGC_AA_REGRA'] = substr($dadosPost['REGC_AA_REGRA'], 2, 2);
        $dados['REGC_IC_TB_IMPACTO'] = $dadosPost['REGC_IC_TB_IMPACTO'];
        $dados['REGC_VL_NATUREZA_DESP_INICIAL'] 
                = substr($dadosPost['REGC_VL_NATUREZA_DESP_INICIAL'], 0, 8);
        $dados['REGC_VL_NATUREZA_DESP_FINAL'] 
                = substr($dadosPost['REGC_VL_NATUREZA_DESP_FINAL'], 0, 8);
        $dados['REGC_IC_CATEGORIA'] = $dadosPost['REGC_IC_CATEGORIA'];
        $dados['REGC_ID_ALINEA'] = $dadosPost['REGC_ID_ALINEA'];
        $dados['REGC_DT_REGRA'] = new Zend_Db_Expr('SYSDATE');

        return $dados;
    }
}