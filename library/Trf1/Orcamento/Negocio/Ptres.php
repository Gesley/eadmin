<?php

/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Ptres
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - PTRES
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
class Trf1_Orcamento_Negocio_Ptres
{

    /**
     * Model dos Ptres
     */
    protected $_dados = null;

    /**
     * Classe construtora
     * 
     * @param	none
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct ()
    {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbPtresPrograma();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return	array		Chave primária ou composta
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela ()
    {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return	array		Chave primária ou composta
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria ()
    {
        return $this->_dados->chavePrimaria ();
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param	none
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaCombo ()
    {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo ( 'ptres' );
        $dados = $cache->lerCache ( $cacheId );

        if( $dados === false ) {
            //Não existindo o cache, busca do banco
            $sql = "
SELECT
	PTRS_CD_PT_RESUMIDO,
	PTRS_CD_UNID_ORCAMENTARIA,
	PTRS_CD_PT_COMPLETO,
	PTRS_SG_PT_RESUMIDO,
	PTRS_DS_PT_RESUMIDO
FROM
	CEO_TB_PTRS_PROGRAMA_TRABALHO
WHERE
	PTRS_DH_EXCLUSAO_LOGICA IS NULL
ORDER BY
	PTRS_CD_PT_RESUMIDO
			";

            $banco = Zend_Db_Table::getDefaultAdapter ();

            $dados = $banco->fetchPairs ( $sql );

            // Cria o cache
            $cache->criarCache ( $dados, $cacheId );
        }

        return $dados;
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param	none
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaListagem ()
    {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->gerarID_Listagem ( 'ptres' );
        $dados = $cache->lerCache ( $cacheId );

        if( $dados === false ) {
            //Não existindo o cache, busca do banco
            $sql = "
SELECT
    PTRS_AA_EXERCICIO,
	PTRS_CD_PT_RESUMIDO,
	PTRS_SG_PT_RESUMIDO,
	PTRS_DS_PT_RESUMIDO,
	PTRS_CD_PT_COMPLETO,
	PTRS_CD_UNID_ORCAMENTARIA
FROM
	CEO_TB_PTRS_PROGRAMA_TRABALHO
WHERE
	PTRS_DH_EXCLUSAO_LOGICA					IS Null
				";

            $banco = Zend_Db_Table::getDefaultAdapter ();

            $dados = $banco->fetchAll ( $sql );

            // Cria o cache
            $cache->criarCache ( $dados, $cacheId );
        }

        return $dados;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param	int		$evento				Chave primária para busca do registro
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistro ( $ptres )
    {
        $sql = "
SELECT
    PTRS_AA_EXERCICIO,
	PTRS_CD_PT_RESUMIDO,
	PTRS_SG_PT_RESUMIDO,
	PTRS_DS_PT_RESUMIDO,
	PTRS_CD_PT_COMPLETO,
	PTRS_CD_UNID_ORCAMENTARIA
FROM
	CEO_TB_PTRS_PROGRAMA_TRABALHO
WHERE
	PTRS_CD_PT_RESUMIDO					= $ptres		AND
	PTRS_DH_EXCLUSAO_LOGICA				IS Null
				";
        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchRow ( $sql );
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param	int		$evento				Chave primária para busca do registro
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistroNomeAmigavel ( $ptres )
    {
        $sql = "
SELECT
    PTRS_AA_EXERCICIO                   AS \"Ano\",
	PTRS_CD_PT_RESUMIDO					AS \"Programa de trabalho resumido\",
	PTRS_DS_PT_RESUMIDO					AS \"Descrição\",
	PTRS_SG_PT_RESUMIDO					AS \"Sigla\",
	PTRS_CD_PT_COMPLETO					AS \"Programa de trabalho\",
	PTRS_CD_UNID_ORCAMENTARIA			AS \"Unidade orçamentária\"
FROM
	CEO_TB_PTRS_PROGRAMA_TRABALHO
WHERE
	PTRS_CD_PT_RESUMIDO		 			IN ($ptres)	AND
	PTRS_DH_EXCLUSAO_LOGICA				IS Null
				";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchRow ( $sql );
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param	array	$chaves				Array de chaves primárias para busca de um ou mais registros
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaVariosRegistros ( $chaves )
    {
        $ptres = implode ( ', ', $chaves );

        $sql = "
SELECT
	PTRS_CD_PT_RESUMIDO,
	PTRS_CD_PT_RESUMIDO					AS \"Programa de trabalho resumido\",
	PTRS_SG_PT_RESUMIDO					AS \"Sigla\",
	PTRS_DS_PT_RESUMIDO					AS \"Descrição\",
	PTRS_CD_PT_COMPLETO					AS \"Programa de trabalho\",
	PTRS_CD_UNID_ORCAMENTARIA			AS \"Unidade orçamentária\"
FROM
	CEO_TB_PTRS_PROGRAMA_TRABALHO
WHERE
	PTRS_CD_PT_RESUMIDO		 			IN ($ptres)	AND
	PTRS_DH_EXCLUSAO_LOGICA				IS Null
				";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchAll ( $sql );
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
        $ptres = implode ( ', ', $chaves );

        $sessao = new Zend_Session_Namespace ( 'userNs' );

        // Exclui um ou mais registros
        $sql = "
UPDATE
	CEO_TB_PTRS_PROGRAMA_TRABALHO
SET
	PTRS_CD_MATRICULA_EXCLUSAO			= '$sessao->matricula',
	PTRS_DH_EXCLUSAO_LOGICA		    	= SYSDATE
WHERE
	PTRS_CD_PT_RESUMIDO					IN ($ptres)	AND
	PTRS_DH_EXCLUSAO_LOGICA				IS Null
				";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        $banco->query ( $sql );
    }

    /**
     * Função para buscar um dado PTRES, apartir de pelo menos um dos campos da
     * consulta (código, sigla, descrição ou código completo) para exibição em
     * campos, tipicamente populados via ajax, nas diversas funcionalidades do
     * sistema.
     *
     * @param string $texto
     *               Texto digitado pelo usuário para busca dos dados sobre 
     *               um ou mais PTRES
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function getPtresAjax ( $texto )
    {
        $novoNegocio = new Orcamento_Business_Negocio_Ptres ();
        $dados = $novoNegocio->getPtresAjax ( $texto );
        return $dados;
    }

}
