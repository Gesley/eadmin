<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_TipoConfidencialidade
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre tipos de confidencialidade no sisad
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
class Trf1_Sisad_Negocio_TipoConfidencialidade {

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
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_cache = new Trf1_Cache();
    }

    /**
     * Retorna array com os tipos de confidencialidade
     * @return array
     */
    private function retornaConfidencialidadesAdministrativa() {
        //enquanto não existir as confidencialidades judiciais usar o codigo abaixo
        $confidencialidadesExcluidas = array(
            Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_A_SUBGRUPO_INTRANET
        );
        $sql = '
            SELECT CONF_ID_CONFIDENCIALIDADE
                   ,CONF_DS_CONFIDENCIALIDADE
            FROM SAD_TB_CONF_CONFIDENCIALIDADE
            WHERE CONF_ID_CONFIDENCIALIDADE NOT IN(' . implode(',', $confidencialidadesExcluidas) . ')
            ORDER BY CONF_ID_CONFIDENCIALIDADE ASC

            ';
        return $this->_db->fetchPairs($sql);
    }
    
    public function retornaCacheTipoConfidencialidade(){
        return $this->retornaCache(Trf1_Sisad_Definicoes::CACHE_TIPO_CONFIDENCIALIDADE_ADMINISTRATIVA);
    }
    
    /**
     * Retorna qualquer cache do Orcamento que for passado um nome
     * @param string $nome
     * @return array
     */
    private function retornaCache($nome) {

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
    private function montaCache() {

        $confidencialidades = $this->retornaConfidencialidadesAdministrativa();
        

        $tempoVida = Trf1_Sisad_Definicoes::TEMPO_24HORAS_EM_SEGUNDOS;
        $cacheId_confidencialidades = $this->_cache->retornaID_Listagem(Trf1_Sisad_Definicoes::CACHE_TIPO_CONFIDENCIALIDADE_ADMINISTRATIVA);
        // Cria o cache
        $this->_cache->criarCache($confidencialidades, $cacheId_confidencialidades, $tempoVida);
    }

}