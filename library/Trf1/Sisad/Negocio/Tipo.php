<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Tipo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre qualquer tipo de Vinculação de documentos no Sisad
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
class Trf1_Sisad_Negocio_Tipo {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    private $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Retorna os tipos de documento por default o tipo minuta e solicitação de serviço foram excluidos.
     * 
     * @param array $tiposExcluidos
     * @return array
     */
    public function getTipoDocumento($tiposExcluidos = array()) {

        $tiposExcluidos[] = Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_MINUTA;
        $tiposExcluidos[] = Trf1_Sisad_Definicoes::TIPO_SOLICITACAO_SERVICO;
        $andTiposExcluidos = ' AND DTPD_ID_TIPO_DOC NOT IN (' . implode($tiposExcluidos, ',') . ')';
        return $this->_db->fetchAll('SELECT DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = \'S\'
                               AND DTPD_IC_ADM_JUD <> \'JU\'
                               ' . $andTiposExcluidos . '
                          ORDER BY DTPD_NO_TIPO ');
    }
    
    /**
     * Retorna os tipo de juntada do relacionamento documento à processo
     * 
     * @return type none
     */
    public function getTipoJuntadaDocumentoProcesso() {
        $sadTbTvdpTipoVincDocProc = new Application_Model_DbTable_Sisad_SadTbTvdpTipoVincDocProc();
        $arrayResultado = $sadTbTvdpTipoVincDocProc->fetchAll()->toArray();
        foreach ($arrayResultado as $vinculo) {
            $arrayVinculos[$vinculo['TVDP_ID_TP_VINCULACAO']] = array('id' => $vinculo['TVDP_ID_TP_VINCULACAO'], 'nome' => $vinculo['TVDP_DS_TP_VINCULACAO']);
        }

        return $arrayVinculos;
    }

    /**
     * Retorna os tipo de juntada do relacionamento processo à processo
     * 
     * @return type none
     */
    public function getTipoJuntadaProcessoProcesso() {
        $sadTbTvpdTipoVincProcesso = new Application_Model_DbTable_Sisad_SadTbTvpdTipoVincProcesso();
        $arrayResultado = $sadTbTvpdTipoVincProcesso->fetchAll()->toArray();
        foreach ($arrayResultado as $vinculo) {
            $arrayVinculos[$vinculo['TVPD_ID_TP_VINCULACAO']] = array('id' => $vinculo['TVPD_ID_TP_VINCULACAO'], 'nome' => $vinculo['TVPD_DS_TP_VINCULACAO']);
        }

        return $arrayVinculos;
    }

    /**
     * Retorna os tipo de juntada do relacionamento documento à documento
     * 
     * @return type none
     */
    public function getTipoJuntadaDocumentoDocumento() {
        //
    }

}