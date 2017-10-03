<?php
/**
 * Realiza a priorização das demandas 
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Sosti_Model_DataMapper_PriorizarDemanda extends Zend_Db_Table_Abstract
{
    /**
     * Grava a prioridade no banco de dados.
     * 
     * @param type $arrayPrioridade
     * @return string|boolean
     */
    public static function salvarOrdem($arrayPrioridade)
    {
        $dbTable = new Sosti_Model_DbTable_SosTbPrdePriorizaDemanda();
        $userNs = new Zend_Session_Namespace('userNs');
        try {
            $i=1;
            foreach ($arrayPrioridade as $p) {
                $data = explode('|', $p);
                if ($data[2] == "null") {
                    return 'Favor escolher o serviço!';
                }
                /** Verifica se já existe priorização na tabela */
                $countRows = $dbTable->fetchRow(
                    $dbTable->select()
                    ->from(array('PRDE' => 'SOS_TB_PRDE_PRIORIZA_DEMANDA'),
                        array('COUNT' => 'COUNT(*)'))
                    ->where("PRDE.PRDE_ID_CAIXA_ENTRADA = ?", $data[1])
                    ->where("PRDE.PRDE_ID_SERVICO = ?", $data[2])
                    ->where("PRDE.PRDE_ID_SOLICITACAO = ?", $data[0])
                );
                /** Se não existir na tabela insere, caso contrário, faz update */
                $arrayData[$i]['PRDE_ID_CAIXA_ENTRADA'] = $data[1];
                $arrayData[$i]['PRDE_ID_SERVICO'] = $data[2];
                $arrayData[$i]['PRDE_ID_SOLICITACAO'] = $data[0];
                $arrayData[$i]['PRDE_NR_PRIORIDADE'] = $i;
                $arrayData[$i]['PRDE_DH_PRIORIZACAO'] = new Zend_Db_Expr('SYSDATE');
                $arrayData[$i]['PRDE_CD_MATR_PRIORIZACAO'] = $userNs->matricula;
                if ($countRows->COUNT > 0) {
                    $where["PRDE_ID_CAIXA_ENTRADA = ?"] = $data[1];
                    $where["PRDE_ID_SERVICO = ?"] = $data[2];
                    $where["PRDE_ID_SOLICITACAO = ?"] = $data[0];
                    $dbTable->update($arrayData[$i], $where);
                } else {
//                    $arrayData[$i]['PRDE_ID_PRIORIZACAO'] = 6+$i;
                    $dbTable->insert($arrayData[$i]);
                }
                $i++;
            }
            return true;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    /**
     * Lista de demadas filtradas por grupo de serviço e por serviço. 
     * 
     * @param type $grupo
     * @param type $arrayDataPesq
     * @param type $order
     * @return type
     */
    public static function demadasPorServico($grupo, $arrayDataPesq, $order)
    {
        $cxSemNivel = new Sosti_Model_DataMapper_CaixaSemNivel();
        $faseAdm = new Application_Model_DbTable_SadTbFadmFaseAdm();
        $queryCaixa = $cxSemNivel->getQuery($grupo, $arrayDataPesq, $order, false, false, true);
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $arrayData = $db->fetchAll($queryCaixa);
        foreach ($arrayData as $k=>$d) {
            $arrayDataDscFase[] =  $d;
            $descricaoFase = $faseAdm
                    ->fetchRow("FADM_ID_FASE = ".$d["MOFA_ID_FASE"])
                    ->toArray();
            $arrayDataDscFase[$k]['FADM_DS_FASE'] = $descricaoFase['FADM_DS_FASE'];
        }
        return $arrayDataDscFase;
    }
}