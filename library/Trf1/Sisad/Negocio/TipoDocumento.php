<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_TipoDocumento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre tipos de documentos no sisad
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
class Trf1_Sisad_Negocio_TipoDocumento {

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
     * Retorna array com os tipos de documentos
     * @return array
     */
    public function retornaCombo() {
        return $this->retornaCache(Trf1_Sisad_Definicoes::CACHE_TIPO_DOCUMENTO);
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

    private function montaCache() {
        $tiposExcluidos[] = Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_MINUTA;
        $tiposExcluidos[] = Trf1_Sisad_Definicoes::TIPO_SOLICITACAO_SERVICO;
        $andTiposExcluidos = ' AND DTPD_ID_TIPO_DOC NOT IN (' . implode($tiposExcluidos, ',') . ')';
        $sql = 'SELECT DTPD_ID_TIPO_DOC,DTPD_NO_TIPO
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = \'S\'
                               AND DTPD_IC_ADM_JUD <> \'JU\'
                               ' . $andTiposExcluidos . '
                          ORDER BY DTPD_NO_TIPO ';
        $dados = $this->_db->fetchPairs($sql);

        $tempoVida = Trf1_Sisad_Definicoes::TEMPO_24HORAS_EM_SEGUNDOS;
        $cacheId_tipoDocumento = $this->_cache->retornaID_Listagem(Trf1_Sisad_Definicoes::CACHE_TIPO_DOCUMENTO);

        // Cria o cache
        $this->_cache->criarCache($dados, $cacheId_tipoDocumento, $tempoVida);
    }

}