<?php 
class Application_Model_DbTable_PSecaoSubsecao extends Zend_Db_Table_Abstract
{
    protected $_name = 'P_SECAO_SUBSECAO';

    public function getFusoHorario($uf) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        /**
         * Verifica se existe horário de verão em Brasília
         */
        $stmtHdf = $db->query("SELECT SESU_ST_HORARIO_VERAO H_VERAO_DF
                               FROM P_SECAO_SUBSECAO
                               WHERE SESU_CAP_UF = 'DF' ");
        $dataHdf = $stmtHdf->fetch();
        /**
         * Verifica se existe horário de verão na seção
         */
        $custUf = $uf === 'TR' ? 'DF' : $uf;
        $stmtHuf = $db->query("SELECT SESU_ST_HORARIO_VERAO H_VERAO_UF,
                                      SESU_FUSO_HORARIO F_HORARIO_UF
                               FROM P_SECAO_SUBSECAO
                               WHERE SESU_CAP_UF = '".$custUf ."' ");
        $dataHuf = $stmtHuf->fetch();
        /**
         * Se existir horário de verão em Brasília
         */
        if($dataHdf['H_VERAO_DF'] == 1) {
            /**
             * Existindo horário de verão em Brasília e não existindo na seção subtrai uma hora
             */
            if($dataHuf['H_VERAO_UF'] == 0) {
                $hVerao = -1;
            } else {
                $hVerao = 0;
            }
        /**
         * Não existindo horário de verão em Brasília
         */
        } else {
           /**
            * Não existindo horário de verão em Brasília e existindo horário de verão na seção
            * soma uma hora
            */
            if($dataHuf['H_VERAO_UF'] == 1) {
                $hVerao = 1;
            } else {
                $hVerao = 0;
            }
        }
        /**
         * Verifica o fuso horário em relação à Brasília
         */
        $hFuso = $dataHuf['F_HORARIO_UF'];
        return ($hFuso)+($hVerao);
    }
}