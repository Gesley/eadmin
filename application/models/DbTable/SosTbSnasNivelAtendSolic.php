<?php
class Application_Model_DbTable_SosTbSnasNivelAtendSolic extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SNAS_NIVEL_ATEND_SOLIC';
    protected $_primary = array('SNAS_ID_MOVIMENTACAO', 'SNAS_DH_FASE');
    
    public function getPrimeiroNivel($idGrupo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SNAT_ID_NIVEL
                            FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS
                            INNER JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                            ON SGRS.SGRS_ID_GRUPO = SNAT.SNAT_ID_GRUPO
                            WHERE SGRS.SGRS_ID_GRUPO = $idGrupo
                            AND SNAT_CD_NIVEL = (
                                                    SELECT MIN(SNAT_CD_NIVEL)
                                                    FROM SOS_TB_SGRS_GRUPO_SERVICO SGRS
                                                    INNER JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
                                                    ON SGRS.SGRS_ID_GRUPO = SNAT.SNAT_ID_GRUPO
                                                    WHERE SGRS.SGRS_ID_GRUPO = $idGrupo
                            )");
        
        return $stmt->fetch();
    }
    

}