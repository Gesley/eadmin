<?php

/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Exercicio
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Gesley B Rodrigues [rodrigues.gesley@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Exercicio
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * Descrever...
 * 
 */
class Trf1_Orcamento_Negocio_Exercicio
{

    /**
     * Model dos Tipos de Recursos
     */
    protected $_dados = null;

    /**
     * Classe construtora
     * 
     * @param	none
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function __construct ()
    {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbAnoExercicio ();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return	array		Chave primária ou composta
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function chavePrimaria ()
    {
        return $this->_dados->chavePrimaria ();
    }

    /**
     * Retorna a tabela principal desta classe negocial
     *
     * @return	array		Chave primária ou composta
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function tabela ()
    {
        return $this->_dados;
    }

    public function retornaListagemSimplificada ()
    {
        $camposDesejados = array (
            'ANOE_AA_ANO',
            'ANOE_DS_OBSERVACAO',
            'FASE_NM_FASE_EXERCICIO'
        );

        $listagem = $this->_retornaListagemBase ( $camposDesejados );

        return $listagem;
    }

    /**
     * Apresenta todos os campos da(s) despesa(s) informada(s)
     *
     * @param	array	$chaves
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaVariosRegistros ( $chaves )
    {
        $exercicios = implode ( ', ', $chaves );

        $sql = $this->_retornaQueryCompleta ( $exercicios );

        $banco = Zend_Db_Table::getDefaultAdapter ();

        $dados = $banco->fetchAll ( $sql );

        return $dados;
    }

    private function _retornaListagemBase ( $camposDesejados )
    {
        if( !$camposDesejados ) {
            return array ();
        }

        $dadosBase = $this->retornaDadosCompletos ();

        $camposExcluir = array_diff ( $this->_retornaCampos (), $camposDesejados );

        $dados = array ();
        foreach ( $dadosBase as $registro ) {
            // Elimina campos não desejados
            foreach ( $camposExcluir as $campo ) {
                unset ( $registro [ $campo ] );
            }

            $dados [] = $registro;
        }

        return $dados;
    }

    /**
     * Retorna array contendo todos os campos das exercicios ativas no banco
     *
     * @param	int		$exercicio
     * @return	array
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaDadosCompletos ()
    {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->gerarID_Listagem ( 'exercicio' );
        $dados = $cache->lerCache ( $cacheId );

        if( $dados === false ) {
            //Não existindo o cache, busca do banco
            $sql = $this->_retornaQueryCompleta ();

            $banco = Zend_Db_Table::getDefaultAdapter ();

            $dados = $banco->fetchAll ( $sql );

            $cache->criarCache ( $dados, $cacheId );
        }

        return $dados;
    }

    private function _retornaQueryCompleta ( $exercicio = null )
    {
        // Valida parâmetros
        $condExercicio = "";
        if( $exercicio ) {
            $condExercicio = " AND A.ANOE_AA_ANO = $exercicio";
        }
        $sql = "
        SELECT DISTINCT
            A.ANOE_AA_ANO,
            A.ANOE_DS_OBSERVACAO AS \"Descrição\",
            E.FASE_NM_FASE_EXERCICIO AS \"Status\"
        FROM ceo_tb_anoe_ano_exercicio A
        INNER JOIN CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO F
            ON F.FANE_NR_ANO = A.ANOE_AA_ANO
        INNER JOIN CEO.CEO_TB_FASE_FASE_EXERCICIO E
            ON E.FASE_ID_FASE_EXERCICIO = F.FANE_ID_FASE_EXERCICIO
        WHERE A.ANOE_DH_EXCLUSAO_LOGICA IS NULL
        $condExercicio

        ORDER BY A.ANOE_AA_ANO DESC";
        return $sql;
    }

    private function _retornaCampos ()
    {
        $campos = array (
            'ANOE_AA_ANO',
            'ANOE_DS_OBSERVACAO',
            'FASE_NM_FASE_EXERCICIO'
        );

        return $campos;
    }

    /**
     * Apresenta todos os campos do exercicio informada
     *
     * @param	int $exercicio
     * @return	array
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaExercicio ( $exercicio )
    {
        if( !$exercicio ) {
            throw new Exception ( 'Código do exercicio é obrigatório' );
        }

        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Despesa ( $exercicio );
        $dados = $cache->lerCache ( $cacheId );

        if( $dados === false ) {
            $sql = $this->_retornaQueryCompleta ( $exercicio );
            $banco = Zend_Db_Table::getDefaultAdapter ();
            $dados = $banco->fetchRow ( $sql );
            $cache->criarCache ( $dados, $cacheId );
        }
        return $dados;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param	int $exercicio Ano Exercicio
     * @param   int $status Status do Ano Exercicio
     * @return	array
     * @author	Gesley B Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaRegistro ( $exercicio, $status = null )
    {
        $condStatus = "";
        if( $status ) {
            $condStatus = " AND E.FASE_ID_FASE_EXERCICIO = $status";
        }
        $sql = "
        SELECT DISTINCT
            A.ANOE_AA_ANO,
            A.ANOE_DS_OBSERVACAO,
            E.FASE_ID_FASE_EXERCICIO,
            E.FASE_NM_FASE_EXERCICIO
        FROM ceo_tb_anoe_ano_exercicio A
        INNER JOIN CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO F
            ON F.FANE_NR_ANO = A.ANOE_AA_ANO
        INNER JOIN CEO.CEO_TB_FASE_FASE_EXERCICIO E
            ON E.FASE_ID_FASE_EXERCICIO = F.FANE_ID_FASE_EXERCICIO
        WHERE A.ANOE_DH_EXCLUSAO_LOGICA IS NULL
        AND A.ANOE_AA_ANO = $exercicio
        $condStatus
        ";
        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchRow ( $sql );
    }

    /**
     * Realiza a exclusão lógica de uma unidade gestora
     *
     * @param	array	$chaves				Array de chaves primárias para exclusão de um ou mais registros
     * @return	none
     * @author	Dayane Freire / Robson Pereira
     */
    public function exclusaoLogica ( $chaves )
    {
        $despesas = implode ( ', ', $chaves );

        $sessao = new Zend_Session_Namespace ( 'userNs' );
        // Exclui um ou mais registros
        $sql = "
        UPDATE
            CEO_TB_ANOE_ANO_EXERCICIO
        SET
            ANOE_CD_MATRICULA_EXCLUSAO = '$sessao->matricula',
            ANOE_DH_EXCLUSAO_LOGICA    = SYSDATE
        WHERE   
            ANOE_AA_ANO IN ($despesas)		
                AND
            ANOE_DH_EXCLUSAO_LOGICA IS NULL
	";

        $banco = Zend_Db_Table::getDefaultAdapter ();
        $banco->query ( $sql );
    }

}
