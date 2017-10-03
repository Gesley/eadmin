<?php
class Application_Model_DbTable_SosTbSspaAuditoria extends Zend_Db_Table_Abstract
{
    protected $_name = 'SOS_TB_SSPA_AUDITORIA';
    protected $_primary = array('SSPA_ID_MOVIMENTACAO' , 'SSPA_DH_FASE');

    /**
     * Define um prazo para uma Solicitação
     * Recebe como parametros de entrada
     * @param $idDocumento
     * @param $dataMofaMoviFase array()
     * @param $dataSspaSolicPrazoAtend array()
     * @return void
     */
    public function prazoSolicitacao($idDocumento, array $dataMofaMoviFase, array $dataSspaSolicPrazoAtend)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();
            /**
             * Lança a nova fase na tabela SAD_TB_MOFA_MOVI_FASE
             */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();
            /**
             * Cria uma nova solicitação de prazo na tabela SOS_TB_SSPA_SOLIC_PRAZO_ATEND
             */
            $SosTbSspaSolicPrazoAtend = new Application_Model_DbTable_SosTbSspaSolicPrazoAtend(); 
            $dataSspaSolicPrazoAtend["SSPA_DH_FASE"] = $datahora;
            $rowSspaSolicPrazoAtend = $SosTbSspaSolicPrazoAtend->createRow($dataSspaSolicPrazoAtend);
            $rowSspaSolicPrazoAtend->save();
            
            
            
            
            
            
            //Ultima Fase do lançada na Solicitação.//
            /*----------------------------------------------------------------------------------------*/
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $SadTbDocmDocumento->find($idDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();
            /*----------------------------------------------------------------------------------------*/
            /**
             * Efetiva a inserção nas duas tabelas
             */
             $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }
    
    /**
     * Autoriza a solicitação de prazo para a solicitação
     * Recebe como parametros de entrada
     * @param $idDocumento
     * @param $dataMofaMoviFase array()
     * @param $dataSspaSolicPrazoAtend array()
     * @return void
     */
    public function autorizaPrazoSolicitacao($idDocumento, array $dataMofaMoviFase, array $dataSspaSolicPrazoAtend)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();
            /**
             * Lança a nova fase na tabela SAD_TB_MOFA_MOVI_FASE
             */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();
            /**
             * Atualiza a SSPA_IC_CONFIRMACAO na tabela SOS_TB_SSPA_SOLIC_PRAZO_ATEND para confirmar a solicitação do prazo
             */
            
            $q = "UPDATE SOS_TB_SSPA_SOLIC_PRAZO_ATEND
                                SET SSPA_IC_CONFIRMACAO = '".$dataSspaSolicPrazoAtend["SSPA_IC_CONFIRMACAO"]."'
                                WHERE SSPA_ID_MOVIMENTACAO = ".$dataSspaSolicPrazoAtend["SSPA_ID_MOVIMENTACAO"]
                              ."AND TO_CHAR(SSPA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') = (SELECT TO_CHAR(MAX(SSPA_DH_FASE),'DD/MM/YYYY HH24:MI:SS') SSPA_DH_FASE 
                                                        FROM   SOS_TB_SSPA_SOLIC_PRAZO_ATEND A
                                                        WHERE  A.SSPA_ID_MOVIMENTACAO = ".$dataSspaSolicPrazoAtend["SSPA_ID_MOVIMENTACAO"].")"    
                    ;
            $stmt = $db->query($q);
            //Ultima Fase do lançada na Solicitação.//
            /*----------------------------------------------------------------------------------------*/
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $SadTbDocmDocumento->find($idDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();
            /*---------------------------------------------------------------------------------------*/
            /**
             * Efetiva as transações nas duas tabelas
             */
             $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }
}