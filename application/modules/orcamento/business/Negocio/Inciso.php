<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as regras negociais sobre Inciso
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Inciso
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2015 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Inciso 
    extends Orcamento_Business_Negocio_Base {

    /**
     * Mensagens
     */
    const MENSAGEM_033 = 'Inciso já cadastrado anteriormente.';
    const MENSAGEM_032 = 'Não é permitido excluir inciso que contenha regras ou alíneas associadas.';
    
    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Inciso();

        // Define a negocio
        $this->_negocio = 'inciso';
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao Nome da ação (action) em questão
     * @return string
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaCampos($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
            INCI_ID_INCISO,
            INCI_VL_INCISO,
            INCI_DS_INCISO";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['index'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
            INCI_VL_INCISO AS \"Inciso\",
            INCI_DS_INCISO AS \"Descrição do Inciso\"
                ";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "INCI_ID_INCISO, ";
        $campos ['excluir'] .= $campos ['detalhe'];

        // Devolve os campos, conforme ação
        return $campos [$acao];
    }
    
    
    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param	none
     * @return	array
     * @author	Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaCombo ()
    {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo ( 'inciso' );
        $dados = $cache->lerCache ( $cacheId );
        
        if( $dados === false ) {
            //Não existindo o cache, busca do banco
            $sql = "
        SELECT
            INCI_ID_INCISO,
            INCI_DS_INCISO
        FROM
            CEO_TB_INCI_INCISO";

            $banco = Zend_Db_Table::getDefaultAdapter ();

            $dados = $banco->fetchPairs ( $sql );

            // Cria o cache
            $cache->criarCache ( $dados, $cacheId );
        }
        
        return $dados;
    }
    
    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param	none
     * @return	array
     * @author	Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaComboComposta ()
    {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo ( 'inciso' );
        $dados = $cache->lerCache ( $cacheId );

        if( $dados === false ) {
            //Não existindo o cache, busca do banco
            $sql = "
        SELECT
            INCI_ID_INCISO,
            CONCAT(CONCAT(INCI_VL_INCISO,' - '), INCI_DS_INCISO) 
            AS INCI_DS_INCISO 
        FROM
            CEO_TB_INCI_INCISO
        ORDER BY INCI_ID_INCISO ASC";

            $banco = Zend_Db_Table::getDefaultAdapter ();
            $dados = $banco->fetchPairs ( $sql );

            // Cria o cache
            $cache->criarCache ( $dados, $cacheId );
        }

        return $dados;
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @param string $chaves Informa a chave, já tratada, se for o caso
     * @return string
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        // Sem condição...
        $restricao['todos'] = " ";
        
        // Condição para ação editar
        $restricao['detalhe'] = " AND INCI_ID_INCISO IN ({$chaves})";

        // Condição para ação editar
        $restricao['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao['excluir'] = $restricao ['detalhe'];

        return $restricao[$acao];
    }
    
    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaOpcoesGrid() {
        
        $acao = Zend_Controller_Front::
            getInstance()->getRequest()->getActionName();
                
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'INCI_VL_INCISO' => array('title' => 'Inciso', 'abbr' => 'Inciso'),
            'INCI_DS_INCISO' => array('title' => 'Descrição Inciso',
                'abbr' => 'Descrição do Inciso'));
        
        // ---------------------------------------------------------------------
        // BOTOES DO GRID

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array('INCI_ID_INCISO');
        
        // botões de ação em massa
        $opcoes ['acoesEmMassa'] = array('incluir','detalhe','editar','excluir');
        
        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
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
     * Validação da regra 093 utilizada na inclusão e edição do inciso
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validacaoRN093($dados) {

        // valor do inciso
        $dadosInciso = $dados['INCI_VL_INCISO'];

        // converte para numeral ou romano
        $numeral = $this->converterNumeralOuRomano($dadosInciso);

        // verifica a regra 093
        $regra093 = $this->consultarRN093($numeral['romano'], 
                $numeral['numeral']);

        // caso regra não esteja ok, retorna mensagem 033
        // caso true no regra093 significa que possui registro
        if (true === $regra093) {
            return self::MENSAGEM_033;
        }

        // retorna ok para a validação e seguirá gravação
        return true;
    }

    /**
     * Efetua validação da regra 093 para update
     * 
     * @param array $dados
     * @return boolean
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validacaoRN093Update($dados) {

        // valor do inciso
        $dadosInciso = $dados['novo'];
        
        // converte para numeral ou romano
        $numeral = $this->converterNumeralOuRomano($dadosInciso);

        // verifica a regra 093
        $regra093 = $this->consultarRN093Update($numeral['romano'],
                $numeral['numeral'], $dados['id']);

        // caso regra não esteja ok, retorna mensagem 033
        // caso true no regra093 significa que possui registro
        if (true === $regra093) {
            return self::MENSAGEM_033;
        }

        // retorna ok para a validação e seguirá gravação
        return true;
    }

    /**
     * Validação da regra 093 utilizada na inclusão
     * 
     * @param string $incisoRomano
     * @param string $incisoNumeral
     * @return boolean
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function consultarRN093($incisoRomano, $incisoNumeral) {

        $sql = "
            SELECT
                COUNT(*)
            FROM
                CEO_TB_INCI_INCISO
            WHERE
                INCI_VL_INCISO = '{$incisoRomano}'
                OR
                INCI_VL_INCISO = '{$incisoNumeral}'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $count = $banco->fetchOne($sql);

        // verifica se existe mais de um cadastro do inciso em romano
        // retorna true se possui registro ou false se não possui
        return $count > 0 ? true : false;
    }

    /**
     * Consulta a regra 93 para verificar se existe algum inciso com o mesmo
     * valor durante a edição
     * 
     * @param string $incisoRomano
     * @param string $incisoNumeral
     * @param string $atual
     * @return boolean
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function consultarRN093Update($incisoRomano, 
        $incisoNumeral, $atual) {

        $sql = "
            SELECT
                COUNT(*)
            FROM
                CEO_TB_INCI_INCISO
            WHERE
                (INCI_VL_INCISO = '{$incisoRomano}'
                OR
                INCI_VL_INCISO = '{$incisoNumeral}')
                AND
                INCI_ID_INCISO != '{$atual}'
        ";
 
        $banco = Zend_Db_Table::getDefaultAdapter();
        $count = $banco->fetchOne($sql);
        
        // verifica se existe mais de um cadastro do inciso em romano
        // retorna true se possui registro ou false se não possui
        return $count > 0 ? true : false;
    }
    
    /**
     * Converte o valor recebido para numeral ou romano
     * 
     * @param array $valor
     * @return mixed array
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function converterNumeralOuRomano($valor) {
        // armazena inciso convertido para outro numeral
        $incisoConvertido = null;

        $incisoRomano = null;
        $incisoNumeral = null;

        // verifica se o inciso está preenchido em número arábico e converte
        // para número romano e vice-versa
        $isNumeric = is_numeric($valor);

        if (true === $isNumeric) {
            $converteInciso = new Zend_Measure_Number($valor, 
                    Zend_Measure_Number::DECIMAL);
            $converteInciso->convertTo(Zend_Measure_Number::ROMAN);
            $incisoConvertido = $converteInciso->getValue();

            $incisoRomano = $incisoConvertido;
            $incisoNumeral = $valor;
        } else {
            // converte para número arábico
            $converteInciso = new Zend_Measure_Number($valor, 
                    Zend_Measure_Number::ROMAN);
            $converteInciso->convertTo(Zend_Measure_Number::DECIMAL);
            $incisoConvertido = $converteInciso->getValue();

            $incisoRomano = $valor;
            $incisoNumeral = $incisoConvertido;
        }

        return array('numeral' => $incisoNumeral, 'romano' => $incisoRomano);
    }

    /**
     * Efetua verificação se já existe alínea ou regra cnj cadastrada
     * 
     * @param array $codigos
     * @return boolean
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarExistRegraAssociada($codigos) {
        
        $in = implode(", ", (array) $codigos);

        $sql = "
            SELECT COUNT(ALIN_ID_ALINEA)
                FROM CEO_TB_ALIN_ALINEA
            WHERE ALIN_ID_INCISO IN ({$in})
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $total = $banco->fetchOne($sql);

        return $total > 0 ? true : false;
    }
    
}