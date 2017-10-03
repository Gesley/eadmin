<?php

/**
 * @category            TRF1
 * @package		Trf1_Rh_Negocio_Pessoa
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial            Tutorial abaixo
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
class Trf1_Rh_Negocio_Pessoa
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
     * Retorna todas as pessoas juridicas cadastradas no TRF1
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCachePessoasJuricasTrf1()
    {
        $cacheId = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_JURIDICAS_TRF1);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCachePessoasJuricasTrf1();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Retorna todas as pessoas fisicas cadastradas no trf1
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCachePessoaFisicaTrf1()
    {

        $cacheId = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCachePessoasFisicas();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Retorna todas as pessoas fisicas cadastradas no trf1 agrupadas ,
     * por lotacao
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCachePessoaFisicaTrf1AgrupadasPorLotacao()
    {

        $cacheId = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1_POR_UNIDADE_DE_LOTACAO);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCachePessoasFisicas();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Retorna todas as pessoas fisicas cadastradas no trf1 agrupadas pelas
     * unidades de responsabilidade do usuario logado
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCachePessoaFisicaTrf1AgrupadasPorMinhasUnidades()
    {

        $unidadesAgrupadasPorResponsavel = $this->retornaCacheUnidadesAgrupadasPorResponsavel();
        $pessoasAgrupadasPorLotacao = $this->retornaCachePessoaFisicaTrf1AgrupadasPorLotacao();

        $idCaixaDeResponsabilidade = array_keys($unidadesAgrupadasPorResponsavel[$this->_userNs->matricula]);
        $dados = array();
        foreach ($idCaixaDeResponsabilidade as $value) {
            $dados[$value] = $pessoasAgrupadasPorLotacao[$value];
        }
        return $dados;
    }

    /**
     * Retorna um array de todas os responsaveis agrupados pela suas unidades
     * cujo indice é o id da unidade e seus values são combos contendo 
     * os responsaveis pelas mesmas
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCacheResponsaveisAgrupadosPorUnidade()
    {
        $cacheId = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_RESONSAVEIS_AGRUPADOS_POR_UNIDADE);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCacheResponsaveis();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Retorna um array de todas os responsaveis agrupados pelas 
     * unidades de responsabilidade do usuário logado
     * cujo indice é o id da unidade e seus values são combos contendo
     * os responsaveis pelas mesmas
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCacheResponsaveisAgrupadosPorMinhasUnidade()
    {
        $unidadesAgrupadasPorResponsavel = $this->retornaCacheUnidadesAgrupadasPorResponsavel();
        $responsaveisAgrupadosPorUnidade = $this->retornaCacheResponsaveisAgrupadosPorUnidade();

        $idCaixasResponsabilidade = array_keys($unidadesAgrupadasPorResponsavel[$this->_userNs->matricula]);
        $dados = array();
        foreach ($idCaixasResponsabilidade as $value) {
            $dados[$value] = $responsaveisAgrupadosPorUnidade[$value];
        }
        return $dados;
    }

    /**
     * Retorna um array de todas as unidades agrupadas por seus responsáveis
     *  cujo indice é o id do responsável e seus values são combos contendo 
     * as unidades de responsabilidade
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCacheUnidadesAgrupadasPorResponsavel()
    {
        $cacheId = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_UNIDADES_AGRUPADAS_POR_RESPONSAVEL);
        $dados = $this->_cache->lerCache($cacheId);

        if ($dados === false) {
            $this->montaCacheResponsaveis();
            $dados = $this->_cache->lerCache($cacheId);
        }
        return $dados;
    }

    /**
     * Retorna um array com todos os responsáveis por alguma unidade e suas
     * unidades de responsabilidade
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    private function retornaResponsaveisDeUnidade()
    {
        $sql = "
        SELECT DISTINCT
            PNAT_NO_PESSOA
            ,PMAT_CD_MATRICULA AS VALUE_PESSOA
            ,PMAT_CD_MATRICULA||' - '||PNAT_NO_PESSOA AS LABEL_PESSOA
            ,UNPE_SG_SECAO||'|'||UNPE_CD_LOTACAO AS VALUE_UNIDADE
            ,LOTA_SIGLA_LOTACAO||' - '||RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO)||' - '||LOTA_COD_LOTACAO||' - '||LOTA_SIGLA_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS LABEL_UNIDADE
            --CONCATENAR COM A TABELA QUE PEGA A SIGLA DA LOTACAO QUE É POR EXEMPLO TRF1 NO LUGAR DE APENAS TR
        FROM OCS_TB_PUPE_PERFIL_UNID_PESSOA
            INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL
               ON PUPE_ID_UNIDADE_PERFIL = UNPE_ID_UNIDADE_PERFIL
               AND UNPE_ID_PERFIL = " . Trf1_Sisad_Definicoes::PERFIL_RESPONSAVEL_UNIDADE . "
            INNER JOIN RH_CENTRAL_LOTACAO
               ON UNPE_SG_SECAO   =  LOTA_SIGLA_SECAO
               AND UNPE_CD_LOTACAO =  LOTA_COD_LOTACAO
            INNER JOIN OCS_TB_PMAT_MATRICULA
               ON PUPE_CD_MATRICULA = PMAT_CD_MATRICULA
               AND PMAT_DT_FIM IS NULL
            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
               ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
        ORDER BY PNAT_NO_PESSOA, LABEL_UNIDADE";
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna todas as pessoas fisicas do TRF1
     * 
     * @param	none
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    private function retornaPessoasFisicasTrf1()
    {
        $sql = "
            SELECT 
                PMAT_CD_MATRICULA VALUE_PESSOA
                ,PMAT_CD_MATRICULA||' - '||PNAT_NO_PESSOA AS LABEL_PESSOA
                ,PMAT_SG_SECSUBSEC_LOTACAO||'|'||PMAT_CD_UNIDADE_LOTACAO AS VALUE_UNIDADE
            FROM
                OCS_TB_PNAT_PESSOA_NATURAL 
                INNER JOIN OCS_TB_PMAT_MATRICULA
                    ON  PNAT_ID_PESSOA = PMAT_ID_PESSOA 
            WHERE PMAT_DT_FIM IS NULL
            ORDER BY PNAT_NO_PESSOA
        ";
        return $this->_db->fetchAll($sql);
    }

    public function retornaComboPessoasFisicasTrf1($termo)
    {
        $sql = "
            SELECT 
                PMAT_CD_MATRICULA||' - '||PNAT_NO_PESSOA AS label
                , PMAT_CD_MATRICULA
                , PNAT_ID_PESSOA
            FROM
                OCS_TB_PNAT_PESSOA_NATURAL 
                INNER JOIN OCS_TB_PMAT_MATRICULA
                    ON  PNAT_ID_PESSOA = PMAT_ID_PESSOA 
            WHERE PMAT_DT_FIM IS NULL
            " . ($termo != '' ? "AND PMAT_CD_MATRICULA||PNAT_NO_PESSOA LIKE UPPER('%$termo%')" : '') . "
            ORDER BY PNAT_NO_PESSOA
        ";
        return $this->_db->fetchAll($sql);
    }

    public function retornaComboPessoasFisicasExternas($termo)
    {
        $sql = "
            SELECT  PNAT_NO_PESSOA AS LABEL,
                    PNAT_ID_PESSOA AS ID
            FROM   OCS_TB_PNAT_PESSOA_NATURAL 
            WHERE  PNAT_NO_PESSOA LIKE UPPER('%$termo%') 
        ";
        return $this->_db->fetchAll($sql);
    }

    public function retornaComboPessoasJuridicasTrf1($termo)
    {
        $sql = "SELECT PJUR_ID_PESSOA AS ID,
                       PJUR_ID_PESSOA||' - '||PJUR_NO_RAZAO_SOCIAL AS LABEL
                FROM OCS_TB_PJUR_PESSOA_JURIDICA
                WHERE PJUR_ID_PESSOA||' - '||PJUR_NO_RAZAO_SOCIAL LIKE UPPER ('%$termo%')";
        return $this->_db->fetchAll($sql);
    }

    private function retornaPessoasJuridicasTrf1()
    {
        $sql = " SELECT PJUR_ID_PESSOA AS VALUE,
                        PJUR_ID_PESSOA||' - '||PJUR_NO_RAZAO_SOCIAL AS LABEL
                 FROM OCS_TB_PJUR_PESSOA_JURIDICA";
        return $this->_db->fetchPairs($sql);
    }

    /**
     * Monta as caches das lotacoes
     * @return none
     */
    private function montaCachePessoasFisicas()
    {
        $cachePessoas = array();
        $cachePessoasAgrupadasPorUnidade = array();

        $pessoasFisicas = $this->retornaPessoasFisicasTrf1();
        foreach ($pessoasFisicas as $tupla) {
            $pessoaTupla = array($tupla['VALUE_PESSOA'] => $tupla['LABEL_PESSOA']);
            //preenche array de todas pessoas fisicas
            $cachePessoas = $cachePessoas + $pessoaTupla;
            //preenche array de todas as pessoas fisicas agrupadas
            //pela unidade de lotacao
            $cachePessoasAgrupadasPorUnidade[$tupla['VALUE_UNIDADE']] = (isset($cachePessoasAgrupadasPorUnidade[$tupla['VALUE_UNIDADE']]) ? $cachePessoasAgrupadasPorUnidade[$tupla['VALUE_UNIDADE']] : array());
            $cachePessoasAgrupadasPorUnidade[$tupla['VALUE_UNIDADE']] = $cachePessoasAgrupadasPorUnidade[$tupla['VALUE_UNIDADE']] + $pessoaTupla;
        }

        //tempo de vida da cache
        $tempoVida = Trf1_Rh_Definicoes::TEMPO_3HORAS_EM_SEGUNDOS;

        //busca o id das caches de acordo com a classe de cache
        $cacheId_pessoas_fisicas = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1);
        $cacheId_pessoas_fisicas_agrupadas = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1_POR_UNIDADE_DE_LOTACAO);

        // Cria o cache
        $this->_cache->criarCache($cachePessoas, $cacheId_pessoas_fisicas, $tempoVida);
        $this->_cache->criarCache($cachePessoasAgrupadasPorUnidade, $cacheId_pessoas_fisicas_agrupadas, $tempoVida);
    }

    private function montaCachePessoasJuricasTrf1()
    {
        $pessoasJuridicas = $this->retornaPessoasJuridicasTrf1();

        //tempo de vida da cache
        $tempoVida = Trf1_Rh_Definicoes::TEMPO_3HORAS_EM_SEGUNDOS;
        //busca o id das caches de acordo com a classe de cache
        $cacheId_pessoasJuricidas = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_PESSOAS_JURIDICAS_TRF1);
        // Cria o cache
        $this->_cache->criarCache($pessoasJuridicas, $cacheId_pessoasJuricidas, $tempoVida);
    }

    private function montaCacheResponsaveis()
    {
        $cacheCaixaAgrupadaPorResponsavel = array();
        $cacheResponsavelAgrupadoPorCaixa = array();

        $responsaveis = $this->retornaResponsaveisDeUnidade();
        foreach ($responsaveis as $tupla) {
            $cacheResponsavelAgrupadoPorCaixa[$tupla['VALUE_UNIDADE']] = (isset($cacheResponsavelAgrupadoPorCaixa[$tupla['VALUE_UNIDADE']]) ? $cacheResponsavelAgrupadoPorCaixa[$tupla['VALUE_UNIDADE']] : array());
            $cacheCaixaAgrupadaPorResponsavel[$tupla['VALUE_PESSOA']] = (isset($cacheCaixaAgrupadaPorResponsavel[$tupla['VALUE_PESSOA']]) ? $cacheCaixaAgrupadaPorResponsavel[$tupla['VALUE_PESSOA']] : array());

            $cacheResponsavelAgrupadoPorCaixa[$tupla['VALUE_UNIDADE']] = $cacheResponsavelAgrupadoPorCaixa[$tupla['VALUE_UNIDADE']] + array($tupla['VALUE_PESSOA'] => $tupla['LABEL_PESSOA']);
            $cacheCaixaAgrupadaPorResponsavel[$tupla['VALUE_PESSOA']] = $cacheCaixaAgrupadaPorResponsavel[$tupla['VALUE_PESSOA']] + array($tupla['VALUE_UNIDADE'] => $tupla['LABEL_UNIDADE']);
        }
        //tempo de vida da cache
        $tempoVida = Trf1_Rh_Definicoes::TEMPO_30MINUTOS_EM_SEGUNDOS;

        //busca o id das caches de acordo com a classe de cache
        $cacheId_caixasAgrupadas = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_UNIDADES_AGRUPADAS_POR_RESPONSAVEL);
        $cacheId_pessoasAgrupadas = $this->_cache->retornaID_Listagem(Trf1_Rh_Definicoes::CACHE_RESONSAVEIS_AGRUPADOS_POR_UNIDADE);

        // Cria o cache
        $this->_cache->criarCache($cacheCaixaAgrupadaPorResponsavel, $cacheId_caixasAgrupadas, $tempoVida);
        $this->_cache->criarCache($cacheResponsavelAgrupadoPorCaixa, $cacheId_pessoasAgrupadas, $tempoVida);
    }

    /**
     * Retorna uma pessoa física. A pessoa pode ser buscada pelo id ou 
     * pela matrícula
     * @param array $pessoa
     * @return array
     */
    public function retornaPessoaFisica($pessoa)
    {
        $clausula = (is_null($pessoa['PMAT_CD_MATRICULA']) ? 'PNAT_ID_PESSOA = ' . $pessoa['PNAT_ID_PESSOA'] : "PMAT_CD_MATRICULA = '" . $pessoa['PMAT_CD_MATRICULA'] . "'");
        $sql = "
            SELECT
                PMAT_CD_MATRICULA
                ,PNAT_ID_PESSOA
                ,LPAD(PNAT_NR_CPF,11,'0') PNAT_NR_CPF
                ,PNAT_NO_PESSOA
                ,PNAT_NR_CNH
                ,PNAT_SG_UF_CNH
                ,PNAT_DT_EMISSAO_CNH
                ,PNAT_DT_VALIDADE_CNH
                ,PNAT_IC_CATEGORIA_CNH
                ,PNAT_DT_NASCIMENTO
                ,PNAT_CD_LOCAL_NASCIMENTO
                ,PNAT_ID_ESTADO_CIVIL
                ,PNAT_NR_IDENTIDADE
                ,PNAT_SG_ORGAO_EMISSOR_ID
                ,PNAT_DH_EMISSAO_ID
                ,PNAT_SG_UF_EMISSOR_ID
                ,PNAT_IC_PESSOA
            FROM
                OCS_TB_PNAT_PESSOA_NATURAL
                INNER JOIN OCS_TB_PMAT_MATRICULA
                    ON PNAT_ID_PESSOA = PMAT_ID_PESSOA
                    AND $clausula
        ";
        return $this->_db->fetchRow($sql);
    }

}
