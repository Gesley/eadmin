<?php

/**
 * @category	TRF1
 * @package		Trf1_Rh_Negocio_Lotacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre lotações no rh
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Rh_Negocio_Lotacao
{

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Adapter_Abstract $_db
     */
    protected $_db;

    /**
     * Armazena os dados da sessão do usuário
     *
     * @var object $_userNs
     */
    protected $_userNs;

    /**
     * Armazena a classe de cache
     *
     * @var Trf1_Orcamento_Cache $_cache
     */
    protected $_cache;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct()
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_cache = new Trf1_Cache();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    /**
     * Efetua consulta da lotação de uma pessoa especifica
     * 
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * @param string $matricula
     * @return array
     */
    public function getLotacaoPorMatricula($matricula)
    {
        return $this->_db->fetchAll("SELECT PNAT_NO_PESSOA,
                                    PMAT_CD_MATRICULA,
                                    LOTA_SIGLA_SECAO,
                                    LOTA_COD_LOTACAO
                            FROM RH_CENTRAL_LOTACAO,
                                OCS_TB_PMAT_MATRICULA,
                                OCS_TB_PNAT_PESSOA_NATURAL
                            WHERE LOTA_COD_LOTACAO = PMAT_CD_UNIDADE_LOTACAO 
                                AND LOTA_SIGLA_SECAO = PMAT_SG_SECSUBSEC_LOTACAO
                                AND PNAT_ID_PESSOA = PMAT_ID_PESSOA
                                AND PMAT_CD_MATRICULA = '".$matricula."'
                                AND PMAT_DT_FIM IS NULL
                                AND LOTA_DAT_FIM IS NULL");
    }

    /**
     * Retorna o nome da família da lotação
     * 
     * @param string $sigla
     * @param int $cod
     * @return string
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getFamiliaLotacao($sigla, $cod)
    {
        $row = $this->_db->fetchRow('SELECT RH_SIGLAS_FAMILIA_CENTR_LOTA(?,?) FAMILIA_LOTACAO FROM DUAL', array($sigla, $cod));
        return $row['FAMILIA_LOTACAO'];
    }

    /**
     * retorna um array com as secoes e trf
     * @return array
     */
    public function retornaCacheTrfSecao()
    {
        return $this->retornaCache(Trf1_Rh_Definicoes::CACHE_TRF_E_SECOES);
    }

    /**
     * retorna um array de subsecoes agrupadas pelo id do trf ou secao
     * @return array
     */
    public function retornaCacheSubsecao()
    {
        return $this->retornaCache(Trf1_Rh_Definicoes::CACHE_SUBSECOES);
    }

    /**
     * Retorna um array com as unidades agrupadas por id da subsecao
     * @return array
     */
    public function retornaCacheUnidadePorSubsecao()
    {
        return $this->retornaCache(Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SUBSECAO);
    }

    /**
     * Retorna os dados de todas as unidades
     * @return array
     */
    public function retornaCacheUnidade()
    {
        return $this->retornaCache(Trf1_Rh_Definicoes::CACHE_UNIDADES);
    }

    /**
     * Retorna os dados das unidades da minha secao
     * @return array
     */
    public function retornaCacheUnidadeDaMinhaSecao()
    {
        $unidadesPorSecao = $this->retornaCacheUnidadePorSiglaSecao();
        //retorno todas as unidades que sejam da minha secao (faço cast para converter caso null o valor para array)
        return (array) $unidadesPorSecao[$this->_userNs->siglasecao];
    }

    /**
     * Retorna um array com todas as unidades agrupadas por sigla da secao
     * @return array
     */
    public function retornaCacheUnidadePorSiglaSecao()
    {
        return $this->retornaCache(Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SIGLA_SECAO);
    }

    /**
     * Retorna combo Unidade
     * @return type
     */
    private function retornaComboUnidade($sigla, $codigo, $tipo)
    {
        $sql = "
            SELECT 
                LOTA_SIGLA_SECAO||'|'||LOTA_COD_LOTACAO --INDEX DO ARRAY
                ,LOTA_SIGLA_LOTACAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO)||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) --VALUE DO ARRAY
            FROM (                           
                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO, LOTA_DSC_LOTACAO, LOTA_SIGLA_LOTACAO
                    FROM RH_CENTRAL_LOTACAO
                    WHERE   LOTA_SIGLA_SECAO   = '$sigla'
                    --AND  LOTA_DAT_FIM IS NULL (comentado para trazer as unidades extintas)
                )
            CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
            --SEMPRE QUE FOR TIPO 1 (SEÇÃO JUDICIÁRIA) DEVERÁ SER DESCONSIDERADO TODAS AS SUBSEÇÕES JUDICIARIAS (VALOR 2) FILHAS (PEGAR APENA A SEÇÃO)
            --LOGO A LÓGICA A BAIXO OPERA DA SEGUINTE MANEIRA SE TIPO != 1 NÃO COMPRADA O NOT IN POIS O VALOR SERÁ TRUE
            --SE FOR == 1 O VALOR SERÁ FALSE E CAIRÁ NA SEGUNDA VALIDAÇÃO = LOTA_TIPO_LOTACAO NOT IN (2)
            AND ( $tipo != 1 OR LOTA_TIPO_LOTACAO NOT IN (2) )
            START WITH LOTA_COD_LOTACAO = $codigo
          ";

        return $this->_db->fetchPairs($sql);
    }

    private function retornaSecaoETrfComSubsecao()
    {
        $sql = "
            --SELECT SECAO/TRF COM SUBSECAO
            SELECT

                --CAMPOS DO SELECT TRF E SECAO
                TRF_SECAO.SESB_SIGLA_SECAO_SUBSECAO||'|'||TRF_SECAO.LOTA_COD_LOTACAO||'|'||TRF_SECAO.LOTA_TIPO_LOTACAO AS ID_TRF_SECAO
                ,TRF_SECAO.LOTA_DSC_LOTACAO AS VALUE_TRF_SECAO

                --CAMPOS DO SELECT DAS SUBSECOES
                ,SUBSECAO.LOTA_SIGLA_LOTACAO||'|'||SUBSECAO.LOTA_COD_LOTACAO||'|'||SUBSECAO.LOTA_TIPO_LOTACAO AS ID_SUBSECAO
                ,SUBSECAO.LOTA_SIGLA_LOTACAO||' - '||SUBSECAO.LOTA_DSC_LOTACAO||' - '||SUBSECAO.LOTA_COD_LOTACAO||' - '||SUBSECAO.LOTA_SIGLA_SECAO||' - '||SUBSECAO.LOTA_LOTA_COD_LOTACAO_PAI AS VALUE_SUBSECAO
                ,SUBSECAO.LOTA_SIGLA_SECAO AS LOTA_SIGLA_SECAO_SUBSECAO
                ,SUBSECAO.LOTA_COD_LOTACAO AS LOTA_COD_LOTACAO_SUBSECAO
                ,SUBSECAO.LOTA_TIPO_LOTACAO AS LOTA_TIPO_LOTACAO_SUBSECAO
            FROM(

                SELECT
                   SESB_SIGLA_SECAO_SUBSECAO
                   ,LOTA_COD_LOTACAO,LOTA_TIPO_LOTACAO --INDEX DO ARRAY
                   , LOTA_DSC_LOTACAO --VALOR DO ARRAY
                FROM
                   RH_CENTRAL_SECAO_SUBSECAO
                   INNER JOIN RH_CENTRAL_LOTACAO
                       ON SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                       AND SESB_SIGLA_CENTRAL = LOTA_SIGLA_SECAO
                WHERE
                   LOTA_TIPO_LOTACAO IN (1,9)                                        -- colocar em constantes
                   AND LOTA_SIGLA_SECAO = 'TR'
                   AND LOTA_LOTA_COD_LOTACAO_PAI = 1
            ) TRF_SECAO
            LEFT JOIN (

                -- SELECT DAS SUBSECOES
                SELECT
                    LOTA_SIGLA_LOTACAO
                    ,LOTA_COD_LOTACAO
                    ,LOTA_TIPO_LOTACAO -- INDEX DO ARRAY
                    ,LOTA_DSC_LOTACAO
                    ,LOTA_SIGLA_SECAO
                    ,LOTA_LOTA_COD_LOTACAO_PAI
                FROM
                    RH_CENTRAL_SECAO_SUBSECAO
                    INNER JOIN RH_CENTRAL_LOTACAO
                        ON SESB_LOTA_COD_LOTACAO = LOTA_COD_LOTACAO
                        AND SESB_SIGLA_CENTRAL = LOTA_SIGLA_SECAO
                WHERE

                    --AND
                    (
                        --PEGA A PROPRIA SECAO OU PEGA O PROPRIO TRIBUNAL
                        (
                            LOTA_TIPO_LOTACAO IN (1,9)        
                            AND LOTA_LOTA_COD_LOTACAO_PAI = 1 --LEMBRE-SE QUE TODA SECAO OU PROPRIO TRIBUNAL TEM POR PAI A JUSTICA FEDERAL
                        )
                        OR
                        --OU PEGA CASO TENHA AS SUBSECOES (APENAS SE O PARAMETRO PASSADO FOR DE UMA SECAO)
                        (
                            LOTA_TIPO_LOTACAO = 2
                        )
                    )

            ) SUBSECAO
                ON TRF_SECAO.SESB_SIGLA_SECAO_SUBSECAO = SUBSECAO.LOTA_SIGLA_SECAO
                AND TRF_SECAO.LOTA_COD_LOTACAO IN(SUBSECAO.LOTA_COD_LOTACAO, SUBSECAO.LOTA_LOTA_COD_LOTACAO_PAI)

          ";
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna qualquer cache do Orcamento que for passado um nome
     * @param string $nome
     * @return array
     */
    private function retornaCache($nome)
    {

        $cacheId = $this->_cache->retornaID_Listagem($nome);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCache();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Monta as caches das lotacoes
     * @return none
     */
    private function montaCache()
    {


        $cacheTrfSecao = array();
        $cacheSubsecoes = array();
        $cacheUnidadePorSubsecao = array();
        $cacheUnidadePorSiglaSecao = array();
        $cacheUnidade = array();

        $trf_secao_subsecoes = $this->retornaSecaoETrfComSubsecao();
        foreach ($trf_secao_subsecoes as $tupla) {

            $cacheTrfSecao[$tupla['ID_TRF_SECAO']] = $tupla['VALUE_TRF_SECAO'];

            $cacheSubsecoes[$tupla['ID_TRF_SECAO']][$tupla['ID_SUBSECAO']] = $tupla['VALUE_SUBSECAO'];

            $tuplasUnidades = $this->retornaComboUnidade($tupla['LOTA_SIGLA_SECAO_SUBSECAO'], $tupla['LOTA_COD_LOTACAO_SUBSECAO'], $tupla['LOTA_TIPO_LOTACAO_SUBSECAO']);

            $cacheUnidadePorSubsecao[$tupla['ID_SUBSECAO']] = $tuplasUnidades;
            $cacheUnidadePorSiglaSecao[$tupla['LOTA_SIGLA_SECAO_SUBSECAO']] = (isset($cacheUnidadePorSiglaSecao[$tupla['LOTA_SIGLA_SECAO_SUBSECAO']]) ? $cacheUnidadePorSiglaSecao[$tupla['LOTA_SIGLA_SECAO_SUBSECAO']] : array());
            $cacheUnidadePorSiglaSecao[$tupla['LOTA_SIGLA_SECAO_SUBSECAO']] = $cacheUnidadePorSiglaSecao[$tupla['LOTA_SIGLA_SECAO_SUBSECAO']] + $tuplasUnidades;
            //uni os dois arrays
            $cacheUnidade = $cacheUnidade + $tuplasUnidades;
        }

        $tempoVida = Trf1_Rh_Definicoes::TEMPO_6HORAS_EM_SEGUNDOS;
        $cacheId_trf_secao = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_TRF_E_SECOES);
        $cacheId_subsecao = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_SUBSECOES);
        $cacheId_subsecaoUnidade = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SUBSECAO);
        $cacheId_unidadePorSiglaSecao = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SIGLA_SECAO);
        $cacheId_unidade = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_UNIDADES);
        // Cria o cache
        
        $this->_cache->criarCache($cacheTrfSecao, $cacheId_trf_secao, $tempoVida);
        $this->_cache->criarCache($cacheSubsecoes, $cacheId_subsecao, $tempoVida);
        $this->_cache->criarCache($cacheUnidadePorSubsecao, $cacheId_subsecaoUnidade, $tempoVida);
        $this->_cache->criarCache($cacheUnidadePorSiglaSecao, $cacheId_unidadePorSiglaSecao, $tempoVida);
        $this->_cache->criarCache($cacheUnidade, $cacheId_unidade, $tempoVida);
    }

    /**
     * Retorna o codigo da secao ou Tribunal de uma unidade.
     * @param string $siglaSecao
     * @param int $codigoLotacao
     * @return int
     */
    public function retornaCodigoSecaoDaUnidade($siglaSecao, $codigoLotacao)
    {
        $sql = "SELECT SESB_SESU_CD_SECSUBSEC /*CODIGO SEÇÃO DA LOTAÇÃO PAI SEÇAÕ OU TRIBUNAL*/
                FROM RH_CENTRAL_SECAO_SUBSECAO
                WHERE (SESB_SIGLA_CENTRAL,SESB_LOTA_COD_LOTACAO) IN
                (
                    SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO
                    FROM    
                    (
                      SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO            
                      FROM (                           
                              SELECT LOTA_SIGLA_SECAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_COD_LOTACAO, LOTA_TIPO_LOTACAO
                              FROM RH_CENTRAL_LOTACAO A
                              WHERE   LOTA_SIGLA_SECAO   = '$siglaSecao'
                              AND  LOTA_DAT_FIM IS NULL
                          )
                      CONNECT BY PRIOR LOTA_LOTA_COD_LOTACAO_PAI = LOTA_COD_LOTACAO
                      START WITH LOTA_COD_LOTACAO = $codigoLotacao
                    )
                    WHERE LOTA_TIPO_LOTACAO IN(9,1)/*LOTAÇÃO PAI SEÇÃO OU TRIBUNAL*/
                 )";
        $tupla = $this->_db->fetchRow($sql);
        return (int) $tupla["SESB_SESU_CD_SECSUBSEC"];
    }

}
