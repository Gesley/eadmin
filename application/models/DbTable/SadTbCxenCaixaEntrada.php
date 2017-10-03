<?php
class Application_Model_DbTable_SadTbCxenCaixaEntrada extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_CXEN_CAIXA_ENTRADA';
    protected $_primary = array('CXEN_ID_CAIXA_ENTRADA');
    
    public function getSecaoUnidadebycaixa($idCaixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO
                                FROM SAD_TB_CXEN_CAIXA_ENTRADA CXEN , SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, RH_CENTRAL_LOTACAO RHLOTA
                                WHERE CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                AND CXLO.CXLO_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                AND CXEN.CXEN_ID_CAIXA_ENTRADA = $idCaixa
                                ORDER BY RHLOTA.LOTA_SIGLA_LOTACAO");
        return $stmt->fetchAll();
    }
    
    public function getCaixaByServico($idCaixa,$idServico)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO, CXEN.CXEN_ID_CAIXA_ENTRADA, CXEN.CXEN_DS_CAIXA_ENTRADA
                                FROM SAD_TB_CXEN_CAIXA_ENTRADA CXEN, 
                                     SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                     RH_CENTRAL_LOTACAO RHLOTA, 
                                     SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                     SOS_TB_SSER_SERVICO SSER 
                                WHERE CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                AND CXLO.CXLO_CD_SECAO = RHLOTA.LOTA_COD_LOTACAO
                                AND SGRS.SGRS_SG_SECAO_LOTACAO = CXLO.CXLO_SG_SECAO
                                AND SGRS.SGRS_CD_LOTACAO = CXLO.CXLO_CD_SECAO
                                AND SSER.sser_id_grupo =  SGRS.sgrs_id_grupo 
                                AND SSER.sser_id_servico = $idServico
                                AND CXLO.CXLO_ID_CAIXA_ENTRADA <> $idCaixa
                                ORDER BY RHLOTA.LOTA_SIGLA_LOTACAO");
        $rows = $stmt->fetchAll();
        if(!$rows){
            $rows[0]['CXEN_ID_CAIXA_ENTRADA'] = '';
            $rows[0]['CXEN_DS_CAIXA_ENTRADA'] = 'Já encontra-se no Grupo';
            
        }
        return $rows;
    }
    
    public function getUnidadeByCaixaServico($idCaixa,$idServico)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT RHLOTA.LOTA_SIGLA_SECAO, RHLOTA.LOTA_COD_LOTACAO, RHLOTA.LOTA_SIGLA_LOTACAO, RHLOTA.LOTA_DSC_LOTACAO, CXEN.cxen_id_caixa_entrada, CXEN.cxen_ds_caixa_entrada
                                FROM SAD_TB_CXEN_CAIXA_ENTRADA CXEN, 
                                     SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                     RH_CENTRAL_LOTACAO RHLOTA, 
                                     SOS_TB_SGRS_GRUPO_SERVICO SGRS, 
                                     SOS_TB_SSER_SERVICO SSER 
                                WHERE CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                AND CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                AND CXLO.CXLO_CD_SECAO = RHLOTA.LOTA_COD_LOTACAO
                                AND SGRS.SGRS_SG_SECAO_LOTACAO = CXLO.CXLO_SG_SECAO
                                AND SGRS.SGRS_CD_LOTACAO IN  (SELECT CXLO.CXLO_CD_SECAO
                                                                FROM SAD_TB_CXEN_CAIXA_ENTRADA CXEN, 
                                                                        SAD_TB_CXLO_CAIXA_ENTRADA_LOT CXLO, 
                                                                        RH_CENTRAL_LOTACAO RHLOTA
                                                                WHERE CXLO.CXLO_SG_SECAO = RHLOTA.LOTA_SIGLA_SECAO
                                                                        AND CXLO.CXLO_CD_SECAO = RHLOTA.LOTA_COD_LOTACAO
                                                                        AND CXEN.CXEN_ID_CAIXA_ENTRADA = CXLO.CXLO_ID_CAIXA_ENTRADA
                                                                        AND  CXEN.CXEN_ID_CAIXA_ENTRADA = $idCaixa) 
                                AND SSER.SSER_ID_GRUPO =  SGRS.SGRS_ID_GRUPO 
                                AND SSER.SSER_ID_SERVICO = $idServico
                                AND CXLO.CXLO_ID_CAIXA_ENTRADA = $idCaixa
                                ORDER BY RHLOTA.LOTA_SIGLA_LOTACAO");
        $rows = $stmt->fetchAll();
        if(!$rows){
            $rows[0]['LOTA_COD_LOTACAO'] = '';
            $rows[0]['LOTA_SIGLA_LOTACAO'] = '';
            $rows[0]['LOTA_DSC_LOTACAO'] = 'Somente o Grupo de Atendimento responsável pode enviar para Unidade.';
        }
        return $rows;
    }
    
    
    public function getCaixas($order)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT CXEN.CXEN_ID_CAIXA_ENTRADA, 
                                    CXEN.CXEN_DS_CAIXA_ENTRADA,
                                    CXEN.CXEN_DT_INCLUSAO, 
                                    CXEN.CXEN_CD_MATRICULA_INCLUSAO,
                                    CXEN.CXEN_DT_EXCLUSAO, 
                                    CXEN.CXEN_CD_MATRICULA_EXCLUSAO,
                                    CXEN.CXEN_ID_TIPO_CAIXA,
                                    TPCX.TPCX_DS_CAIXA_ENTRADA,
                                    TPCX.TPCX_DS_PROPRIETARIO_CAIXA
                            FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,  
                                SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                SISTEMAS_TRF SIS
                                WHERE TPCX.TPCX_ID_TIPO_CAIXA = CXEN.CXEN_ID_TIPO_CAIXA
                                AND   SIS.NOME_SISTEMA = TPCX.TPCX_DS_PROPRIETARIO_CAIXA
                                ORDER BY $order");

        return $stmt->fetchAll();
    }
    
    public function getCaixaEntrada($idCaixa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = 'SELECT * '
                . 'FROM SAD_TB_CXEN_CAIXA_ENTRADA '
                . 'WHERE CXEN_ID_CAIXA_ENTRADA = '.$idCaixa;
        $stmt = $db->query($q);
        return $stmt->fetchObject();
    }

}
