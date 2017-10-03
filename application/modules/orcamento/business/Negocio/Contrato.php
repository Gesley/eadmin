<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
 */

/**
 * Contém as regras negociais sobre contrato
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_contrato
 * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Contrato extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function init () {

// Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Ctrd();

// Define a negocio
        $this->_negocio = 'contrato';
    }

    public function editar ($dadosContrato) {

        $valor = new Trf1_Orcamento_Valor();
        $CeotbCtrdContratoDespesa = new Trf1_Orcamento_Negocio_Contratodespesa();


        if ($dadosContrato ["CTRD_DT_INICIO_VIGENCIA"]) {
            $dadosContrato ["CTRD_DT_INICIO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_INICIO_VIGENCIA'] . "','DD/MM/YYYY')");
        }
        if ($dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"]) {
            $dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_TERMINO_VIGENCIA'] . "','DD/MM/YYYY')");
        }
        if ($dadosContrato ["CTRD_VL_DESPESA"] != "") {
            $valorcontrato = $valor->retornaValorParaBancoRod($dadosContrato ["CTRD_VL_DESPESA"]);
            $dadosContrato ["CTRD_VL_DESPESA"] = new Zend_Db_Expr("TO_NUMBER(" . $valorcontrato . ")");
        }
        if ($dadosContrato ["CTRD_CPFCNPJ_DESPESA"] != "") {
            $dadosContrato ["CTRD_CPFCNPJ_DESPESA"] = $CeotbCtrdContratoDespesa->retirarCaractercpf($dadosContrato ["CTRD_CPFCNPJ_DESPESA"]);
        }

        return parent::editar($dadosContrato);
    }

    public function incluir ($dadosContrato) {

        $valor = new Trf1_Orcamento_Valor();
        $CeotbCtrdContratoDespesa = new Trf1_Orcamento_Negocio_Contratodespesa();


        if ($dadosContrato ["CTRD_DT_INICIO_VIGENCIA"]) {
            $dadosContrato ["CTRD_DT_INICIO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_INICIO_VIGENCIA'] . "','DD/MM/YYYY')");
        }
        if ($dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"]) {
            $dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_TERMINO_VIGENCIA'] . "','DD/MM/YYYY')");
        }
        if ($dadosContrato ["CTRD_VL_DESPESA"] != "") {
            $valorcontrato = $valor->retornaValorParaBancoRod($dadosContrato ["CTRD_VL_DESPESA"]);
            $dadosContrato ["CTRD_VL_DESPESA"] = new Zend_Db_Expr("TO_NUMBER(" . $valorcontrato . ")");
        }
        if ($dadosContrato ["CTRD_CPFCNPJ_DESPESA"] != "") {
            $dadosContrato ["CTRD_CPFCNPJ_DESPESA"] = $CeotbCtrdContratoDespesa->retirarCaractercpf($dadosContrato ["CTRD_CPFCNPJ_DESPESA"]);
        }

        unset($dadosContrato['CTRD_ID_CONTRATO_DESPESA']);

        return parent::incluir($dadosContrato);
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function retornaCampos ($acao = 'todos') {

// Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

// Campos para a serem apresentados na indexAction
        $campos ['index'] = ""
            . "CTRD_ID_CONTRATO_DESPESA , "
            . "CTRD_NR_CONTRATO, "
            . "CTRD_NR_DESPESA, "
            . "CTRD_NM_EMPRESA_CONTRATADA, "
            . "CTRD_DT_INICIO_VIGENCIA, "
            . "CTRD_DT_TERMINO_VIGENCIA, "
            . "CTRD_CPFCNPJ_DESPESA, "
            . "CTRD_VL_DESPESA, "
            . "CASE WHEN LENGTH(CTRD_CD_MATRICULA_EXCLUSAO) > 0 "
            . "THEN 'Excluído ' "
            . "ELSE 'Ativo' "
            . "END AS CTRD_STATUS";

// Campos para a serem apresentados na editarAction
        $campos ['editar'] = "CTRD_ID_CONTRATO_DESPESA, "
            . "CTRD_NR_CONTRATO, "
            . "CTRD_NR_DESPESA, "
            . "CTRD_NM_EMPRESA_CONTRATADA, "
            . "CTRD_DT_INICIO_VIGENCIA, "
            . "CTRD_DT_TERMINO_VIGENCIA, "
            . "CTRD_CPFCNPJ_DESPESA, "
            . "CTRD_VL_DESPESA, "
            . "CASE WHEN LENGTH(CTRD_CD_MATRICULA_EXCLUSAO) > 0 "
            . "THEN 'Excluído ' "
            . "ELSE 'Ativo' "
            . "END AS \"Status do registro\", CTRD_CD_MATRICULA_EXCLUSAO AS \"Excluído por\", CTRD_DH_EXCLUSAO_LOGICA AS \"Data da exclusão\"";

// Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "CTRD_ID_CONTRATO_DESPESA AS \"Código\", "
            . "CTRD_NR_DESPESA AS \"Despesa\", "
            . "CTRD_VL_DESPESA AS \"Valor Contrato\", "
            . "CTRD_NM_EMPRESA_CONTRATADA AS \"Empresa\", "
            . "CTRD_DT_INICIO_VIGENCIA AS \"Inicio vigência\", "
            . "CTRD_DT_TERMINO_VIGENCIA AS \"Fim Vigência\", "
            . "CTRD_CPFCNPJ_DESPESA AS \"CPF/CNPJ\", "
            . "CASE WHEN LENGTH(CTRD_CD_MATRICULA_EXCLUSAO) > 0 "
            . "THEN 'Excluído ' "
            . "ELSE 'Ativo' "
            . "END AS \"Status do registro\", CTRD_CD_MATRICULA_EXCLUSAO AS \"Excluído por\", CTRD_DH_EXCLUSAO_LOGICA AS \"Data da exclusão\"";

// Campos para a serem apresentados na excluirAction
        $campos ['excluir'] = "CTRD_ID_CONTRATO_DESPESA, ";
        $campos ['excluir'] .= $campos ['detalhe'];

// Campos para a serem apresentados na restaurarAction
        $campos ['restaurar'] = $campos ['excluir'];

// Campos para a serem apresentados num combo
        $campos ['combo'] = "CTRD_ID_CONTRATO_DESPESA, CTRD_NM_EMPRESA_CONTRATADA";

// Devolve os campos, conforme ação
        return $campos [$acao];
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chaves
     *        Informa a chave, já tratada, se for o caso
     * @return string
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function retornaRestricoes ($acao = 'todos', $chaves = null) {

// Condição para ação editar
        $restricao ['detalhe'] = " AND CTRD_ID_CONTRATO_DESPESA IN ( $chaves ) ";

// Condição para ação editar
        $restricao ['editar'] = $restricao ['detalhe'];

// Condição para ação excluir
        $restricao ['excluir'] = $restricao ['detalhe'];

// Condição para ação restaurar
        $restricao ['restaurar'] = $restricao ['detalhe'];

// Condição para montagem do combo
        $restricao ['combo'] = " CTRD_DH_EXCLUSAO_LOGICA IS Null ";

        return $restricao [$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais contratos
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function retornaSqlExclusaoLogica ($chaves) {

// Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

// Trata a chave primária (ou composta)
        $contratos = $this->separaChave($chaves);

// Exclui um ou mais registros
        $sql = "UPDATE CEO_TB_CTRD_CONTRATO_DESPESA "
            . "SET CTRD_CD_MATRICULA_EXCLUSAO = '$matricula', CTRD_DH_EXCLUSAO_LOGICA = SYSDATE WHERE CTRD_ID_CONTRATO_DESPESA "
            . "IN ( $contratos ) "
            . "AND CTRD_DH_EXCLUSAO_LOGICA IS Null ";

// Devolve a instrução sql para exclusão lógica
        return $sql;
    }

    /**
     * Restaura um ou mais registros logicamente excluídos
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function retornaSqlRestauracaoLogica ($chaves) {

// Trata a chave primária (ou composta)
        $contratos = $this->separaChave($chaves);

// Restaura um ou mais registros
        $sql = "UPDATE CEO_TB_CTRD_CONTRATO_DESPESA "
            . "SET CTRD_CD_MATRICULA_EXCLUSAO = Null, CTRD_DH_EXCLUSAO_LOGICA = Null "
            . "WHERE CTRD_NR_DESPESA ( $contratos ) "
            . "AND CTRD_DH_EXCLUSAO_LOGICA IS NOT Null";

// Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     * @return array
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function retornaOpcoesGrid () {

// Personaliza a exibição dos campos no grid
        $detalhes = array(
            'CTRD_ID_CONTRATO_DESPESA' => array('title' => 'Código', 'abbr' => ''),
            'CTRD_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'CTRD_NR_CONTRATO' => array('title' => 'Contrato', 'abbr' => ''),
            'CTRD_NM_EMPRESA_CONTRATADA' => array('title' => 'Empresa', 'abbr' => ''),
            'CTRD_DT_INICIO_VIGENCIA' => array('title' => 'Inicio Vigência', 'abbr' => ''),
            'CTRD_DT_TERMINO_VIGENCIA' => array('title' => 'Fim Vigência', 'abbr' => ''),
            'CTRD_CPFCNPJ_DESPESA' => array('title' => 'CNPJ', 'abbr' => ''),
            'CTRD_VL_DESPESA' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'CTRD_STATUS' => array('title' => 'Status', 'abbr' => 'Excluído?'));

// Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = array(
            'CTRD_CD_MATRICULA_EXCLUSAO',
            'CTRD_DH_EXCLUSAO_LOGICA',
        );

// Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     * @return string array
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
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

    /*
     * Retorna registro selecionado.
     */

    public function retornaRegistros ($identificador) {

        try {

            $banco = Zend_Db_Table::getDefaultAdapter();
            $stmt = $banco->prepare("SELECT DISTINCT CTRD_ID_CONTRATO_DESPESA, CTRD_NR_DESPESA, CTRD_NR_CONTRATO, CTRD_NM_EMPRESA_CONTRATADA, "
                . "CTRD_DT_INICIO_VIGENCIA, CTRD_DT_TERMINO_VIGENCIA, CTRD_CPFCNPJ_DESPESA, CTRD_VL_DESPESA "
                . "FROM CEO_TB_CTRD_CONTRATO_DESPESA, CEO_TB_VLDE_VALOR_DESPESA "
                . "WHERE CTRD_NR_DESPESA = ?");

            $stmt->execute(array(
                $identificador
            ));

            return $stmt->fetchAll();
        } catch (Exception $ex) {

            throw $ex;
        }
    }

    private function retornaOpcoesAcoesEmMassa ($gridUrl) {
        // Ação de incluisão de um registro
        $acao ['incluir'] = array('url' => $gridUrl . '/incluir/teste',
            'caption' => 'Incluir novo registro', 'confirm' => '',
            'imagem' => 'incluir');

        // Ação para exibição de detalhe
        $acao ['detalhe'] = array('url' => $gridUrl . '/detalhe/cod/',
            'caption' => 'Visualizar registro selecionado', 'confirm' => '',
            'imagem' => 'detalhe');

        // Ação de edição de único registro
        $acao ['editar'] = array('url' => $gridUrl . '/editar/cod/',
            'caption' => 'Editar registro selecionado', 'confirm' => '',
            'imagem' => 'editar');

        // Ação de exclusão de um ou mais registros
        $acao ['excluir'] = array('url' => $gridUrl . '/excluir/cod/',
            'caption' => 'Excluir um ou mais registros selecionados',
            'confirm' => '', 'imagem' => 'excluir');

        // Ação de leitura de um registro
        $acao ['leitura'] = array('url' => $gridUrl . '/leitura/cod/',
            'caption' => 'Ler o informativo selecionado',
            'confirm' => '', 'imagem' => 'leitura');

        $msgCaption = 'Restaurar um ou mais registros logicamente excluídos';
        // Ação de restauração de um ou mais registros logicamente excluídos
        $acao ['restaurar'] = array('url' => $gridUrl . '/restaurar/cod/',
            'caption' => $msgCaption, 'confirm' => '',
            'imagem' => 'restaurar');

        // Ação de importar
        $acao ['importar'] = array('url' => $gridUrl . '/importar',
            'caption' => 'Importar dados', 'confirm' => '',
            'imagem' => 'importar');

        // Devolve as opções das ações em massa
        return $acao;
    }

}
