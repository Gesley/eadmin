<?php

/**
 * 
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_Fase
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre FASES de solicitações de informação
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
class Trf1_Sosti_Negocio_Fase {

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
     * Insere uma nova fase na MOVIMENTAÇÂO especificada
     * 
     * @param	array	$arrayMovimentacao
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function lancaFase(array $arrayMovimentacao) {

        /*
         * Formato do array a ser utilizado
         * 
         * $arrayMovimentacao = array(
          'MOFA_ID_MOVIMENTACAO' => ''
          , 'MOFA_CD_MATRICULA'    => ''
          , 'MOFA_DH_FASE'         => ''
          , 'MOFA_ID_FASE'         => ''
          , 'MOFA_DS_COMPLEMENTO'  => ''); */

        /* Cria fase */
        $sadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
        $sadTbMofaMoviFase->createRow($arrayMovimentacao)
                ->save();
    }

    /**
     * Retorna a(s) ultima(s) fase(s) desejada(s) de uma movimentação especifica
     * @param int $fase
     * @param mixed $idMovimentacao
     * @param string $order
     */
    public function getFaseMovimentacao($fase, $idMovimentacao, $order = 'TO_DATE(MOFA_DH_FASE,\'DD/MM/YYYY HH24:MI:SS\') DESC') {
        if (is_array($fase)) {
            $completaFase = 'MOFA_ID_FASE IN(' . implode($fase, ',') . ')';
        } else {
            $completaFase = 'MOFA_ID_FASE = ' . $fase;
        }
        $sql = "
            SELECT MOFA_ID_FASE
                   ,TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS MOFA_DH_FASE
                   ,MOFA_CD_MATRICULA
            FROM SAD_TB_MOFA_MOVI_FASE
            WHERE MOFA_ID_MOVIMENTACAO = $idMovimentacao AND
                  $completaFase
            ORDER BY $order
            ";
        if (is_array($fase)) {
            return $this->_db->fetchAll($sql);
        } else {
            return $this->_db->fetchRow($sql);
        }
    }

    /**
     * Verifica se existe uma fase especifica depois de uma data e hora em uma
     * movimentação especifica
     * @param type $fase
     * @param type $idMovimentacao
     * @param type $dataHora
     */
    public function isFaseDepoisData($fase, $idMovimentacao, $dataHora) {
        $sql = "
            SELECT COUNT(*) QTD
            FROM SAD_TB_MOFA_MOVI_FASE
            WHERE
                MOFA_ID_MOVIMENTACAO = $idMovimentacao AND
                MOFA_ID_FASE = $fase AND
                MOFA_DH_FASE > TO_DATE('$dataHora','DD/MM/YYYY HH24:MI:SS')
            ";
        $resultado = $this->_db->fetchRow($sql);
        return ($resultado['QTD'] > 0);
    }

}