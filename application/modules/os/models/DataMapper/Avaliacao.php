<?php
/**
 * Validações das OS
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Os_Model_DataMapper_Avaliacao extends Zend_Db_Table_Abstract
{
    public static $_userNs = "";
    private static $_tarefaSolicit = "";
    private static $_solicitacaoOs = "";

    public function __construct() 
    {
    }
    
    public static function avaliarPositivamenteStatusMsg($jsonArraySolicit)
    {
        self::$_solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        self::$_tarefaSolicit = new Tarefa_Model_DataMapper_TarefaSolicitacao();
        foreach ($jsonArraySolicit as $k=>$d) {
            $arraySol[] = Zend_Json::decode($d);
            /** Verifica se é OS **/
            if (self::$_solicitacaoOs->getVerificaSeOs($arraySol[$k]["SSOL_ID_DOCUMENTO"])) {
                $arrayResult[] = count(self::$_tarefaSolicit->fetchAll(
                    "TASO_IC_SITUACAO_NEGOCIACAO <> 3 AND TASO_ID_DOCUMENTO = ".$arraySol[$k]["SSOL_ID_DOCUMENTO"]
                )) ?: false;
            }
        }
        /** Verifica se possui defeitos não homologados **/
        if (in_array(!false, $arrayResult)) {
            return array('status' => false, 'message' => 'Essa solicitação possui defeitos que não foram homologados.');
        } else {
            return array('status' => true, 'message' => 'Todos os defeitos foram homologados.');
        }
    }
    
    public static function recusarStatusMsg($jsonArraySolicit)
    {
        self::$_solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        self::$_tarefaSolicit = new Tarefa_Model_DataMapper_TarefaSolicitacao();
        foreach ($jsonArraySolicit as $k=>$d) {
            $arraySol[] = Zend_Json::decode($d);
            /** Verifica se é OS **/
            if (self::$_solicitacaoOs->getVerificaSeOs($arraySol[$k]["SSOL_ID_DOCUMENTO"])) {
                /** Carrega array com solictações que possuem defeitos para homologação **/
                $arrayResultHomologacao[] = count(self::$_tarefaSolicit->fetchAll(
                    "TASO_IC_SITUACAO_NEGOCIACAO = 1 AND TASO_ID_DOCUMENTO = ".$arraySol[$k]["SSOL_ID_DOCUMENTO"]
                )) ?: false;
                /** Carrega array com solicitações que não possuem defeitos cadastrados **/
                $arrayResultSemDefeito[] = count(self::$_tarefaSolicit->fetchAll(
                    "TASO_ID_DOCUMENTO = ".$arraySol[$k]["SSOL_ID_DOCUMENTO"]
                )) > 0 ? false : true;
            }
        }
        if (in_array(!false, $arrayResultHomologacao) || in_array(true, $arrayResultSemDefeito)) {
            return array('status' => false, 'message' => 'Essa solicitação não possui defeitos ou possui defeitos que não foram homologados.');
        } else {
            return array('status' => true, 'message' => 'Essa soliictação possui defeitos homologados.');
        }
    }
    /**
     * Baixa as solicitações que deram origem a OS.
     * @param type $jsonArraySolicit
     */
    public static function baixaSolicitacoesOrigemOs($jsonArraySolicit)
    {
        /** Baixar as solicitações que estão vinculadas a OS **/
        self::$_userNs = new Zend_Session_Namespace('userNs');
        self::$_solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $tpVincDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $solicitDecode = Zend_Json::decode($jsonArraySolicit[0]);
        if (self::$_solicitacaoOs->getVerificaSeOs($solicitDecode["SSOL_ID_DOCUMENTO"])) {
            $tpVinculacao = $tpVincDoc->
                fetchAll('VIDC_ID_DOC_PRINCIPAL = '.$solicitDecode["SSOL_ID_DOCUMENTO"])->
                toArray();
            foreach ($tpVinculacao as $tv) {
                $dataSol = $ssolSolicitacao->getDadosSolicitacao($tv["VIDC_ID_DOC_VINCULADO"]);
                $result = $ssolSolicitacao->baixaSolicitacao(array(
                        "MOFA_ID_MOVIMENTACAO" => $dataSol["MOVI_ID_MOVIMENTACAO"],
                        "MOFA_CD_MATRICULA"    => self::$_userNs->matricula,
                        "MOFA_DS_COMPLEMENTO"  => "Solicitação atendida."
                    ), $tv["VIDC_ID_DOC_VINCULADO"], null, true
                );
            }
        }
        return $result;
    }
}