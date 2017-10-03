<?php
class Application_Model_DbTable_SadTbCadoCategoriaDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_CADO_CATEGORIA_DOC';
    protected $_primary = array('CADO_ID_CATEGORIA',
                                'CADO_ID_DOCUMENTO'); 
    
    /**
     *
     * @param type $idDocs
     * @param type $matricula
     * @param type $secao
     * @param type $lotacao
     * @return type array de Categorias da UNIDADE
     */
    public function getCategoriaDocs($idDocs, $matricula = null, $secao = null, $lotacao = null)
    {
        if($matricula){
            $dado = "A.CATE_CD_MATRICULA_CATEGORIA = '$matricula'";
        }else if($secao && $lotacao){
            $dado = "    A.CATE_SG_SECAO_CATEGORIA = '$secao'
                     AND A.CATE_CD_LOTACAO_CATEGORIA = $lotacao";
        }
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT A.CATE_ID_CATEGORIA, 
                                            A.CATE_NM_CATEGORIA,
                                            A.CATE_DS_DESCRICAO_COR, 
                                            A.CATE_DS_OBSERVACAO
                                       FROM SAD_TB_CATE_CATEGORIA A
                                 INNER JOIN SAD_TB_CADO_CATEGORIA_DOC B
                                         ON A.CATE_ID_CATEGORIA = B.CADO_ID_CATEGORIA
                                      WHERE ".$dado."
                                        AND CADO_ID_DOCUMENTO IN ($idDocs)
                                        AND CADO_CD_MATRICULA_INATIVACAO IS NULL
                                        AND CADO_DH_INATIVACAO_CATEGORIA IS NULL");
        return $stmt->fetchAll();
    }
    public function getCategoriasUnidadePessoa($matricula = null, $secao = null, $lotacao = null){
        if($matricula){
            $dado = "A.CATE_CD_MATRICULA_CATEGORIA = '$matricula'";
        }else if($secao && $lotacao){
            $dado = "    A.CATE_SG_SECAO_CATEGORIA = '$secao'
                   AND A.CATE_CD_LOTACAO_CATEGORIA = $lotacao";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT A.CATE_ID_CATEGORIA, 
                                            A.CATE_NM_CATEGORIA,
                                            A.CATE_DS_DESCRICAO_COR, 
                                            A.CATE_DS_OBSERVACAO,
                                            A.CATE_IC_ATIVO
                                       FROM SAD_TB_CATE_CATEGORIA A
                                      WHERE ".$dado);
        return $stmt->fetchAll();
    }
    
}