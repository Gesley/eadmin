<?php
class Application_Model_DbTable_SosTbSavsAvaliacaoServico extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SAVS_AVALIACAO_SERVICO';
    protected $_primary = array('SAVS_ID_MOVIMENTACAO' , 'SAVS_DH_FASE');
    
    /**
     * Avalia uma Solicitação
     * Recebe como parametros de entrada
     * @param $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
     * @param $dataMofaMoviFase["MOFA_ID_FASE"]
     * @param $dataMofaMoviFase["MOFA_CD_MATRICULA"];
     * @param $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"];
     * @param $dataSavsAvaliacaoServico["SAVS_ID_TIPO_SAT"];
     * 
     * @return void
     */
    public function setAvaliaSolicitacao($idDocmDocumento, array $dataMofaMoviFase, array $dataSavsAvaliacaoServico, $nrDocsRed = null, $autoCommit = true)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($autoCommit){
            $db->beginTransaction();
        }
        try {
            $dual = new Application_Model_DbTable_Dual();
            $datahora = $dual->sysdate();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_DH_FASE"] = $datahora;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            $SosTbSavsAvaliacaoServico = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();
            $dataSavsAvaliacaoServico["SAVS_ID_MOVIMENTACAO"] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            $dataSavsAvaliacaoServico["SAVS_DH_FASE"] = $dataMofaMoviFase["MOFA_DH_FASE"];
            $dataSavsAvaliacaoServico["SAVS_ID_DOCUMENTO"] = $idDocmDocumento;
            $rowSavsAvaliacaoServico = $SosTbSavsAvaliacaoServico->createRow($dataSavsAvaliacaoServico);
            $rowSavsAvaliacaoServico->save();
            
            
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
            // Insere o anexo
            /*----------------------------------------------------------------------------------------*/
            $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
            $anexAnexo['ANEX_DH_FASE'] = $datahora;
            $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            if($nrDocsRed){
                $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                foreach ($nrDocsRed["incluidos"] as $anexosIncluir) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo =  $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
             /*----------------------------------------------------------------------------------------*/
            if($autoCommit){
                $db->commit();
            }
            $retorno['DATA_HORA'] = $datahora;
            $retorno['ID_MOVIMENTACAO'] = $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"];
            
            return $retorno;
        } catch (Exception $exc) {
            if($autoCommit){
                $db->rollBack();
            }
            throw $exc;
        }
    }
    
}