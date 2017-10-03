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
 * Contém as regras negociais sobre esfera
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Elementosregra
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Elementosregra extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Elemregr ();

        // Define a negocio
        $this->_negocio = 'elementosregra';
    }

    /**
     * retorna os elementos de regra de acordo com a regra
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaElementos($exercicio)
    {
        $sql = "
SELECT * FROM CEO_TB_ELRG_ELEMENTOS_REGRA
WHERE ELRG_ID_REGRA_EXERCICIO = $exercicio";
        return $this->executaQuery($sql);
    }

    /**
     * Exclui os elementos de uma regra
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluiElementos($regra)
    {
        $sql = "
DELETE FROM CEO_TB_ELRG_ELEMENTOS_REGRA
WHERE ELRG_ID_REGRA_EXERCICIO = $regra";
        return $this->executaQuery($sql);
    }

    /*
     * Trata os dados para inclusao de elementos de regra
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */

    public function incluirRegra($idregra, $dados)
    {
        // seta null para os campos vazios
        ($dados[1] == "") ? $dados[1] = "NULL" : $dados[1];
        ($dados[2] == "") ? $dados[2] = "NULL" : $dados[2];

        $sqls = "
INSERT INTO CEO_TB_ELRG_ELEMENTOS_REGRA
( 
    ELRG_ID_ELEMEN_REGRA, 
    ELRG_ID_REGRA_EXERCICIO,     
    ELRG_CD_TIPO_DESPESA,        
    ELRG_CD_PT_RESUMIDO,          
    ELRG_CD_ELEMENTO_DESPESA_SUB 
 )
 VALUES
 (
    CEO_SQ_ELRG.NEXTVAL,
    $idregra,
    $dados[2],
    $dados[0],
    $dados[1]
 )";
        return $this->executaQuery($sqls);
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
    public function retornaCampos($acao = 'todos')
    {
        // Campos para a serem apresentados na indexAction
        $campos ['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos ['index'] = "
RGEX_ID_REGRA_EXERCICIO,
RGEX_DS_REGRA_EXERCICIO,
CONCAT(RGEX_VL_PERCENTUAL,'%') AS RGEX_VL_PERCENTUAL,
CASE WHEN LENGTH ( RGEX_CD_MATRICULA_EXCLUSAO ) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END AS RGEX_CD_MATRICULA_EXCLUSAO
                                ";

        // Campos para a serem apresentados na editarAction
        $campos ['editar'] = $campos ['index'];

        // Campos para a serem apresentados na detalheAction
        $campos ['detalhe'] = "
RGEX_ID_REGRA_EXERCICIO             AS \"Código\",
RGEX_DS_REGRA_EXERCICIO                  AS \"Descrição\",
RGEX_VL_PERCENTUAL                  AS \"Percentual\",
CASE WHEN LENGTH(RGEX_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS \"Situação da Regra\",
RGEX_CD_MATRICULA_EXCLUSAO      AS \"Excluído por\"
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
    public function retornaRestricoes($acao = 'todos', $chaves = null)
    {
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
    public function retornaSqlExclusaoLogica($chaves)
    {
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
    ESFE_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    RGEX_ID_REGRA_EXERCICIO                      IN ( $regras ) 
    AND ESFE_DH_EXCLUSAO_LOGICA             IS Null
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
    public function retornaSqlRestauracaoLogica($chaves)
    {
        // Trata a chave primária (ou composta)
        $regras = $this->separaChave($chaves);

        // Restaura um ou mais registros
        $sql = "
UPDATE 
    CEO_TB_RGEX_REGRA_EXERCICIO
SET RGEX_CD_MATRICULA_EXCLUSAO = Null,
    ESFE_DH_EXCLUSAO_LOGICA    = Null
WHERE RGEX_ID_REGRA_EXERCICIO IN ( $regras ) 
    AND ESFE_DH_EXCLUSAO_LOGICA    IS NOT Null";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaOpcoesGrid()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
'RGEX_ID_REGRA_EXERCICIO' => array('title' => 'Regra', 'abbr' => ''),
'RGEX_DS_REGRA_EXERCICIO' => array('title' => 'Descrição', 'abbr' => ''),
'RGEX_VL_PERCENTUAL' => array('title' => 'Percentual', 'abbr' => ''),
'RGEX_CD_MATRICULA_EXCLUSAO' => array('title' => 'Situação da Regra', 'abbr' => 'Informa se o registro foi ou não excluído'));

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        $opcoes ['controle'] = $this->_negocio;

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCacheIds($acao = null)
    {
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

}
