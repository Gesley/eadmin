<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SosTbCasoCategoriaSolic extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_CASO_CATEGORIA_SOLIC';
    protected $_primary = array('CASO_ID_DOCUMENTO','CASO_ID_CATEGORIA');
    
    public function setIncluirCategoria($casoCategoria){
        $SosTbCasoCategoriaSolic = new Application_Model_DbTable_SosTbCasoCategoriaSolic();
        $rowCategoria = $SosTbCasoCategoriaSolic->find($casoCategoria['CASO_ID_DOCUMENTO'],$casoCategoria['CASO_ID_CATEGORIA'])->current();
        if($rowCategoria != null){
            $casoCategoria["CASO_DH_INATIVACAO_CATEGORIA"] = null;
            $casoCategoria["CASO_CD_MATRICULA_INATIVACAO"] = null;
            $rowCategoria->setFromArray($casoCategoria);
            $rowCategoria->save();
        }else{
            $rowCategoria = $SosTbCasoCategoriaSolic->createRow($casoCategoria);
            $rowCategoria->save();
        }
    }
    
    public function setExcluirCategoria($casoCategoria){
        $SosTbCasoCategoriaSolic = new Application_Model_DbTable_SosTbCasoCategoriaSolic();
        
        $rowCateCategoria = $SosTbCasoCategoriaSolic->find($casoCategoria['CASO_ID_DOCUMENTO'],$casoCategoria['CASO_ID_CATEGORIA'])->current();
        if($rowCateCategoria != null){
            $rowCateCategoria->setFromArray($casoCategoria);
            $rowCateCategoria->save();
        }
    }
    
    public function getCategoriasDocumentoPessoal($idDocumento,$identificador = null, $tipo = null){
        $userNs = new Zend_Session_Namespace('userNs');
        if($tipo == 1){
            $qr = "AND A.CATE_ID_GRUPO = $identificador";
        }else if($tipo == 2 || ($identificador == null && $tipo == null)){
            $qr = "AND A.CATE_CD_MATRICULA_CATEGORIA = '$userNs->matricula'";
        }else if($tipo == 3){
            $qr = "AND A.CATE_ID_NIVEL = $identificador";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.CATE_ID_CATEGORIA, 
                                   A.CATE_NO_CATEGORIA, 
                                   A.CATE_ID_GRUPO,
                                   A.CATE_ID_NIVEL, 
                                   A.CATE_CD_MATRICULA_CATEGORIA,
                                   A.CATE_DS_OBSERVACAO, 
                                   A.CATE_DS_DESCRICAO_COR, 
                                   A.CATE_IC_ATIVO,
                                   B.CASO_ID_DOCUMENTO, 
                                   B.CASO_ID_CATEGORIA,
                                   B.CASO_CD_MATRICULA_OPERACAO, 
                                   B.CASO_DH_CATEGORIA_SOLICITACAO
                              FROM SOS.SOS_TB_CATE_CATEGORIA A,SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                             WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                               AND B.CASO_ID_DOCUMENTO = $idDocumento
                               AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                               AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                               ".$qr."
                             ORDER BY A.CATE_NO_CATEGORIA");
        return $stmt->fetchAll();
    }
    public function getCategoriasDocumentoGrupo($idDocumento,$identificador,$tipo){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CateNs = new Zend_Session_Namespace('CateNs');
        $qr = "SELECT A.CATE_ID_CATEGORIA, 
                       A.CATE_NO_CATEGORIA, 
                       A.CATE_ID_GRUPO,
                       A.CATE_ID_NIVEL, 
                       A.CATE_CD_MATRICULA_CATEGORIA,
                       A.CATE_DS_OBSERVACAO, 
                       A.CATE_DS_DESCRICAO_COR, 
                       A.CATE_IC_ATIVO,
                       B.CASO_ID_DOCUMENTO, 
                       B.CASO_ID_CATEGORIA,
                       B.CASO_CD_MATRICULA_OPERACAO, 
                       B.CASO_DH_CATEGORIA_SOLICITACAO
                  FROM SOS.SOS_TB_CATE_CATEGORIA A,SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                 WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                   AND B.CASO_ID_DOCUMENTO = $idDocumento
                   AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                   AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL ";
                    if($tipo == 'GRUPO' && !empty($identificador)){
                        $qr .= " 
                                AND A.CATE_ID_GRUPO = $identificador 
                                  ORDER BY A.CATE_NO_CATEGORIA";
                    }else if($tipo == 'NIVEL'){
                        $qr .= " 
                                AND A.CATE_ID_NIVEL = $identificador 
                                  ORDER BY A.CATE_NO_CATEGORIA";
                    }
        $stmt = $db->query("$qr");
        return $stmt->fetchAll();
    }
    public function getDescategorizarDocumentos($ids){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CateNs = new Zend_Session_Namespace('CateNs');
        $query = "SELECT DISTINCT A.CATE_ID_CATEGORIA, 
                                   A.CATE_NO_CATEGORIA, 
                                   A.CATE_DS_OBSERVACAO, 
                                   A.CATE_DS_DESCRICAO_COR 
                              FROM SOS.SOS_TB_CATE_CATEGORIA A,SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                             WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                               AND B.CASO_ID_DOCUMENTO IN ($ids)
                               AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                             ";
                               if($CateNs->tipo == 1){
                              $query .= " AND A.CATE_ID_GRUPO = $CateNs->idGrupo
                                   ORDER BY A.CATE_NO_CATEGORIA";
                               }else if($CateNs->tipo == 2){
                              $query .= " AND A.CATE_CD_MATRICULA_CATEGORIA = '$CateNs->identificador'
                                    ORDER BY A.CATE_NO_CATEGORIA";
                               }else if($CateNs->tipo == 3){
                              $query .= "AND A.CATE_ID_NIVEL = $CateNs->identificador
                                    ORDER BY A.CATE_NO_CATEGORIA";  
                               }
        $stmt = $db->query("$query");
        return $stmt->fetchAll();
    }
}