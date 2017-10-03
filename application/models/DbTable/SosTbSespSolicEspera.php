<?php
class Application_Model_DbTable_SosTbSespSolicEspera extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SESP_SOLIC_ESPERA';
    protected $_primary = array('SESP_ID_MOVIMENTACAO' , 'SESP_DH_FASE');
    
    /**
     * Coloca uma Solicitação em espera
     * Recebe como parametros de entrada
     * @param $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
     * @param $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * @param $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = ;
     * @param $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = ;
     * @return void
     */
    public function esperaSolicitacao($idDocmDocumento, array $dataMofaMoviFase, array $dataSespSolicEspera, $nrDocsRed = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();
            Zend_Debug::dump($data);
            
            /*----------------------------------------------------------------------------------------*/
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
            //$dataMofaMoviFase = array();
            
            Zend_Debug::dump($dataMofaMoviFase);
//            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = ;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_ID_FASE"] = 1007; /*ESPERA SOLICITACAO TI*/
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
//            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
//            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = '';
            Zend_Debug::dump($dataMofaMoviFase);

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            
            /*----------------------------------------------------------------------------------------*/
            $SosTbSespSolicEspera = new Application_Model_DbTable_SosTbSespSolicEspera();
            //$dataSespSolicEspera=  $SosTbSespSolicEspera->fetchNew()->toArray();
            //$dataSespSolicEspera = array();
            
            Zend_Debug::dump($dataSespSolicEspera);
            $dataSespSolicEspera["SESP_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataSespSolicEspera["SESP_DH_FASE"] = $datahora;
            $dataSespSolicEspera["SESP_ID_DOCUMENTO"] = $idDocmDocumento;
//            $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = ;
            Zend_Debug::dump($dataSespSolicEspera);

            $rowSespSolicEspera = $SosTbSespSolicEspera->createRow($dataSespSolicEspera);
            $rowSespSolicEspera->save();
            /*----------------------------------------------------------------------------------------*/
             
            //Ultima Fase do lançada na Solicitação.//
            /*----------------------------------------------------------------------------------------*/
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
            $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();;
            $rowUltima_fase->setFromArray($dataUltima_fase);
            Zend_Debug::dump($rowUltima_fase->toArray());
            $rowUltima_fase->save();
            /*----------------------------------------------------------------------------------------*/
            
//            /*Retira do atendente*/
//             /*----------------------------------------------------------------------------------------*/
//            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
//            $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
//            $rowSolicitacao = $SosTbSsolSolicitacao->find($idDocmDocumento)->current();
//            $rowSolicitacao->setFromArray($dataSsolSolicitacao);
//            $rowSolicitacao->save();
//             /*----------------------------------------------------------------------------------------*/
            /* ---------------------------------------------------------------------------------------- */

            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
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
            
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
            exit;
        }
        return $datahora;
    }
}