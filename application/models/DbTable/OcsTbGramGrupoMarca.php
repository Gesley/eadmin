<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_OcsTbGramGrupoMarca extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_GRMA_GRUPO_MARCA';
    protected $_primary = array('GRMA_ID_GRUPO_MAT_SERV', 'GRMA_ID_MARCA');
    protected $_sequence = 'OCS_SQ_GRUP';

    public function getGrupoporMarca($marcaID) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT  LHDW_ID_HARDWARE,
                                    LHDW_DS_HARDWARE,
                                    LHDW_CD_MATERIAL,
                                    LHDW_CD_MARCA,
                                    MARC_DS_MARCA,
                                    LHDW_CD_MODELO,
                                    MODE_DS_MODELO,
                                    LHDW_NR_PROCESSO
                              FROM SOS_TB_LHDW_MATERIAL_ALMOX,
                                    OCS_TB_MODE_MODELO,
                                    OCS_TB_MARC_MARCA
                              WHERE LHDW_CD_MARCA = $marcaID
                               AND LHDW_CD_MODELO = MODE_ID_MODELO
                               ORDER BY $order";

        $Hardware = $db->query($stmt)->fetchAll();
        return $Hardware;
    }

    public function getgrupoAssociacoes($grupoID, $flag = null) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = "SELECT B.MARC_DS_MARCA, B.MARC_ID_MARCA, A.GRMA_IC_ATIVO
				  FROM 
				  OCS_TB_GRMA_GRUPO_MARCA A,
				  OCS_TB_MARC_MARCA B ";

        //SOMENTE PARA TESTE REMOVER QUANDO O GRUPO E A MARCA FOR SELECIONADO PARA O MODELO.::1 
        if (isset($grupoID)) {
            $sql .= "WHERE  A.GRMA_ID_GRUPO_MAT_SERV(+) = $grupoID
				  AND A.GRMA_ID_MARCA = B.MARC_ID_MARCA
				  AND A.GRMA_IC_ATIVO = 'S' 
				  ";
        }
        //::1
        //zend_debug::dump($sql);exit;
        return $db->query($sql)->fetchAll();
    }

    /**
     * Retorna as marcas associadas ao grupo.
     * @param int $grupoID
     * 
     */
    public function getMarcaporGrupo($grupoID) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT B.MARC_DS_MARCA
			   FROM 
			   OCS_TB_GRMA_GRUPO_MARCA A, OCS_TB_MARC_MARCA B
			   WHERE 
			   A.GRMA_ID_MARCA = B.MARC_ID_MARCA
			   AND A.GRMA_ID_GRUPO_MAT_SERV =$grupoID";
        return $db->query($sql)->fetchAll();
    }

    /**
     * ESTE MÉTODO RETORNA TODOS OS GRUPOS  ASSOCIADO A ID DA MARCA
     * 
     * @param int $marcaID
     */
    public function getGrupopelaMarcaID($marcaID) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT B.GRUP_DS_GRUPO_MAT_SERV
				FROM 
					 OCS_TB_GRMA_GRUPO_MARCA A,
				     OCS_TB_GRUP_GRUPO_MAT_SERV B
				WHERE 
				A.GRMA_ID_MARCA = $marcaID
				AND A.GRMA_ID_GRUPO_MAT_SERV = B.GRUP_ID_GRUPO_MAT_SERV";

        return $db->query($sql)->fetchAll();
    }

    /**
     * Método que atualiza as associuações do grupo com a marca. 
     * 
     * @param int $ids
     * @param int $GrupoID
     * @param char $flag
     * @param boolean $acao 
     */
    public function updateRows($idsPassed, $GrupoID, $flag, $acao) {
        //Zend_Debug::dump($idsPassed);exit;
        if ($idsPassed && count($idsPassed) > 0) {

            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();

            $ids = implode(',', $idsPassed);

            $stmt = "UPDATE OCS_TB_GRMA_GRUPO_MARCA 
                SET 
                GRMA_IC_ATIVO = '$flag'
                WHERE 
                GRMA_ID_GRUPO_MAT_SERV = $GrupoID";
            if (!empty($ids)) {
                if ($acao == true) {
                    $stmt .= " AND GRMA_ID_MARCA in($ids)";
                } else {
                    $stmt .= " AND GRMA_ID_MARCA not in($ids)";
                }
            }

            $retorno = $db->query($stmt);
            if ($retorno) {
                $db->commit();
            } else {
                $db->rollBack();
            }
        }
        return true;
    }

    public function updateParaN($GrupoID) {
        try {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $stmt = "UPDATE OCS_TB_GRMA_GRUPO_MARCA 
                    SET 
                    GRMA_IC_ATIVO = 'N'
                    WHERE 
                    GRMA_ID_GRUPO_MAT_SERV = $GrupoID";

            $db->query($stmt);
            return true;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }


        //return true;
    }

    /**
     * Retorna todas as associaoes do grupo exceto as que estão no NOT IN clause 
     *  @param  array $idsMarca
     *  @Param  int $GrupoID  
     */
    public function getMarcasNotIn($grupoID, $idsMarca, $flagTipo = null) {

        if (count($idsMarca) > 0) {

            $ids = implode(',', $idsMarca);

            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $stmt = "SELECT   FROM OCS_TB_GRMA_GRUPO_MARCA 
			WHERE GRMA_ID_GRUPO_MAT_SERV = $grupoID";
            if (!is_null($flagTipo)) {
                $stmt .=" AND GRMA_IC_ATIVO = '$flagTipo' ";
            }
            $stmt .="AND GRMA_ID_MARCA NOT IN($ids)";

            return $db->query($stmt)->fetchAll();
        } else {
            return true;
        }
    }

    /**
     * Retorna todas as associaoes do grupo exceto as que estão no NOT IN clause 
     *  @param  array $idsMarca
     *  @Param  int $GrupoID  
     */
    public function getMarcasIn($grupoID, $idsMarca) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $ids = implode(',', $idsMarca);
        $stmt = "SELECT * FROM OCS_TB_GRMA_GRUPO_MARCA 
			WHERE GRMA_ID_GRUPO_MAT_SERV = $grupoID 
			AND GRMA_ID_MARCA IN($ids)";


        return $db->query($stmt)->fetchAll();
    }

    public function getMarcasAssociadas($idGrupo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT 
                GRMA_ID_MARCA, 
                GRMA_IC_ATIVO
            FROM
                OCS_TB_GRMA_GRUPO_MARCA
            WHERE
                GRMA_ID_GRUPO_MAT_SERV = $idGrupo
        ";
        return $db->query($stmt)->fetchAll();
    }
    
    public function verificaMarcaAssociada($idGrupo, $idMarca){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT 
                GRMA_ID_MARCA, 
                GRMA_IC_ATIVO
            FROM
                OCS_TB_GRMA_GRUPO_MARCA
            WHERE
                GRMA_ID_GRUPO_MAT_SERV = $idGrupo AND
                GRMA_ID_MARCA = $idMarca
        ";
        $result = $db->query($stmt)->fetch();
        if(count($result) > 0){
            return true;
        }else{
            return false;
        }
        
    }
}