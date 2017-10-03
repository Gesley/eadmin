<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class Application_Model_DbTable_SosTbGrexGrupoServExped extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_GREX_GRUPO_SERV_EXPED';
    protected $_primary = 'GREX_ID_GRUPO_SERV_EXPED';
    protected $_sequence = 'SOS_SQ_GREX';
 
    public function getExpedientePorGrupoPorNomeExpediente($idGrupo, $nmExpediente)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT GREX_NM_EXPEDIENTE_GRUPO EXPEDIENTE, TO_CHAR(HREX_DT_INICIO_EXPEDIENTE,'HH24:MI:SS') INICIO, TO_CHAR(HREX_DT_FIM_EXPEDIENTE,'HH24:MI:SS') FIM 
                            FROM SOS_TB_GREX_GRUPO_SERV_EXPED
                            INNER JOIN OCS_TB_HREX_HORARIO_EXPEDIENTE
                            ON HREX_ID_HORARIO_EXPEDIENTE = GREX_ID_HORARIO_EXPEDIENTE
                            WHERE GREX_ID_GRUPO = $idGrupo
                            AND UPPER(GREX_NM_EXPEDIENTE_GRUPO) = UPPER('$nmExpediente')
                		");
        return $stmt->fetch();
    }
}