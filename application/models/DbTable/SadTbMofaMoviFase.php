<?php
class Application_Model_DbTable_SadTbMofaMoviFase extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_MOFA_MOVI_FASE';
    protected $_primary = 'MOFA_ID_MOVIMENTACAO';
    
    public function sysdate()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux =  $stmt->fetchAll();
        $datahora = $datahora_aux[0]["DATAHORA"];
        $datahora = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/yyyy HH24:MI:SS')");
        return $datahora;
    }
    
    public function encaminhaCaixaPessoalSolicitacao($idDocmDocumento , array $dataMofaMoviFase, array $dataSsolSolicitacao, $nrDocsRed = null,$acompanhar = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();

            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1013; /*ENCAMINHAMENTO PARA CAIXA PESSOAL*/
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            
            $dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"] = $dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"];
            
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $idMoviMovimentacao = $rowMofaMoviFase->save();

            $rowSsolSolicitacao = $SosTbSsolSolicitacao->find($dataSsolSolicitacao['SSOL_ID_DOCUMENTO'])->current();
            $rowSsolSolicitacao->setFromArray($dataSsolSolicitacao);
            $rowSsolSolicitacao->save();
            
            //Ultima Fase do lançada na Solicitação.//
            /*----------------------------------------------------------------------------------------*/
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /*----------------------------------------------------------------------------------------*/
            
            // Insere o anexo
            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
            /**
             * Cadastra os documentos que ainda não existe no red.
             */
            if ($nrDocsRed['incluidos']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
            /**
             *  Verifica se o documento que já existe no red já pertence a esta solicitação
             * caso negativo, cadastra o nr do documento para a solicitação.
             */
            if ($nrDocsRed['existentes']) {
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                    $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                    if (!$SadTbAnexAnexofetchRow) {
                        $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                        $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                        $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                        $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                        $rowAnexAnexo->save();
                    }
                }
            }
            /*----------------------------------------------------------------------------------------*/
            
             /*-------------ACOMPANHAMENTO DE BAIXA DA SOLICITAÇÃO NO ENCAMINHAMENTO CX PESSOAL--*/
            if ($acompanhar=="S") {
                 $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                 $tabelaPapd->addAcompanhanteSostiCaixaAtendimento($idDocmDocumento);
            }
            $db->commit();
        
			$logAcesso = new Trf1_Guardiao_Log ();
			$logAcesso->gravaLog ('encaminhamento' );
        } catch (Exception $exc) {
            $db->rollBack();
            throw $exc;
        }
        $retorno['ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $retorno['DATA_HORA'] = $datahora;
        return $retorno;
    }
    
    public function retiraCaixaPessoalSolicitacao(array $dataSsolSolicitacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $datahora = $this->sysdate();

            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();

            $rowSsolSolicitacao = $SosTbSsolSolicitacao->find($dataSsolSolicitacao['SSOL_ID_DOCUMENTO'])->current();
            $rowSsolSolicitacao->setFromArray($dataSsolSolicitacao);
            $rowSsolSolicitacao->save();
            
            $db->commit();
        
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
    }
    
    public function getMovimentacaoProcesso($idProcesso, $dhFase){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MOFA_ID_MOVIMENTACAO, 
                                   TO_CHAR(MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE, 
                                   DOCM_NR_DOCUMENTO
                            FROM SAD_TB_DOCM_DOCUMENTO
                                 INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                 ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                 INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                 ON  MOFA.MOFA_DH_FASE = DCPR_DH_VINCULACAO_DOC
                            WHERE MOFA_ID_MOVIMENTACAO IN (SELECT DISTINCT MOVI_ID_MOVIMENTACAO
                                                              FROM SAD_TB_DOCM_DOCUMENTO DOCM
                                                              INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                                              ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                                              INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                                              ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO                                 
                                                              WHERE  DOCM.DOCM_ID_DOCUMENTO = $idProcesso)
                            AND DOCM_ID_TIPO_DOC <> 152 
                            AND DCPR_ID_PROCESSO_DIGITAL IN (SELECT DCPR_ID_PROCESSO_DIGITAL
                                                             FROM SAD_TB_DOCM_DOCUMENTO
                                                                  INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                                  ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                                                  INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                                                  ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                                             WHERE DOCM_ID_DOCUMENTO = $idProcesso )
                            AND TO_DATE(TO_CHAR(MOFA.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS'),'DD/MM/YYYY HH24:MI:SS') = TO_DATE('$dhFase','DD/MM/YYYY HH24:MI:SS')
                        ");
        return $stmt->fetchAll();
    }
    
    public function deleteMovimentacao($idMovimentacao,$dhFase)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->query("DELETE FROM SAD_TB_MOFA_MOVI_FASE
                          WHERE MOFA_ID_MOVIMENTACAO = $idMovimentacao
                            AND MOFA_DH_FASE = TO_DATE('$dhFase','DD/MM/YYYY HH24:MI:SS')");
    }
}
