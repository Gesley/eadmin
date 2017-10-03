<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TomboTiCentral
 *
 * @author TR17358PS
 */
class Application_Model_DbTable_TomboTiCentral  extends Zend_Db_Table_Abstract {
    
    protected $_name = 'TOMBO_TI_CENTRAL';
    protected $_primary = 'ID_TOMBO_TI_CENTRAL';
    protected $_siglaSecao = '';


    public function __construct() {
        parent::__construct();
        $userNs = new Zend_Session_Namespace('userNs');
        $this->_siglaSecao = $userNs->siglasecao;
    }

    public function getDescTombo($nu_tombo) {
        if (!$nu_tombo)
            $nu_tombo = 0;
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.NU_TOMBO,A.DE_MAT
                              FROM TOMBO_TI_CENTRAL A
                             WHERE A.TI_TERMO IN (6,5,1) 
                               AND A.LOTA_SIGLA_SECAO = '$this->_siglaSecao'
                               AND A.NU_TOMBO = $nu_tombo");
        $rows = $stmt->fetchAll();
        if ($rows) {
            return $rows;
        } else {
            $rows[0]['DE_MAT'] = 'Tombo não encontrado';
            $rows[0]['NU_TOMBO'] = '';
            return $rows;
        }
    }

    
    /**
     * 
     * retorna o nome da rede  do usuario pelo número do tombo e tipo do tombo
     * @param int $tomboNR
     * @param char $tomboTipo
     */
    public function getNomeRede($tomboNR, $tomboTipo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT NO_REDE
            FROM TOMBO
            WHERE 
            TI_TOMBO = '$tomboTipo' AND
            NU_TOMBO = '$tomboNR'");
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function getTomboInfo($tomboNr) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
          SELECT A.NU_TOMBO AS LABEL
            FROM TOMBO_TI_CENTRAL A
           WHERE A.TI_TOMBO = 'T'
             AND A.CO_MAT LIKE '5235%'
             AND A.LOTA_SIGLA_SECAO = '$this->_siglaSecao' 
             AND A.NU_TOMBO LIKE '$tomboNr%'");
        $rows = $stmt->fetchAll();
        return $rows;
    }

    /**
     * Verifica se o tombo está disponível para uso  na ficha de serviço.
     * ...
     * @param int $tomboNr
     */
    public function isTomboDisponivel($tomboNr) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT A.*,B.*
            FROM SOS_TB_LSBK_SERVICO_BACKUP A, SOS_TB_LBKP_BACKUP B
            WHERE LSBK_NR_TOMBO = $tomboNr
            AND LSBK_TP_TOMBO = 'T'
            AND A.LSBK_NR_TOMBO = B.LBKP_NR_TOMBO
            AND A.LSBK_TP_TOMBO = B.LBKP_SG_TOMBO
                ";
        //
        $row = $db->query($stmt)->fetchAll();
        if ($row)
            return $row;
    }

    public function getTomboBackup($tomboNr) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT A.LBKP_NR_TOMBO, A.LBKP_SG_TOMBO, C.DE_MAT
              FROM SOS_TB_LBKP_BACKUP A, TOMBO_TI_CENTRAL C 
             WHERE A.LBKP_NR_TOMBO = C.NU_TOMBO 
               AND A.LBKP_SG_TOMBO = C.TI_TOMBO
               AND A.LBKP_SG_TOMBO = 'T'
               AND A.LBKP_IC_ATIVO = 'S'
               AND C.TI_TERMO IN (6,5,1)
               AND C.LOTA_SIGLA_SECAO = '$this->_siglaSecao'
               AND A.LBKP_NR_TOMBO = $tomboNr");
        $row = $stmt->fetchAll();
        return $row;
    }

    /**
     * Retorna a lista de Tombos a serem usado no check list.
     * 
     * @param unknown_type $nrTombo
     */
    public function getTomboLista($nrTombo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT A.LBKP_NR_TOMBO AS LABEL 
              FROM SOS_TB_LBKP_BACKUP A
             WHERE A.LBKP_NR_TOMBO LIKE '$nrTombo%'
               AND A.LBKP_IC_ATIVO = 'S'";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Retorna a lista de néumero de Tombos
     * 
     * @param $nrTombo
     * @return array 
     */
    public function getNumeroTombo($nrTombo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                NU_TOMBO AS ID, 
                NU_TOMBO AS LABEL 
            FROM 
                TOMBO_TI_CENTRAL
            WHERE TI_TOMBO = 'T' 
              AND TI_TERMO IN (6,5,1)
              AND LOTA_SIGLA_SECAO = '$this->_siglaSecao' 
              AND NU_TOMBO LIKE '$nrTombo%'";
        return $db->query($sql)->fetchAll();
    }

    
}
