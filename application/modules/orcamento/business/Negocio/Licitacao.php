    <?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 */

/**
 * Contém as regras negociais sobre Fase de LICTação
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Esfera
 *
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Licitacao extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     *
     */
    public function init() {

        // Instancia a classe model

        $this->_model = new Orcamento_Model_DbTable_Lict();

        // Define a negocio
        $this->_negocio = 'licitacao';

    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     *
     */
    public function retornaCampos($acao = 'todos') {

        // Campos para a serem apresentados na indexAction
        $campos['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos['index'] = "
        LICT_ID_LICITACAO,
        LICT_CD_LICITACAO,
        CASE WHEN LENGTH(LICT_CD_MATRICULA_EXCLUSAO) > 0
        THEN 'Excluído '
        ELSE 'Ativo'
        END AS FASL_STATUS
        ";

        // Campos para a serem apresentados na editarAction
        $campos['editar'] = $campos['index'];

        // Campos para a serem apresentados na detalheAction

        $campos['detalhe'] = "
        LICT_NR_DESPESA AS \"Número despesa\",
        LICT_CD_FASE_LICTACAO AS \"Código da fase\",
        CASE WHEN LENGTH(LICT_CD_MATRICULA_EXCLUSAO) > 0
        THEN 'Excluído '
        ELSE 'Ativo'
        END AS \"Status do registro\",
        LICT_CD_MATRICULA_EXCLUSAO AS \"Excluído por\",
        LICT_DH_EXCLUSAO_LOGICA AS \"Data da exclusão\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos['excluir'] = "LICT_ID_LICITACAO, ";
        $campos['excluir'] .= $campos['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos['restaurar'] = $campos['excluir'];

        // Campos para a serem apresentados num combo
        $campos['combo'] = "
        LICT_CD_FASE_LICTACAO,
        ";

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
     *
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {

        // Condição para ação editar
        $restricao['detalhe'] = " AND LICT_ID_LICITACAO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao['editar'] = $restricao['detalhe'];

        // Condição para ação excluir
        $restricao['excluir'] = $restricao['detalhe'];

        // Condição para ação restaurar
        $restricao['restaurar'] = $restricao['detalhe'];

        // Condição para montagem do combo
        $restricao['combo'] = " LICT_DH_EXCLUSAO_LOGICA IS Null ";

        return $restricao[$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais fase LICTações
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     *
     */
    public function retornaSqlExclusaoLogica($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $licitacao = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
        UPDATE
            CEO_TB_LICT_LICITACAO
        SET
            LICT_CD_MATRICULA_EXCLUSAO          = '$matricula',
            LICT_DH_EXCLUSAO_LOGICA             = SYSDATE
        WHERE
            LICT_ID_LICITACAO IN ( $licitacao) AND
            LICT_DH_EXCLUSAO_LOGICA IS Null
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
     *
     */
    public function retornaSqlRestauracaoLogica($chaves) {

        // Trata a chave primária (ou composta)
        $licitacao = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
        UPDATE
            CEO_TB_LICT_LICITACAO
        SET
            LICT_CD_MATRICULA_EXCLUSAO          = Null,
            LICT_DH_EXCLUSAO_LOGICA             = Null
        WHERE
            LICT_ID_LICITACAO                      IN ($licitacao) AND
            LICT_DH_EXCLUSAO_LOGICA             IS NOT Null
            ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     *
     */
    public function retornaOpcoesGrid() {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'LICT_ID_LICITACAO' => array('title' => 'LICTação', 'abbr' => ''),
            'LICT_CD_LICITACAO' => array('title' => 'Descrição', 'abbr' => ''),
            'LICT_STATUS' => array('title' => 'Status',
                'abbr' => 'Informa se o registro foi ou não excluído'));

        // Combina as opções num array
        $opcoes['detalhes'] = $detalhes;

        $opcoes['controle'] = $this->_negocio;
        $opcoes['ocultos'] = array('CAMPO_NAO_EXISTENTE');

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     *
     */
    public function retornaCacheIds($acao = null) {
        // Instancia o cache
        $cache = new Trf1_Cache();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listage'm
        $id['index'] = $cache->retornaID_Listagem('orcamento', $negocio);

        // Id para combo
        $id['combo'] = $cache->retornaID_Combo('orcamento', $negocio);

        // Determina qual valor será retornado
        $retorno = ($acao != null ? $id[$acao] : $id);

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Retorna combo de fase da licitação
     */
    public function retornaCombo() {

        try {

            $banco = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT FASL_CD_FASE, FASL_DS_FASE FROM CEO_TB_FASL_FASE_PROC_LICIT ORDER BY FASL_CD_FASE";
            $dados = $banco->fetchAll($sql);

            // remove chaves do array para apresentação no form
            foreach ($dados as $key => $value) {

                $retorno[$value['FASL_CD_FASE']] = $value['FASL_DS_FASE'];
            }

            return $retorno;

        } catch (Exception $ex) {

            throw $ex;
        }

    }

    /**
     * Salva fase da licitação
     *
     * @param      <array>  $fase fase de licitação
     * @param      <array>  $dados dados da licitação
     */
    public function salvar($fase, $dados) {

        try {

            $banco = Zend_Db_Table::getDefaultAdapter();

            // verifica se já existe uma fase para a licitação corrente.
            $sql = "SELECT * FROM CEO_TB_LICT_LICITACAO WHERE LICT_NR_DESPESA = " . $dados['NR_DESPESA']
                . "AND LICT_AA_DESPESA = " . $dados['DESP_AA_DESPESA'];

            $retorno = $banco->fetchRow($sql);

            $banco->beginTransaction();

            // verifica se inclui uma fase ou se altera uma existente.
            if ($retorno) {

                $stmt = $banco->prepare("UPDATE CEO_TB_LICT_LICITACAO "
                    . "SET LICT_AA_DESPESA = ?, LICT_CD_FASE_LICTACAO = ? "
                    . "WHERE LICT_NR_DESPESA = ? AND LICT_AA_DESPESA = ?");

                $stmt->execute(array(

                    $dados['DESP_AA_DESPESA'],
                    $fase['FASL_CD_FASE'],
                    $dados['NR_DESPESA'],
                    $dados['DESP_AA_DESPESA'],
                ));

                return $banco->commit();

            } else {

                $stmt = $banco->prepare("INSERT INTO CEO_TB_LICT_LICITACAO "
                    . "(LICT_ID_LICITACAO, LICT_NR_DESPESA, LICT_AA_DESPESA, LICT_CD_FASE_LICTACAO) "
                    . "VALUES (CEO_SQ_LICT.nextval,?,?,?)");

                $stmt->execute(array(

                    $dados['NR_DESPESA'],
                    $dados['DESP_AA_DESPESA'],
                    $fase['FASL_CD_FASE'],
                ));

                return $banco->commit();

            }

        } catch (Exception $ex) {

            $banco->rollBack();
            throw $ex;
        }
    }

    /**
     * Função para excluir logicamente fase de licitação
     *
     * @param      <array>  $dados Dados de Licitação
     */
    public function excluir($dados) {

        try {

            // Busca matrícula do usuário logado
            $sessao = new Zend_Session_Namespace('userNs');
            $matricula = strtolower($sessao->matricula);

            // Banco.
            $banco = Zend_Db_Table::getDefaultAdapter();
            $banco->beginTransaction();

            // percorre os arrays para a deleçao.
            foreach ($dados as $key) {

                // verifica se já existe uma fase para a licitação corrente.
                $sql = "SELECT * FROM CEO_TB_LICT_LICITACAO WHERE LICT_NR_DESPESA = " . $key['NR_DESPESA']
                    . "AND LICT_AA_DESPESA = " . $key['DESP_AA_DESPESA'];

                $retorno = $banco->fetchRow($sql);

                // verifica se a fase existe antes de excluir.
                if ($retorno) {

                    $stmt = $banco->prepare("UPDATE CEO_TB_LICT_LICITACAO "
                        . "SET LICT_CD_MATRICULA_EXCLUSAO = ?, LICT_DH_EXCLUSAO_LOGICA = SYSDATE "
                        . "WHERE LICT_ID_LICITACAO = ?"
                    );

                    $stmt->execute(array(

                        $matricula,
                        $key['LICT_ID_LICITACAO'],
                    ));

                } else {

                    $stmt = $banco->prepare("INSERT INTO CEO_TB_LICT_LICITACAO "
                        . "(LICT_ID_LICITACAO, LICT_NR_DESPESA, LICT_AA_DESPESA, LICT_CD_FASE_LICTACAO, "
                        . "LICT_CD_MATRICULA_EXCLUSAO, LICT_DH_EXCLUSAO_LOGICA) "
                        . "VALUES (CEO_SQ_LICT.nextval,?,?,?,?, SYSDATE)"
                    );

                    $stmt->execute(array(

                        $key['NR_DESPESA'],
                        $key['DESP_AA_DESPESA'],
                        '1',
                        $matricula,
                    ));

                }
            }

            return $banco->commit();

        } catch (Exception $ex) {

            $banco->rollBack();
            throw $ex;
        }
    }

}
