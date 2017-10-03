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
 */
class Orcamento_Business_Negocio_Regra extends Orcamento_Business_Negocio_Base {
    # variável para instancia do modelo de regra.

    protected $_model;

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init () {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Regr ();

        // Define a negocio
        $this->_negocio = 'regra';

        // Define a model de elementos da regra
        $this->_modelElementosregra = new Orcamento_Model_DbTable_Elemregr ();

        // Define o negocio da regra
        $this->_negocioElementosregra = new Orcamento_Business_Negocio_Elementosregra ();
    }

    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function transformaFormulario ($formulario, $acao) {
        if ($acao == Orcamento_Business_Dados::ACTION_INCLUIR) {
            $formulario->removeElement('RGEX_ID_REGRA_EXERCICIO');
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function transformaDados ($dados, $acao) {
        // Sera usado em todas as ações
        $acao = null;

        // Remove o % tanto na inclusao como na edição
        $dados['RGEX_VL_PERCENTUAL'] = str_replace("%", "", $dados['RGEX_VL_PERCENTUAL']);

        return $dados;
    }

    /**
     * Valida se a regra foi incluida com sucesso
     *
     * @param array $dados
     * @return (array) - sucesso e mensagem de erro definida
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function validaInclusao ($dados) {
        // Valida negocialmente a inclusão do registro
        // $qtdeRegistros = $this->validaRegras($dados);

        $resultado ['sucesso'] = true;

        if ($qtdeRegistros == 1) {
            $resultado ['sucesso'] = false;
            $resultado ['msgErro'] = Orcamento_Business_Dados::MSG_DUPLICIDADEREGRA_ERRO;
        }

        return $resultado;
    }

    /**
     * Método que valida edição de regra.
     *
     * @param string $dados
     * @return Ambigous <boolean, string>
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     * @version 1.1
     */
    public function validaEdicao ($dados = null) {

        /*
         * Recupera dados antigos.
         */
        $sql = "SELECT * FROM CEO_TB_RGEX_REGRA_EXERCICIO WHERE RGEX_ID_REGRA_EXERCICIO = " . $dados['RGEX_ID_REGRA_EXERCICIO'];
        $dadosVelhos = Zend_Db_Table::getDefaultAdapter()->fetchRow($sql);

        /*
         * Valida negocialmente a inclusão do registro
         */
        // $qtdeRegistros = $this->validaRegras($dados);

        $resultado ['sucesso'] = true;

        if ($qtdeRegistros == 1) {
            $resultado ['sucesso'] = false;
            $resultado ['msgErro'] = Orcamento_Business_Dados::MSG_DUPLICIDADEREGRA_ERRO;
        } else {

            /*
             * Se houve modificação de dados impactantes na regra, muda a situação para 'não aplicada'.
             */

            if ($dadosVelhos['RGEX_AA_ANO'] != $dados['RGEX_AA_ANO'] || $dadosVelhos['RGEX_VL_PERCENTUAL'] != $dados['RGEX_VL_PERCENTUAL'] || $dadosVelhos['RGEX_DS_INCIDENCIA_REGRA'] != $dados['RGEX_DS_INCIDENCIA_REGRA']) {

                try {

                    $sql = "UPDATE CEO_TB_RGEX_REGRA_EXERCICIO "
                        . "SET RGEX_IC_SITUACAO_REGRA = 'N' "
                        . "WHERE RGEX_ID_REGRA_EXERCICIO = " . $dados['RGEX_ID_REGRA_EXERCICIO']
                    ;

                    $banco = Zend_Db_Table::getDefaultAdapter();
                    $banco->beginTransaction();
                    $banco->query($sql);
                    $banco->commit();
                } catch (Exception $ex) {

                    $banco->rollBack();
                    throw $ex;
                }
            }
        }

        return $resultado;
    }

    /**
     * Sobrescreve o método de incluir da classe base
     * para validar a inclusão de regras
     *
     * @see Orcamento_Business_Negocio_Base::incluir()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     * @version 1.1
     */
    public function incluir ($dados) {

        // retorna o id da regra gravada
        $resultado = parent::incluir($dados);

        $codigo = $resultado ['codigo'];

        if ($resultado ['sucesso'] == true) {

            // grava os elementos da regra
            foreach ($dados['registro'] as $registro => $v) {

                $split = explode(" ", $v);
                $this->_negocioElementosregra->incluirRegra($codigo, $split);
            }
        }

        return $resultado;
    }

    /**
     * Sobrescreve o método de incluir da classe base para validar a inclusão
     * de regras
     *
     * @see Orcamento_Business_Negocio_Base::incluir()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     * @version 1.1
     */
    public function editar ($dados) {

        unset($dados["DESP_CD_PT_RESUMIDO"]);
        unset($dados["DESP_CD_ELEMENTO_DESPESA_SUB"]);
        unset($dados["DESP_CD_TIPO_DESPESA"]);
        unset($dados["Enviar"]);

        $resultado = parent::editar($dados);


        if ($resultado['codigo']) {

            $this->_negocioElementosregra->excluiElementos($resultado['codigo']);
        }

      

        // incluir elementos caso existam
        if ($dados['registro']) {

            foreach ($dados['registro'] as $registro => $v) {
                $split = explode(" ", $v);
                $this->_negocioElementosregra->incluirRegra($resultado['codigo'], $split);
            }
        }

        return $resultado;
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
    public function retornaCampos ($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
RGEX_ID_REGRA_EXERCICIO,
RGEX_DS_REGRA_EXERCICIO,
CONCAT(RGEX_VL_PERCENTUAL,' %') AS RGEX_VL_PERCENTUAL,
RGEX_DS_INCIDENCIA_REGRA,
RGEX_AA_ANO,
CASE RGEX_IC_SITUACAO_REGRA
    WHEN 'N' THEN 'Não Aplicada '
    WHEN 'A' THEN 'Aplicada ' END AS
RGEX_IC_SITUACAO_REGRA
";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['index'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
RGEX_ID_REGRA_EXERCICIO         AS \"Código\",
RGEX_DS_REGRA_EXERCICIO         AS \"Descrição\",
RGEX_VL_PERCENTUAL              AS \"Percentual\",
RGEX_DS_INCIDENCIA_REGRA        AS \"Campo de incidência\",
RGEX_AA_ANO                     AS \"Ano\",
CASE RGEX_IC_SITUACAO_REGRA
    WHEN 'N' THEN 'Não Aplicada '
    WHEN 'A' THEN 'Aplicada' END
                                AS \"Situação\"
";

        // Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "RGEX_ID_REGRA_EXERCICIO, ";
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {
        // Condição para ação editar
        $restricao ['detalhe'] = " AND RGEX_ID_REGRA_EXERCICIO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

        // Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

        // Condição para montagem do combo
        $restricao ['combo'] = " RGEX_CD_MATRICULA_EXCLUSAO IS Null ";

        return $restricao [$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais regras
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica ($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $regras = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_RGEX_REGRA_EXERCICIO
SET
    RGEX_CD_MATRICULA_EXCLUSAO          = '$matricula',
    /* @TODO - ESFE_DH_EXCLUSAO_LOGICA             = SYSDATE */
WHERE
    RGEX_ID_REGRA_EXERCICIO                      IN ( $regras )
    /* @TODO - AND ESFE_DH_EXCLUSAO_LOGICA             IS Null */
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
    public function retornaSqlRestauracaoLogica ($chaves) {
        // Trata a chave primária (ou composta)
        $regras = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_RGEX_REGRA_EXERCICIO
SET
    RGEX_CD_MATRICULA_EXCLUSAO          = Null,
    ESFE_DH_EXCLUSAO_LOGICA             = Null
WHERE
    RGEX_ID_REGRA_EXERCICIO                      IN ( $regras )
    AND ESFE_DH_EXCLUSAO_LOGICA             IS NOT Null
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
    public function retornaOpcoesGrid () {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'RGEX_ID_REGRA_EXERCICIO' => array('title' => 'Regra', 'abbr' => ''),
            'RGEX_DS_REGRA_EXERCICIO' => array('title' => 'Descrição', 'abbr' => ''),
            'RGEX_VL_PERCENTUAL' => array('title' => 'Percentual', 'abbr' => ''),
            'RGEX_DS_INCIDENCIA_REGRA' => array('title' => 'Incidência da Regra', 'abbr' => ''),
            'RGEX_AA_ANO' => array('title' => 'Ano Exercício', 'abbr' => ''),
            'RGEX_IC_SITUACAO_REGRA' => array('title' => 'Situação', 'abbr' => ''),
            'RGEX_CD_MATRICULA_EXCLUSAO' => array('title' => 'Status da regra', 'abbr' => 'Informa se o registro foi ou não excluído'));

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array();

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
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
     * Metodo que faz a aplicação do percentual das regras nas despesas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function aplicarRegra ($chave) {

        $valor = new Trf1_Orcamento_Valor ();

        $percentual = $this->retornaPercentualRegra($chave);

        $percentual = $valor->retornaValorParaBanco($percentual);

        $condicoes = $this->retornaCondicoesRegra($chave);

        # Se não existir PTRES e ND na regra, não aplica a regra.
        if (!$condicoes) {
            return false;
        }

        # limita a regra para o PTRES e ND informado na regra.
        $sqlDespesasValidas = "SELECT DISTINCT DESP_NR_DESPESA "
            . "FROM CEO_TB_DESP_DESPESA, CEO_TB_TIDE_TIPO_DESPESA, CEO_TB_VLDE_VALOR_DESPESA "
            . "WHERE CEO_TB_DESP_DESPESA.DESP_CD_TIPO_DESPESA = CEO_TB_TIDE_TIPO_DESPESA.TIDE_CD_TIPO_DESPESA "
            . "AND CEO_TB_VLDE_VALOR_DESPESA.VLDE_NR_DESPESA = CEO_TB_DESP_DESPESA.DESP_NR_DESPESA "
            . "AND " . $condicoes
            . " AND TIDE_DS_TIPO_DESPESA NOT Like '%- Reajuste'";

        # recupera o valor do campo incidência da regra.
        $incidencia = $this->_model->fetchAll(array('RGEX_ID_REGRA_EXERCICIO = ?' => $chave))->current();

        # define qual regra será aplicada apartir do valor da incidência.
        switch ($incidencia->RGEX_DS_INCIDENCIA_REGRA) {

            # regra de composição da base;
            case "Composição da base": {

                    $sqlLimpaValores = " DELETE CEO_TB_VLDE_VALOR_DESPESA "
                        . "WHERE VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL
                        . " AND VLDE_NR_DESPESA IN (" . $sqlDespesasValidas . ")";

                    $sqlInserveValores = "INSERT INTO CEO_TB_VLDE_VALOR_DESPESA "
                        . "(VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, VLDE_VL_DESPESA, VLDE_DH_DESPESA) "
                        . "SELECT VLDE_NR_DESPESA, "
                        . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL
                        . ", VLDE_VL_DESPESA * ( 100 + $percentual ) / 100, SYSDATE "
                        . "FROM CEO_TB_VLDE_VALOR_DESPESA "
                        . "WHERE VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ANTERIOR
                        . " AND VLDE_NR_DESPESA IN ($sqlDespesasValidas)";

                    break;
                }

            # regra de reajuste da proposta.
            case "Reajuste do exercício": {

                    $sqlLimpaValores = "DELETE FROM CEO_TB_VLDE_VALOR_DESPESA "
                        . "WHERE VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_PROPOSTA_ATUAL
                        . " AND VLDE_NR_DESPESA IN ($sqlDespesasValidas)";

                    $sqlInserveValores = "INSERT INTO CEO_TB_VLDE_VALOR_DESPESA "
                        . "(VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, VLDE_VL_DESPESA, VLDE_DH_DESPESA) SELECT VLDE_NR_DESPESA, "
                        . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_PROPOSTA_ATUAL
                        . ", VLDE_VL_DESPESA * ($percentual / 100), SYSDATE "
                        . "FROM CEO_TB_VLDE_VALOR_DESPESA "
                        . "WHERE VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL
                        . " AND VLDE_NR_DESPESA IN ($sqlDespesasValidas)";

                    break;
                }

            # regra de reajuste aplicado ao limite.
            case "Ajuste ao limite": {

                    $sqlLimpaValores = "DELETE FROM CEO_TB_VLDE_VALOR_DESPESA "
                        . "WHERE VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_APLICADO_LIMITE
                        . " AND VLDE_NR_DESPESA IN ($sqlDespesasValidas)";

                    $sqlInserveValores = "INSERT INTO CEO_TB_VLDE_VALOR_DESPESA "
                        . "(VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, VLDE_VL_DESPESA, VLDE_DH_DESPESA) SELECT VLDE_NR_DESPESA, "
                        . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_APLICADO_LIMITE
                        . ", VLDE_VL_DESPESA * ($percentual / 100), SYSDATE FROM CEO_TB_VLDE_VALOR_DESPESA WHERE  VLDE_CD_DEMANDANTE = "
                        . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL . " AND VLDE_NR_DESPESA IN ($sqlDespesasValidas)";

                    break;
                }
        }

        # atualiza a regra para o status aplicada.
        $sqlAtualizaSituacao = "UPDATE CEO_TB_RGEX_REGRA_EXERCICIO "
            . "SET RGEX_IC_SITUACAO_REGRA = 'A' "
            . "WHERE RGEX_ID_REGRA_EXERCICIO = $chave";

        $sqls [0] = $sqlLimpaValores;
        $sqls [1] = $sqlInserveValores;
        $sqls [2] = $sqlAtualizaSituacao;

        $resultado = $this->executaQuery($sqls, true);

        /*
         * @TODO Consertar a regra 112
         */
//        # insere valores recalculados de reserva de reajuste RN112.
//        $banco = Zend_Db_Table::getDefaultAdapter();
//
//        # recaulcula o valor das despesas reserva de reajuste RN112.
//        $stmtReserva = "SELECT VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, substr(DESP_CD_ELEMENTO_DESPESA_SUB, 1,2), "
//            . "DESP_CD_PT_RESUMIDO, DESP_AA_DESPESA, SUM(VLDE_VL_DESPESA) "
//            . "FROM CEO.CEO_TB_DESP_DESPESA, CEO.CEO_TB_VLDE_VALOR_DESPESA, CEO_TB_TIDE_TIPO_DESPESA "
//            . "WHERE DESP_NR_DESPESA = VLDE_NR_DESPESA "
//            . "AND CEO_TB_DESP_DESPESA.DESP_CD_TIPO_DESPESA = CEO_TB_TIDE_TIPO_DESPESA.TIDE_CD_TIPO_DESPESA "
//            . "AND TIDE_DS_TIPO_DESPESA NOT Like '%- Reajuste' "
//            . "AND DESP_AA_DESPESA = " . $incidencia->RGEX_AA_ANO
//            . " GROUP BY (VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, substr(DESP_CD_ELEMENTO_DESPESA_SUB, 1,2), DESP_CD_PT_RESUMIDO, DESP_AA_DESPESA)";
//
//        $reservaReajuste = $banco->fetchAll($stmtReserva);
//
//        $banco->beginTransaction();
//
//        try {
//
//            foreach ($reservaReajuste as $key) {
//
//                $stmt = "UPDATE CEO.CEO_TB_VLDE_VALOR_DESPESA "
//                    . " SET VLDE_VL_DESPESA = " . "'" . $key['SUM(VLDE_VL_DESPESA)'] . "'"
//                    . " WHERE VLDE_NR_DESPESA = " . "'" . $key['VLDE_NR_DESPESA'] . "'"
//                    . " AND VLDE_CD_DEMANDANTE = " . "'" . $key['VLDE_CD_DEMANDANTE'] . "'";
//
//                $banco->query($stmt);
//            }
//
//            $banco->commit();
//        } catch (Exception $ex) {
//
//            $banco->rollBack();
//            throw $ex;
//        }

        return $resultado;
    }

    /**
     * Retorna o percentual de uma regra
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     *
     * @return object
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    private function retornaPercentualRegra ($chave) {
        $sql = "
SELECT
    RGEX_VL_PERCENTUAL AS PERCENTUAL
FROM
    CEO_TB_RGEX_REGRA_EXERCICIO
WHERE
    RGEX_ID_REGRA_EXERCICIO = $chave
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $regra = $banco->fetchOne($sql);

        return $regra;
    }

    /**
     * Valida uma regra
     *
     * @param array $dados
     * @return object
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function validaRegras ($dados) {

        /*
         * Verifica se já existe alguma regra igual a que está sendo criada ou editada.
         */
        $dados['RGEX_VL_PERCENTUAL'] = str_replace("%", "", $dados['RGEX_VL_PERCENTUAL']);

        $sql = "SELECT "
            . "CASE WHEN Count(*) > 0 "
            . "THEN 1 "
            . "ELSE 0 "
            . "END AS QTDE "
            . "FROM CEO_TB_RGEX_REGRA_EXERCICIO "
            . "WHERE RGEX_VL_PERCENTUAL = " . $dados['RGEX_VL_PERCENTUAL']
            . " AND RGEX_AA_ANO = " . $dados['RGEX_AA_ANO']
            . " AND RGEX_DS_INCIDENCIA_REGRA = " . "'" . $dados['RGEX_DS_INCIDENCIA_REGRA'] . "'"
        ;

        /*
         * Verifica se é edição.
         */
        if (!empty($dados['RGEX_ID_REGRA_EXERCICIO'])) {

            $sql = $sql . " AND RGEX_ID_REGRA_EXERCICIO <> " . $dados['RGEX_ID_REGRA_EXERCICIO'];
        }

        return Zend_Db_Table::getDefaultAdapter()->fetchOne($sql);
    }

    /**
     * Retorna uma ou mais condições para uma regra
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     * @return object
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaCondicoesRegra ($chave) {
        // Retorna condições da regra informada
        $sql = "
SELECT
    RGEX.RGEX_AA_ANO,
    RGEX.RGEX_VL_PERCENTUAL,
    RGEX.RGEX_IC_SITUACAO_REGRA,
    ELRG_CD_PT_RESUMIDO,
    ELRG_CD_ELEMENTO_DESPESA_SUB,
    ELRG_CD_TIPO_DESPESA,
    ELRG_IC_REGRA_APLICADA
FROM
    CEO_TB_RGEX_REGRA_EXERCICIO RGEX
Left JOIN
    CEO_TB_ELRG_ELEMENTOS_REGRA ELRG ON
        ELRG.ELRG_ID_REGRA_EXERCICIO = RGEX.RGEX_ID_REGRA_EXERCICIO
WHERE
    RGEX.RGEX_ID_REGRA_EXERCICIO = $chave
                ";

        $sql_Melhor = "
SELECT
    CASE
        WHEN RGEX.RGEX_AA_ANO IS NOT NULL
        THEN 'DESP_AA_DESPESA = ' || RGEX.RGEX_AA_ANO || ' AND '
        ELSE ''
    END ||
    CASE
        WHEN ELRG.ELRG_CD_PT_RESUMIDO IS NOT NULL
        THEN 'DESP_CD_PT_RESUMIDO = ' || ELRG.ELRG_CD_PT_RESUMIDO || ' AND '
        ELSE ''
    END ||
    CASE
        WHEN ELRG.ELRG_CD_ELEMENTO_DESPESA_SUB IS NOT NULL
        THEN 'DESP_CD_ELEMENTO_DESPESA_SUB = \"' || ELRG.ELRG_CD_ELEMENTO_DESPESA_SUB || '\" AND '
        ELSE ''
    END ||
    CASE
        WHEN ELRG.ELRG_CD_TIPO_DESPESA IS NOT NULL
        THEN 'DESP_CD_TIPO_DESPESA = ' || ELRG.ELRG_CD_TIPO_DESPESA || ' AND '
        ELSE ''
    END AS CONDICAO,
    RGEX.RGEX_VL_PERCENTUAL
FROM
    CEO_TB_RGEX_REGRA_EXERCICIO RGEX
Left JOIN
    CEO_TB_ELRG_ELEMENTOS_REGRA ELRG ON
        ELRG.ELRG_ID_REGRA_EXERCICIO = RGEX.RGEX_ID_REGRA_EXERCICIO
WHERE
    RGEX.RGEX_ID_REGRA_EXERCICIO = 1
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $regras = $banco->fetchAll($sql);

        if (!$regras) {
            return "";
        }

        $condicoes = "";
        $condicao = "";

        foreach ($regras as $regra) {
            $ano = $regra ['RGEX_AA_ANO'];
            $ptres = $regra ['ELRG_CD_PT_RESUMIDO'];
            $natureza = $regra ['ELRG_CD_ELEMENTO_DESPESA_SUB'];
            $carater = $regra ['ELRG_CD_TIPO_DESPESA'];

            if ($ano) {
                $condicao .= "DESP_AA_DESPESA = $ano AND ";
            }

            if ($ptres) {
                $condicao .= "DESP_CD_PT_RESUMIDO = $ptres AND ";
            }

            if ($natureza) {
                $nat1_6 = substr($natureza, 0, 6);
                $nat7_2 = substr($natureza, 6, 2);
                if ($nat7_2 == '00') {
                    $condicao .= "DESP_CD_ELEMENTO_DESPESA_SUB Like '$nat1_6%' AND ";
                } else {
                    $condicao .= "DESP_CD_ELEMENTO_DESPESA_SUB = $natureza AND ";
                }
            }

            if ($carater) {
                $condicao .= "DESP_CD_TIPO_DESPESA = $carater AND ";
            }

            $condicao = substr($condicao, 0, -4);

            $condicoes .= PHP_EOL . "( $condicao) OR ";
            $condicao = "";
        }

        $condicoes = substr($condicoes, 0, -3);

        return $condicoes;
    }

}
