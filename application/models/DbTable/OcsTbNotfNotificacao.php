<?php
/**
 * Model para criacao, delecao e ler notificacoes dos sistemas.
 */

class Application_Model_DbTable_OcsTbNotfNotificacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_NOTF_NOTIFICACAO';
    protected $_primary = array('NOTF_CD_MATRICULA','NOTF_DH_NOTIFICACACAO');
 
    public function getnotfCount($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) AS COUNT
                            FROM OCS_TB_NOTF_NOTIFICACAO
                            WHERE NOTF_CD_MATRICULA = '$matricula'
                            AND NOTF_DH_LEITURA IS NULL"
        );
        return $stmt->fetchAll();
    }
    
    public function getnotfcountforDelete($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(*) AS LIDAS
                            FROM OCS_TB_NOTF_NOTIFICACAO
                            WHERE NOTF_CD_MATRICULA = '$matricula'
                            AND NOTF_DH_LEITURA IS NOT NULL"
        );
        return $stmt->fetch();
    }
    
    public function getNotf($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT NOTF_CD_MATRICULA,
                                   NOTF_DH_NOTIFICACACAO,
                                   TO_CHAR(NOTF_DH_NOTIFICACACAO,'DD/MM/YYYY HH24:MI:SS,FF6')MILI,
                                   TO_CHAR(NOTF_DH_NOTIFICACACAO,'DD/MM/YYYY HH24:MI:SS')DATA_FORMATADA,
                                   NOTF_NM_SISTEMA_INTRODUTOR,
                                   NOTF_ID_TIPO,
                                   NOTF_DS_TITULO,
                                   NOTF_DS_NOTIFICACAO,
                                   NOTF_DH_LEITURA
                            FROM OCS_TB_NOTF_NOTIFICACAO
                            WHERE NOTF_CD_MATRICULA = '$matricula'
                            AND NOTF_DH_LEITURA IS NULL"
        );
        return $stmt->fetchAll();
    }
    
    public function getnotfCaixa($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT NOTF_CD_MATRICULA,
                                   NOTF_DH_NOTIFICACACAO,
                                   TO_CHAR(NOTF_DH_NOTIFICACACAO,'DD/MM/YYYY HH24:MI:SS,FF6')MILI,
                                   TO_CHAR(NOTF_DH_NOTIFICACACAO,'DD/MM/YYYY HH24:MI:SS')DATA_FORMATADA,
                                   NOTF_NM_SISTEMA_INTRODUTOR,
                                   NOTF_ID_TIPO,
                                   NOTF_DS_TITULO,
                                   NOTF_DS_NOTIFICACAO,
                                   TO_CHAR(NOTF_DH_LEITURA,'DD/MM/YYYY HH24:MI:SS')NOTF_DH_LEITURA
                            FROM OCS_TB_NOTF_NOTIFICACAO
                            WHERE NOTF_CD_MATRICULA = '$matricula'
                            AND NOTF_DH_LEITURA IS NOT NULL
                            ORDER BY NOTF_DH_NOTIFICACACAO DESC"
        );
        return $stmt->fetchAll();
    }
    
    public function setnotfAction($matricula, $titulo, $sistema, $mensagem) {
        $dual = new Application_Model_DbTable_Dual();
        $dh_ts = $dual->localtimestampDb();

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            $total_lidas = $this->getnotfcountforDelete($matricula);
            if ($total_lidas["LIDAS"] < 200) {

                $arrayDadosNotf["NOTF_CD_MATRICULA"] = $matricula;
                $arrayDadosNotf["NOTF_DH_NOTIFICACACAO"] = $dh_ts['DATA'];
                $arrayDadosNotf["NOTF_NM_SISTEMA_INTRODUTOR"] = $sistema;
                $arrayDadosNotf["NOTF_ID_TIPO"] = 1;
                $arrayDadosNotf["NOTF_DS_TITULO"] = $titulo;
                $arrayDadosNotf["NOTF_DS_NOTIFICACAO"] = $mensagem;
                $arrayDadosNotf["NOTF_DH_LEITURA"] = NULL; /* NOTIFICAÇÃO ORIGINAL NÃO DH LEITURA */

                $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                $tabelaNotfAuditoria = new Application_Model_DbTable_OcsTbNotfAuditoria();
                $rowNotf = $tabelaNotf->createRow($arrayDadosNotf);
                $rowNotf->save();

                $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
                $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'I';
                $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $matricula;
                $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                $arrayDadosNotfAuditoria["NEW_NOTF_CD_MATRICULA"] = $matricula;
                $arrayDadosNotfAuditoria["NEW_NOTF_DH_NOTIFICACACAO"] = $arrayDadosNotf["NOTF_DH_NOTIFICACACAO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_NM_SISTEMA_INTRODUTOR"] = $arrayDadosNotf["NOTF_NM_SISTEMA_INTRODUTOR"];
                $arrayDadosNotfAuditoria["NEW_NOTF_ID_TIPO"] = $arrayDadosNotf["NOTF_ID_TIPO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_DS_TITULO"] = $arrayDadosNotf["NOTF_DS_TITULO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_DS_NOTIFICACAO"] = $arrayDadosNotf["NOTF_DS_NOTIFICACAO"];
                $arrayDadosNotfAuditoria["NES_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
                $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
                $rowNotfAuditoria->save();
            } else {
                if ($total_lidas["LIDAS"] == 200) {
				$tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                $rowNotf = $tabelaNotf->fetchRow("NOTF_CD_MATRICULA = '$matricula' AND NOTF_DH_LEITURA IS NOT NULL", "NOTF_DH_NOTIFICACACAO");
                $rowNotf->delete();

                $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
                $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'E';
                $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $matricula;
                $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                $arrayDadosNotfAuditoria["NEW_NOTF_CD_MATRICULA"] = $matricula;
                $arrayDadosNotfAuditoria["NEW_NOTF_DH_NOTIFICACACAO"] = $arrayDadosNotf["NOTF_DH_NOTIFICACACAO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_NM_SISTEMA_INTRODUTOR"] = $arrayDadosNotf["NOTF_NM_SISTEMA_INTRODUTOR"];
                $arrayDadosNotfAuditoria["NEW_NOTF_ID_TIPO"] = $arrayDadosNotf["NOTF_ID_TIPO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_DS_TITULO"] = $arrayDadosNotf["NOTF_DS_TITULO"];
                $arrayDadosNotfAuditoria["NEW_NOTF_DS_NOTIFICACAO"] = $arrayDadosNotf["NOTF_DS_NOTIFICACAO"];
                $arrayDadosNotfAuditoria["NES_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
				$tabelaNotfAuditoria = new Application_Model_DbTable_OcsTbNotfAuditoria();
                $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
                $rowNotfAuditoria->save();
                } else {
                    $excedente = $total_lidas["LIDAS"] - 200;
                    for ($index = 0; $index < $excedente; $index++) {
                        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                        $rowNotf = $tabelaNotf->fetchRow("NOTF_CD_MATRICULA = '$matricula' AND NOTF_DH_LEITURA IS NOT NULL", "NOTF_DH_NOTIFICACACAO");
                        $rowNotf->delete();

                        $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
                        $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'E';
                        $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $matricula;
                        $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                        $arrayDadosNotfAuditoria["NEW_NOTF_CD_MATRICULA"] = $matricula;
                        $arrayDadosNotfAuditoria["NEW_NOTF_DH_NOTIFICACACAO"] = $arrayDadosNotf["NOTF_DH_NOTIFICACACAO"];
                        $arrayDadosNotfAuditoria["NEW_NOTF_NM_SISTEMA_INTRODUTOR"] = $arrayDadosNotf["NOTF_NM_SISTEMA_INTRODUTOR"];
                        $arrayDadosNotfAuditoria["NEW_NOTF_ID_TIPO"] = $arrayDadosNotf["NOTF_ID_TIPO"];
                        $arrayDadosNotfAuditoria["NEW_NOTF_DS_TITULO"] = $arrayDadosNotf["NOTF_DS_TITULO"];
                        $arrayDadosNotfAuditoria["NEW_NOTF_DS_NOTIFICACAO"] = $arrayDadosNotf["NOTF_DS_NOTIFICACAO"];
                        $arrayDadosNotfAuditoria["NES_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
                        $tabelaNotfAuditoria = new Application_Model_DbTable_OcsTbNotfAuditoria();
                        $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
                        $rowNotfAuditoria->save();
            }
                }
            $db->commit();
                $confirmado = $this->setnotfAction($matricula, $sistema, $mensagem);
                if ($confirmado) {
                    return true;
                }
            }
            $db->commit();
            return true;
        } catch (Exception $exc) {
            $db->rollBack();
            return $exc->getMessage();
        }
    }
    
    public function setdeletenotf($matricula,$datahora) {
        
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try{
            $userNs = new Zend_Session_Namespace('userNs');
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $tabelaNotfAuditoria = new Application_Model_DbTable_OcsTbNotfAuditoria();
            $dual = new Application_Model_DbTable_Dual();
            $dh_ts = $dual->localtimestampDb();

            /* Leitura da Notificação */
            $rowNotf = $tabelaNotf->fetchRow("NOTF_CD_MATRICULA = '$matricula' AND NOTF_DH_NOTIFICACACAO = TO_TIMESTAMP('$datahora','DD/MM/RR HH24:MI:SS,FF6')");
            $rowNotfDadosAuditoria = $rowNotf->toArray();
            $arrayDadosNotf["NOTF_DH_LEITURA"] = new Zend_Db_Expr("SYSDATE");
            $rowNotf->setFromArray($arrayDadosNotf);
            $rowNotf->save();
            
            $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
            $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'A';
            $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
            $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
            $arrayDadosNotfAuditoria["NEW_NOTF_CD_MATRICULA"] = $rowNotfDadosAuditoria['NOTF_CD_MATRICULA'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DH_NOTIFICACACAO"] = $rowNotfDadosAuditoria['NOTF_DH_NOTIFICACACAO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_NM_SISTEMA_INTRODUTOR"] = $rowNotfDadosAuditoria['NOTF_NM_SISTEMA_INTRODUTOR'];
            $arrayDadosNotfAuditoria["NEW_NOTF_ID_TIPO"] = $rowNotfDadosAuditoria['NOTF_ID_TIPO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DS_TITULO"] = $rowNotfDadosAuditoria['NOTF_DS_TITULO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DS_NOTIFICACAO"] = $rowNotfDadosAuditoria['NOTF_DS_NOTIFICACAO'];
            $arrayDadosNotfAuditoria["NES_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
            $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
            $rowNotfAuditoria->save();


            /* Exclusão da Notificação */
            $rowNotf = $tabelaNotf->fetchRow("NOTF_CD_MATRICULA = '$matricula' AND NOTF_DH_NOTIFICACACAO = TO_TIMESTAMP('$datahora','DD/MM/RR HH24:MI:SS,FF6')");
            $rowNotf->delete();
            
            $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
            $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'E';
            $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
            $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
            $arrayDadosNotfAuditoria["OLD_NOTF_CD_MATRICULA"] = $rowNotfDadosAuditoria['NOTF_CD_MATRICULA'];
            $arrayDadosNotfAuditoria["OLD_NOTF_DH_NOTIFICACACAO"] = $rowNotfDadosAuditoria['NOTF_DH_NOTIFICACACAO'];
            $arrayDadosNotfAuditoria["OLD_NOTF_NM_SISTEMA_INTRODUTOR"] = $rowNotfDadosAuditoria['NOTF_NM_SISTEMA_INTRODUTOR'];
            $arrayDadosNotfAuditoria["OLD_NOTF_ID_TIPO"] = $rowNotfDadosAuditoria['NOTF_ID_TIPO'];
            $arrayDadosNotfAuditoria["OLD_NOTF_DS_TITULO"] = $rowNotfDadosAuditoria['NOTF_DS_TITULO'];
            $arrayDadosNotfAuditoria["OLD_NOTF_DS_NOTIFICACAO"] = $rowNotfDadosAuditoria['NOTF_DS_NOTIFICACAO'];
            $arrayDadosNotfAuditoria["OLD_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
            $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
            $rowNotfAuditoria->save();
            
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
    }
    
    public function setnotflida($matricula,$datahora) {
        
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try{
            $userNs = new Zend_Session_Namespace('userNs');
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $tabelaNotfAuditoria = new Application_Model_DbTable_OcsTbNotfAuditoria();
            $dual = new Application_Model_DbTable_Dual();
            $dh_ts = $dual->localtimestampDb();

            /* Leitura da Notificação */
            $rowNotf = $tabelaNotf->fetchRow("NOTF_CD_MATRICULA = '$matricula' AND NOTF_DH_NOTIFICACACAO = TO_TIMESTAMP('$datahora','DD/MM/RR HH24:MI:SS,FF6')");
            $rowNotfDadosAuditoria = $rowNotf->toArray();
            $arrayDadosNotf["NOTF_DH_LEITURA"] = new Zend_Db_Expr("SYSDATE");
            $rowNotf->setFromArray($arrayDadosNotf);
            $rowNotf->save();
            
            $arrayDadosNotfAuditoria["NOTF_TS_OPERACAO"] = $dh_ts['DATA'];
            $arrayDadosNotfAuditoria["NOTF_CD_OPERACAO"] = 'A';
            $arrayDadosNotfAuditoria["NOTF_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
            $arrayDadosNotfAuditoria["NOTF_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $arrayDadosNotfAuditoria["NOTF_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
            $arrayDadosNotfAuditoria["NEW_NOTF_CD_MATRICULA"] = $rowNotfDadosAuditoria['NOTF_CD_MATRICULA'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DH_NOTIFICACACAO"] = $rowNotfDadosAuditoria['NOTF_DH_NOTIFICACACAO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_NM_SISTEMA_INTRODUTOR"] = $rowNotfDadosAuditoria['NOTF_NM_SISTEMA_INTRODUTOR'];
            $arrayDadosNotfAuditoria["NEW_NOTF_ID_TIPO"] = $rowNotfDadosAuditoria['NOTF_ID_TIPO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DS_TITULO"] = $rowNotfDadosAuditoria['NOTF_DS_TITULO'];
            $arrayDadosNotfAuditoria["NEW_NOTF_DS_NOTIFICACAO"] = $rowNotfDadosAuditoria['NOTF_DS_NOTIFICACAO'];
            $arrayDadosNotfAuditoria["OLD_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
            $arrayDadosNotfAuditoria["NES_NOTF_DH_LEITURA"] = $arrayDadosNotf["NOTF_DH_LEITURA"];
            $rowNotfAuditoria = $tabelaNotfAuditoria->createRow($arrayDadosNotfAuditoria);
            $rowNotfAuditoria->save();
            
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
    }
}