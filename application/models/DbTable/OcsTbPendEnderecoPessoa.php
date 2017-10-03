<?php
class Application_Model_DbTable_OcsTbPendEnderecoPessoa extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PEND_ENDERECO_PESSOA';
    protected $_primary = 'PEND_ID_ENDERECO';
    protected $_sequence = 'OCS_SQ_PEND';
    
    public function getDadosEndereçoPessoa($tipo, $id){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($tipo == 'fisica'){
            $sql = "SELECT A.PNAT_ID_PESSOA, 
                            A.PNAT_NR_CPF, 
                            A.PNAT_NO_PESSOA, 
                            A.PNAT_NR_CNH, 
                            A.PNAT_SG_UF_CNH, 
                            A.PNAT_DT_EMISSAO_CNH, 
                            A.PNAT_DT_VALIDADE_CNH,
                            A.PNAT_IC_CATEGORIA_CNH,
                            A.PNAT_DT_NASCIMENTO,
                            A.PNAT_CD_LOCAL_NASCIMENTO,
                            A.PNAT_ID_ESTADO_CIVIL,
                            A.PNAT_NR_IDENTIDADE,
                            A.PNAT_SG_ORGAO_EMISSOR_ID,
                            A.PNAT_DH_EMISSAO_ID,
                            A.PNAT_IC_PESSOA,
                            B.PEND_ID_TP_ENDERECO,
                            B.PEND_NR_CEP,
                            B.PEND_DS_ENDERECO,
                            B.PEND_CD_LOCALIDADE,
                            B.PEND_IC_PRINCIPAL,
                            B.PEND_IC_EXCLUSAO_LOGICA 
                       FROM OCS_TB_PNAT_PESSOA_NATURAL A, 
                            OCS_TB_PEND_ENDERECO_PESSOA B,
                            OCS_TB_PJUR_PESSOA_JURIDICA C 
                      WHERE A.PNAT_ID_PESSOA = B.PEND_ID_PESSOA
                        AND A.PNAT_ID_PESSOA = $id";
        }else if($tipo == 'juridica'){
            $sql = "SELECT  C.PJUR_ID_PESSOA,
                            C.PJUR_NO_RAZAO_SOCIAL,
                            C.PJUR_NR_CNPJ,
                            C.PJUR_NO_FANTASIA,
                            C.PJUR_IC_PORTE,
                            B.PEND_ID_TP_ENDERECO,
                            B.PEND_NR_CEP,
                            B.PEND_DS_ENDERECO,
                            B.PEND_CD_LOCALIDADE,
                            B.PEND_IC_PRINCIPAL,
                            B.PEND_IC_EXCLUSAO_LOGICA 
                       FROM OCS_TB_PEND_ENDERECO_PESSOA B,
                            OCS_TB_PJUR_PESSOA_JURIDICA C 
                      WHERE C.PJUR_ID_PESSOA = B.PEND_ID_PESSOA
                        AND C.PJUR_ID_PESSOA = $id";
        }
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
 }