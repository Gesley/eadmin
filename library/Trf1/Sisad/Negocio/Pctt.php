<?php

/**
 * 
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Pctt
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre pctt dos documentos do sisad
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
class Trf1_Sisad_Negocio_Pctt
{

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Adapter_Abstract $_db
     */
    private $_db;

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
    }

    /**
     * Retorna todos os pctt ativos em um formato util na utilização de ajax
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getPcttsAjax()
    {
        $sql = "SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT DESCRICAO_PCTT
                FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                    AND AQVP_ID_AQVP_ATUAL IS NULL
                    AND AQVP_DH_FIM IS NULL
                    ORDER BY DESCRICAO_PCTT";
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna todos os pctt ativos
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getPctts()
    {
        return null;
    }

    /**
     * Retorna combo com todos os pctt ativos
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function retornaCombo()
    {
        $sql = "SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT AS DESCRICAO_PCTT
                FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                    AND AQVP_ID_AQVP_ATUAL IS NULL
                    AND AQVP_DH_FIM IS NULL
                    ORDER BY DESCRICAO_PCTT";
        return $this->_db->fetchPairs($sql);
    }

    public function retornaCachePctt()
    {
        return $this->retornaCache(Trf1_Sisad_Definicoes::CACHE_PCTT);
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

    private function montaCache()
    {

        $dados = $this->retornaCombo();

        $tempoVida = Trf1_Sisad_Definicoes::TEMPO_24HORAS_EM_SEGUNDOS;
        $cacheId_pctt = $this->_cache->retornaID_Listagem(Trf1_Sisad_Definicoes::CACHE_PCTT);

        // Cria o cache
        $this->_cache->criarCache($dados, $cacheId_pctt, $tempoVida);
    }

}
