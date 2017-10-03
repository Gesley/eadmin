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
 * Contém as regras negociais sobre permissãos
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Permissao
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Permissao extends Orcamento_Business_Negocio_Base {
    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Perm();

        // Define a negocio
        $this->_negocio = 'permissao';
    }

    /**
     * trata os dados antes da inclusao
     * @param array $dados
     * @return NULL
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluir($dados) {
        unset($dados["CH_UG"]);
        unset($dados["CH_RESP"]);
        unset($dados["CAMPUSUARIO"]);
        unset($dados["AUTO_CP_RESPONSABILIDADE"]);
        unset($dados["Enviar"]);

        return parent::incluir($dados); // TODO: Change the autogenerated stub
    }

    /**
     * trata os dados antes da edição
     * @param array $dados
     * @return NULL
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editar($dados) {

        // matriculas maiusculas
        $matricula = $this->tratamatricula($dados["PERM_CD_MATRICULA"]);
        $dados["PERM_CD_MATRICULA"] = strtoupper($matricula);

        // trata a unidade gestora ( todos )
        if ($dados['CH_UG'] == '1') {
            $dados["PERM_CD_UNIDADE_GESTORA"] = '99999';
        }
        unset($dados['CH_UG']);

        // trata a responsabilidade ( todos )
        if ($dados['CH_RESP'] == '1') {
            $dados['PERM_DS_RESPONSABILIDADE'] = 'todos';
        }
        unset($dados['CH_RESP']);
        unset($dados["CAMPUSUARIO"]);

        unset($dados["Enviar"]);

        return parent::editar($dados); // TODO: Change the autogenerated stub
    }

    /*
     * Retorna as condições restritivas, se houver INNER JOIN
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaJoins($acao = 'todos') {

        $join['index'] = "
              LEFT JOIN OCS_TB_PMAT_MATRICULA PMAT
                  ON PERM_CD_MATRICULA = PMAT_CD_MATRICULA
              LEFT JOIN OCS_TB_PJUR_PESSOA_JURIDICA PJUR
                  ON PJUR_ID_PESSOA = PMAT_ID_PESSOA
              LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                  ON PNAT_ID_PESSOA = PMAT_ID_PESSOA
              LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE
                  ON UNGE_CD_UG = PERM_CD_UNIDADE_GESTORA
        ";

        $join['detalhe'] = $join['index'];
        $join['editar'] = $join['index'];
        $join['excluir'] = $join['index'];

        return $join[$acao];
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
    public function retornaCampos($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos['index'] = "
PERM_ID_PERMISSAO_ACESSO,
PERM_CD_MATRICULA,
PNAT_NO_PESSOA,
INITCAP(PERM_DS_PERFIL) AS PERM_DS_PERFIL,
UNGE_SG_UG,
INITCAP(PERM_DS_RESPONSABILIDADE) AS PERM_DS_RESPONSABILIDADE,
CASE WHEN LENGTH(PERM_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS PERM_STATUS
                                ";

        // Campos para a serem apresentados na editarAction
        $campos['editar'] = "
        PERM_ID_PERMISSAO_ACESSO,
PERM_CD_MATRICULA ||' - '|| PNAT_NO_PESSOA AS PERM_CD_MATRICULA,
PERM_DS_PERFIL,
PERM_CD_UNIDADE_GESTORA,
PERM_DS_RESPONSABILIDADE,
CASE WHEN LENGTH(PERM_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS PERM_STATUS
        ";

        // Campos para a serem apresentados na detalheAction
        $campos['detalhe'] = "
PERM_ID_PERMISSAO_ACESSO                  AS \"Código\",
PERM_CD_MATRICULA                         AS \"Matricula\",
PNAT_NO_PESSOA                            AS \"Usuário\",
PERM_DS_PERFIL                            AS \"Perfil\",
UNGE_SG_UG                                AS \"UG\",
PERM_DS_RESPONSABILIDADE                  AS \"Responsavel\"
";

        // Campos para a serem apresentados na excluirAction
        $campos['excluir'] = "PERM_ID_PERMISSAO_ACESSO, ";
        $campos['excluir'] .= $campos['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos['restaurar'] = $campos['excluir'];

        // Campos para a serem apresentados num combo
        $campos['combo'] = "
PERM_ID_PERMISSAO_ACESSO,
PERM_DS_RESPONSABILIDADE
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        // Condição pra acao index
        $restricao['index'] = " AND PERM_CD_MATRICULA_EXCLUSAO IS NULL
                                AND PERM_DH_EXCLUSAO_LOGICA IS NULL
                              ";

        // Condição para ação editar
        $restricao['detalhe'] = " AND PERM_ID_PERMISSAO_ACESSO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao['editar'] = $restricao['detalhe'];

        // Condição para ação excluir
        $restricao['excluir'] = $restricao['detalhe'];

        // Condição para ação restaurar
        $restricao['restaurar'] = $restricao['detalhe'];

        // Condição para montagem do combo
        $restricao['combo'] = " PERM_DH_EXCLUSAO_LOGICA IS Null ";

        return $restricao[$acao];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais esferas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica($chaves) {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula();

        // Trata a chave primária (ou composta)
        $permissoes = $this->separaChave($chaves);

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PERM_PERMISSAO_ACESSO
SET
    PERM_CD_MATRICULA_EXCLUSAO          = '$matricula',
    PERM_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    PERM_ID_PERMISSAO_ACESSO                      IN ( $permissoes ) AND
    PERM_DH_EXCLUSAO_LOGICA             IS Null
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
    public function retornaSqlRestauracaoLogica($chaves) {
        // Trata a chave primária (ou composta)
        $permissoes = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PERM_PERMISSAO_ACESSO
SET
    PERM_CD_MATRICULA_EXCLUSAO          = Null,
    PERM_DH_EXCLUSAO_LOGICA             = Null
WHERE
    PERM_ID_PERMISSAO_ACESSO                      IN ( $permissoes ) AND
    PERM_DH_EXCLUSAO_LOGICA             IS NOT Null
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
    public function retornaOpcoesGrid() {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'PERM_ID_PERMISSAO_ACESSO' => array('title' => 'Código', 'abbr' => ''),
            'PERM_CD_MATRICULA' => array('title' => 'Matricula', 'abbr' => ''),
            'PNAT_NO_PESSOA' => array('title' => 'Usuário', 'abbr' => ''),
            'PERM_DS_PERFIL' => array('title' => 'Perfil', 'abbr' => ''),
            'UNGE_SG_UG' => array('title' => 'UG', 'abbr' => ''),
            'PERM_DS_RESPONSABILIDADE' => array('title' => 'Responsavel', 'abbr' => ''),
            'PERM_STATUS' => array('title' => 'Status', 'abbr' => 'Informa se o registro foi ou não excluído'),
        );

        // Combina as opções num array
        $opcoes['detalhes'] = $detalhes;
        $opcoes['controle'] = $this->_negocio;
        $opcoes['ocultos'] = array('PERM_ID_PERFIL');

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
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
     * Verifica se o usuário já tem permissao
     * @param string $matricula
     * @return mixed
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function verificaDuplicidade($matricula) {
        $facadePerm = new Orcamento_Facade_Permissao();
        return $facadePerm->verificaDuplicidade($matricula);
    }

    /**
     * retorna todos usuários da tabela de permissao
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaUsuarios() {
        $facadePerm = new Orcamento_Facade_Permissao();
        return $facadePerm->retornaUsuarios();
    }

    /**
     * retorna um usuário da tabela de rh
     * @param $matricula
     * @return mixed
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaUsuarioPorMatricula($matricula) {
        $facadePerm = new Orcamento_Facade_Permissao();
        return $facadePerm->retornaUsuarioPorMatricula($matricula);
    }

    /**
     * Retorna a matricula sem nome
     * @param $matricula
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function tratamatricula($matricula) {
        $posicao = strpos($matricula, '-');
        $newmatricula = substr($matricula, 0, ($posicao - 1));
        return trim($newmatricula);
    }

    public function retornaComboResponsavel($dado) {

        $sql = "SELECT DISTINCT LOTA_SIGLA_LOTACAO, LOTA_SIGLA_LOTACAO||' - '||LOTA_DSC_LOTACAO as LABEL "
            . "FROM SARH.RH_CENTRAL_LOTACAO "
            . "WHERE LOTA_SIGLA_LOTACAO LIKE UPPER('%$dado%') "
            . "ORDER BY LOTA_SIGLA_LOTACAO ASC"
        ;

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

}