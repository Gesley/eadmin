<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbLbkpBackup extends Zend_Db_Table_Abstract
{

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LBKP_BACKUP';
    protected $_primary = 'LBKP_ID_BACKUP';
    protected $_sequence = 'SOS_SQ_LBKP';


    /**
     * get tomo backup details
     */
    public function gettomboBackupDetail($tomboNr)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT
              TTC.ID_TOMBO_TI_CENTRAL,
              TTC.NU_TOMBO,
              TTC.TI_TOMBO,
              A.LBKP_CD_MATRICULA_CAD,
              A.LBKP_DH_CADASTRO,
              DECODE(A.LBKP_IC_ATIVO,'S',1,'N',0)LBKP_IC_ATIVO,
              A.LBKP_CD_MATRICULA_EXC,
              TTC.LOTA_SIGLA_SECAO,
              TTC.LOTA_COD_LOTACAO,
              A.LBKP_DH_EXCLUSAO
             -- TTC.LOTA_COD_LOTACAO AS LBKP_CD_LOTACAO
            FROM SOS_TB_LBKP_BACKUP A
              INNER JOIN TOMBO_TI_CENTRAL TTC ON TTC.ID_TOMBO_TI_CENTRAL = A.LBKP_ID_TOMBO_TI_CENTRAL
            WHERE
              TTC.NU_TOMBO = $tomboNr
              AND TTC.TI_TOMBO = 'T'
		";


        $row = $db->query($stmt)->fetchAll();
        return $row;
    }

    function getbackupTomboList($order = null)
    {
        if (is_null($order)) {
            $order = "LBKP_ID_TOMBO_TI_CENTRAL ASC";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "
           SELECT
                *
            FROM SOS_TB_LBKP_BACKUP BKP

              INNER JOIN TOMBO_TI_CENTRAL TTC ON TTC.ID_TOMBO_TI_CENTRAL = BKP.LBKP_ID_TOMBO_TI_CENTRAL
              INNER JOIN TOMBO TB ON TTC.NU_TOMBO = TB.NU_TOMBO
              INNER JOIN MATERIAL MAT ON MAT.CO_MAT = TB.CO_MAT

              INNER JOIN RH_CENTRAL_LOTACAO RH
                ON TTC.LOTA_SIGLA_SECAO = RH.LOTA_SIGLA_SECAO
                   AND TTC.LOTA_COD_LOTACAO = RH.LOTA_COD_LOTACAO

            WHERE \"TB\".TI_TOMBO = 'T'
            --AND \"BKP\".LBKP_IC_ATIVO = 'S'
            ORDER BY $order, \"BKP\".LBKP_IC_ATIVO DESC
        ";

        $rows = $db->query($stmt)->fetchAll();
        return $rows;
    }

    /**
     * Retorna a lista de néumero de Tombos de backup que não estão emprestados
     *
     * @param $nrTombo
     * @return array
     */
    public function getNumeroTomboBackup($nrTombo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT
              NU_TOMBO AS ID,
              NU_TOMBO AS LABEL
            FROM
              SICAM.TOMBO_TI_CENTRAL TTC
              INNER JOIN SOS.SOS_TB_LBKP_BACKUP BK
                ON BK.LBKP_ID_TOMBO_TI_CENTRAL = TTC.ID_TOMBO_TI_CENTRAL
            WHERE
              TTC.TI_TOMBO = 'T'
              AND TTC.NU_TOMBO LIKE '$nrTombo%'
            ";
        return $db->query($sql)->fetchAll();
    }

}