<?php

class Application_Model_DbTable_SadTbModoMoviDocumento extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_MODO_MOVI_DOCUMENTO';
    protected $_primary = array('MODO_ID_MOVIMENTACAO');

    //protected $_sequence = 'SAD_SQ_MOVI';
    
     /**
     * Recebe como parametros de entrada
     * @param type $idDocmDocumento
     * @param array $dataMoviMovimentacao ('MOVI_SG_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_MATR_ENCAMINHADOR'=>'', 'MOVI_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataModeMoviDestinatario ('MODE_SG_SECAO_UNID_DESTINO'=>'', 'MODE_CD_SECAO_UNID_DESTINO'=>'', 'MODE_IC_RESPONSAVEL'=>'', 'MODE_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataMofaMoviFase ('MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @param array $dataModpDestinoPessoa ('MODP_CD_MAT_PESSOA_DESTINO'=>'');
     
      * * @return void
     */
    public function encaminhaDocumento(
            $idDocmDocumento,
            array $dataMoviMovimentacao,
            array $dataModeMoviDestinatario,
            array $dataMofaMoviFase,
            array $dataModpDestinoPessoa,
            $nrDocsRed = null,
            $autoCommit = true) {
        /**
         * Encaminha Documento
         * Com ou sem troca de nível.
         */
        //Zend_Debug::dump($autoCommit, 'auto commit'); exit;
        if ($autoCommit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }
        try {
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            /* ---------------------------------------------------------------------------------------- */
            /* primeira tabela */
            if ($dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] == "" || is_null($dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"])) {
                 $e = new Exception('O valor de sigla de seção de origem não pode ser vazio', 1, null);
                 throw $e; 
             }
            if ($dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] == "" || is_null($dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"])) {
                 $e = new Exception('O valor de codigo de lotação de origem não pode ser vazio', 1, null);
                 throw $e; 
             }
             
            unset($dataMoviMovimentacao["MOVI_ID_MOVIMENTACAO"]);
            $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
            $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
            $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
            $idMoviMovimentacao = $rowMoviMovimentacao->save();
            /*----------------------------------------------------------------------------------------*/
            
            /* ---------------------------------------------------------------------------------------- */
            /* segunda tabela */
            $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
            $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
            $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
            $rowModoMoviDocumento->save();
            /* ---------------------------------------------------------------------------------------- */
            
            /* ---------------------------------------------------------------------------------------- */
            /* terceira tabela */
            unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]); 
            unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
            $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
            $rowModeMoviDestinatario->save();
            /* ---------------------------------------------------------------------------------------- */
            
            /* ---------------------------------------------------------------------------------------- */
            /* quarta tabela */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            
            /* quinta tabela */
            if (array_key_exists('MODP_CD_MAT_PESSOA_DESTINO', $dataModpDestinoPessoa) && isset($dataModpDestinoPessoa["MODP_CD_MAT_PESSOA_DESTINO"])) {
                unset($dataModpDestinoPessoa["MODP_DH_RECEBIMENTO"]);
                unset($dataModpDestinoPessoa["MODP_IC_RESPONSAVEL"]);
                $SadTbModpDestinoPessoa = new Application_Model_DbTable_SadTbModpDestinoPessoa();
                $dataModpDestinoPessoa["MODP_ID_MOVIMENTACAO"] = $idMoviMovimentacao;

                $dataModpDestinoPessoa["MODP_SG_SECAO_UNID_DESTINO"] = $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"];
                $dataModpDestinoPessoa["MODP_CD_SECAO_UNID_DESTINO"] = $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"];
                $rowModpDestinoPessoa = $SadTbModpDestinoPessoa->createRow($dataModpDestinoPessoa);
                $rowModpDestinoPessoa->save();
            }
            /* ---------------------------------------------------------------------------------------- */
            $anexAnexo['ANEX_ID_DOCUMENTO']     = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE']          = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO']  = $idMoviMovimentacao;
            if ($nrDocsRed) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            if ($autoCommit) {
                $db->commit();
            }
            return true;
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            throw $exc;
            //return false;
        }
    }
    
    public function desfazerencaminhamento(
        $idDocmDocumento
        , array $dataMoviMovimentacao
        , array $dataModeMoviDestinatario
        , array $dataMofaMoviFase
        , array $dataModoMoviDocumento
        , array $dataModpDestPessoa
        , $autoCommit = true) {
        /**
         * Desfaz Encaminhamento de Documento
         * Com ou sem troca de nível.
         */
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ($autoCommit) {
            $db->beginTransaction();
        }
        try {
            
            $sadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo;
            $arrayAnexosMovimentacao = $sadTbAnexAnexo->getAxenosMovimentacaoTemporario($dataMofaMoviFase);
            
            $userNs = new Zend_Session_Namespace('userNs');
            $sadTbAnexAuditoria = new Application_Model_DbTable_SadTbAnexAuditoria();
            foreach ($arrayAnexosMovimentacao as $tuplaAnexoMovimentacao) {

                $dataAnexAuditoria['ANEX_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(TO_CHAR(sysdate,'DD/MM/YYYY HH24:MI:SS'),'DD/MM/YYYY HH24:MI:SS')");
                $dataAnexAuditoria['ANEX_IC_OPERACAO'] = 'E';
                $dataAnexAuditoria['ANEX_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                $dataAnexAuditoria['ANEX_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $dataAnexAuditoria['ANEX_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                $dataAnexAuditoria['OLD_ANEX_ID_DOCUMENTO'] = $tuplaAnexoMovimentacao['ANEX_ID_DOCUMENTO'];
                $dataAnexAuditoria['NEW_ANEX_ID_DOCUMENTO'] = new Zend_Db_Expr("NULL");
                $dataAnexAuditoria['OLD_ANEX_NR_DOCUMENTO_INT'] = $tuplaAnexoMovimentacao['ANEX_NR_DOCUMENTO_INTERNO'];
                $dataAnexAuditoria['NEW_ANEX_NR_DOCUMENTO_INT'] = new Zend_Db_Expr("NULL");
                $dataAnexAuditoria['OLD_ANEX_ID_MOVIMENTACAO'] = $tuplaAnexoMovimentacao['ANEX_ID_MOVIMENTACAO'];
                $dataAnexAuditoria['NEW_ANEX_ID_MOVIMENTACAO'] = new Zend_Db_Expr("NULL");
                $dataAnexAuditoria['OLD_ANEX_DH_FASE'] = new Zend_Db_Expr("TO_DATE('{$tuplaAnexoMovimentacao['ANEX_DH_FASE']}','DD/MM/YYYY HH24:MI:SS')");
                $dataAnexAuditoria['NEW_ANEX_DH_FASE'] = new Zend_Db_Expr("NULL");
                if ($tuplaAnexoMovimentacao['ANEX_ID_TP_EXTENSAO'] == null) {
                    $dataAnexAuditoria['OLD_ANEX_ID_TP_EXTENSAO'] = new Zend_Db_Expr("NULL");
                } else {
                    $dataAnexAuditoria['OLD_ANEX_ID_TP_EXTENSAO'] = $tuplaAnexoMovimentacao['ANEX_ID_TP_EXTENSAO'];
                }
                $dataAnexAuditoria['NEW_ANEX_ID_TP_EXTENSAO'] = new Zend_Db_Expr("NULL");
                $sadTbAnexAuditoria->insert($dataAnexAuditoria);
            }
            $sadTbAnexAnexo->delete("
                    ANEX_ID_MOVIMENTACAO = $dataMofaMoviFase[MOFA_ID_MOVIMENTACAO] 
                    AND ANEX_DH_FASE = TO_DATE('$dataMofaMoviFase[MOFA_DH_FASE]','dd/mm/yyyy HH24:MI:SS')");

//            Zend_Debug::dump($dataMofaMoviFase);
//            exit;
//            Zend_Debug::dump($dataModeMoviDestinatario, MOVIDESTINATARIO);
//            Zend_Debug::dump($dataModoMoviDocumento, MOVIDOCUMENTO);
//            Zend_Debug::dump($dataModpDestPessoa, MODPDESTPESSOA);
//            Zend_Debug::dump($dataMofaMoviFase, MOFAMOVIFASE);
//            Zend_Debug::dump($dataMoviMovimentacao, MOVIMOVIMENTACAO);
//            exit;
            /* quinta tabela */
            
            /* ---------------------------------------------------------------------------------------- */
            if ($dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] && isset($dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"])) {
                $stmt = $db->query("DELETE FROM SAD_TB_MODP_DESTINO_PESSOA
                                    WHERE MODP_ID_MOVIMENTACAO = $dataModpDestPessoa[MODP_ID_MOVIMENTACAO]
                                    AND MODP_SG_SECAO_UNID_DESTINO = '$dataModpDestPessoa[MODP_SG_SECAO_UNID_DESTINO]'
                                    AND MODP_CD_SECAO_UNID_DESTINO = $dataModpDestPessoa[MODP_CD_SECAO_UNID_DESTINO]
                                    AND MODP_CD_MAT_PESSOA_DESTINO = '$dataModpDestPessoa[MODP_CD_MAT_PESSOA_DESTINO]'");
            }
            /* ---------------------------------------------------------------------------------------- */
            
            /* quarta tabela */
            /* ---------------------------------------------------------------------------------------- */
            $stmt = $db->query("DELETE FROM SAD_TB_MOFA_MOVI_FASE
                                WHERE /*MOFA_DH_FASE = TO_DATE('$dataMofaMoviFase[MOFA_DH_FASE]','DD/MM/YYYY HH24:MI:SS')
                                AND*/ MOFA_ID_MOVIMENTACAO = $dataMofaMoviFase[MOFA_ID_MOVIMENTACAO]");
            /* ---------------------------------------------------------------------------------------- */
            
            /* terceira tabela */
            /* ---------------------------------------------------------------------------------------- */
            $stmt = $db->query("DELETE FROM SAD_TB_MODE_MOVI_DESTINATARIO
                                WHERE MODE_ID_MOVIMENTACAO = $dataModeMoviDestinatario[MODE_ID_MOVIMENTACAO]
                                AND MODE_CD_SECAO_UNID_DESTINO = $dataModeMoviDestinatario[MODE_CD_SECAO_UNID_DESTINO]
                                AND MODE_SG_SECAO_UNID_DESTINO = '$dataModeMoviDestinatario[MODE_SG_SECAO_UNID_DESTINO]'");
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* segunda tabela */
             $stmt = $db->query("DELETE FROM SAD_TB_MODO_MOVI_DOCUMENTO
                                WHERE MODO_ID_MOVIMENTACAO = $dataModoMoviDocumento[MODO_ID_MOVIMENTACAO]");
            /* ---------------------------------------------------------------------------------------- */
            
            /* primeira tabela */
            /* ---------------------------------------------------------------------------------------- */
             $stmt = $db->query("DELETE FROM SAD_TB_MOVI_MOVIMENTACAO
                                WHERE MOVI_ID_MOVIMENTACAO = $dataMoviMovimentacao[MOVI_ID_MOVIMENTACAO]");
            /* ---------------------------------------------------------------------------------------- */
            if ($autoCommit) {
                $db->commit();
            }
            return true;
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            return $exc->getMessage();
        }
    }
    
    /**
     * Recebe como parametros de entrada
     * @param array $dataMofaMoviFase ('MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @param array $dataModpDestinoPessoa ('MODP_SG_SECAO_UNID_DESTINO'=>'','MODP_CD_SECAO_UNID_DESTINO'=>'','MODP_CD_MAT_PESSOA_DESTINO'=>'');
      * * @return void
     */
    public function encaminhaDocumentoDaCaixaUnidadeParaCaixaPessoal(
							        array $dataMofaMoviFase,
								array $dataModpDestinoPessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try{
            $SadTbModpDestinoPessoa = new Application_Model_DbTable_SadTbModpDestinoPessoa();
            //$dataModpDestinoPessoa=  $SadTbModpDestinoPessoa->fetchNew()->toArray();
            //$dataModpDestinoPessoa = array();

            //Zend_Debug::dump($dataModpDestinoPessoa);
        //        $dataModpDestinoPessoa["MODP_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        //            $dataModpDestinoPessoa["MODP_SG_SECAO_UNID_DESTINO"] = INFORMAR;
        //            $dataModpDestinoPessoa["MODP_CD_SECAO_UNID_DESTINO"] = INFORMAR;
        //            $dataModpDestinoPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = INFORMAR;
            unset($dataModpDestinoPessoa["MODP_DH_RECEBIMENTO"]);
            unset($dataModpDestinoPessoa["MODP_IC_RESPONSAVEL"]);
            //Zend_Debug::dump($dataModpDestinoPessoa);

            $rowModpDestinoPessoa = $SadTbModpDestinoPessoa->createRow($dataModpDestinoPessoa);
            $rowModpDestinoPessoa->save();

            /*----------------------------------------------------------------------------------------*/

            /*----------------------------------------------------------------------------------------*/
            /*segunda tabela*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();

            //Zend_Debug::dump($dataMofaMoviFase);
        //        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        //        $dataMofaMoviFase["MOFA_DH_FASE"] = new Zend_Db_Expr("SYSDATE");
        //        $dataMofaMoviFase["MOFA_ID_FASE"] = INFORMAR;
        //        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = INFORMAR;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            //Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            $db->commit();
            return true;

        }catch(Exception $exc){

        }
        
        /*----------------------------------------------------------------------------------------*/          
    }
    
    public function verificaQtdeMovimentacaoDcmto( $idDocumento ){
        /*
         * Verifica a quantidade de movimentacoes que o documento teve
         */  
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT NVL(COUNT(MODO_ID_DOCUMENTO),0) QTDE_MOVIMENTACOES
                            FROM SAD_TB_MODO_MOVI_DOCUMENTO
                            WHERE MODO_ID_DOCUMENTO = $idDocumento");
        
        $qtde_movimentacoes =  $stmt->fetch();
        
        return $qtde_movimentacoes['QTDE_MOVIMENTACOES'];
    }
        
    public function encaminhaMinuta(
            $idDocmDocumento,
      array $dataMoviMovimentacao,
      array $dataModeMoviDestinatario,
      array $dataMofaMoviFase,
      array $dataModpDestinoPessoa,
      $nrDocsRed = null,
      $autoCommit = true,
      $datahora = null) {
        /**
         * Encaminha Documento
         * Com ou sem troca de nível.
         */
 
        if ($autoCommit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
    }
        try {
            if($datahora==null){
                $Dual = new Application_Model_DbTable_Dual();
                $datahora = $Dual->sysdate();
            }
            /* ---------------------------------------------------------------------------------------- */
            /* primeira tabela */
            if ($dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] == "" || is_null($dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"])) {
                $e = new Exception('O valor de sigla de seção de origem não pode ser vazio', 1, null);
                throw $e;
            }
            if ($dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] == "" || is_null($dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"])) {
                $e = new Exception('O valor de codigo de lotação de origem não pode ser vazio', 1, null);
                throw $e;
            }
    
            unset($dataMoviMovimentacao["MOVI_ID_MOVIMENTACAO"]);
            $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
            $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $datahora;
            $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
            $idMoviMovimentacao = $rowMoviMovimentacao->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* segunda tabela */
            $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
            $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
            $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
            $rowModoMoviDocumento->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* terceira tabela */
            unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]);
            unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
            $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
            $rowModeMoviDestinatario->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* quarta tabela */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();

            /* quinta tabela */
            if (array_key_exists('MODP_CD_MAT_PESSOA_DESTINO', $dataModpDestinoPessoa) && isset($dataModpDestinoPessoa["MODP_CD_MAT_PESSOA_DESTINO"])) {
                unset($dataModpDestinoPessoa["MODP_DH_RECEBIMENTO"]);
                unset($dataModpDestinoPessoa["MODP_IC_RESPONSAVEL"]);
                $SadTbModpDestinoPessoa = new Application_Model_DbTable_SadTbModpDestinoPessoa();
                $dataModpDestinoPessoa["MODP_ID_MOVIMENTACAO"] = $idMoviMovimentacao;

                $dataModpDestinoPessoa["MODP_SG_SECAO_UNID_DESTINO"] = $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"];
                $dataModpDestinoPessoa["MODP_CD_SECAO_UNID_DESTINO"] = $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"];
                $rowModpDestinoPessoa = $SadTbModpDestinoPessoa->createRow($dataModpDestinoPessoa);
                $rowModpDestinoPessoa->save();
}
            /* ---------------------------------------------------------------------------------------- */
            $anexAnexo['ANEX_ID_DOCUMENTO']     = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE']          = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO']  = $idMoviMovimentacao;
            if ($nrDocsRed) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                $cont = 0;
                
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];                    
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                    $cont++;
                }
                //Zend_Debug::dump($anexAnexo, 'array anexo'); exit;
            }
            if ($autoCommit) {
                $db->commit();
            }
            return true;
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            throw $exc;
            //return false;
        }
    }

}