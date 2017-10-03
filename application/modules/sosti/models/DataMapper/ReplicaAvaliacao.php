<?php
/**
 * Replicações de avaliações das OS's e das Solicitações de TI.
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_ReplicaAvaliacao extends Zend_Db_Table_Abstract
{
    /**
     * Quando a Solicitação for avaliada, se a opção acima tiver sido selecionada, o sistema deverá seguir 
     * os seguintes passos:
     * I. Verificar se a OS vinculada a ela foi avaliada; 
     * II. Se a OS não tiver sido avaliada, caso a solicitação tenha sido avaliada positivamente, 
     * o sistema deverá replicar a avaliação da Solicitação para a avaliação da OS.
     */
    public static function addCheckbox($idDoc)
    {
        $osVinculada = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $documento = new Application_Model_DbTable_SadTbDocmDocumento();
        $numeroOs = $osVinculada->getPrincipalOs($idDoc);
        $idOsDoc = $documento->fetchRow('DOCM_NR_DOCUMENTO = '.$numeroOs[0]['DOCM_NR_DOCUMENTO']);
        $dOs = $SosTbSsolSolicitacao->getHistoricoSolicitacao($idOsDoc["DOCM_ID_DOCUMENTO"]);
        foreach ($dOs as $d) {
            $arrayFase[] = $d['FADM_ID_FASE'];
        }
        /** Última baixa lançada */
        $menorBaixa  = array_search('1000', $arrayFase) !== false ? array_search('1000', $arrayFase) : 'false';
        /** Última avaliação lançada */
        $menorAvaliacao = array_search('1014', $arrayFase) !== false ? array_search('1014', $arrayFase) : 'false';
        /** Última recusa lançada */
        $menorRecusa = array_search('1019', $arrayFase) !== false ? array_search('1019', $arrayFase)  : 'false';
        $arrayAcoes = array(
            $menorBaixa     => 'baixa', 
            $menorAvaliacao => 'avaliacao', 
            $menorRecusa    => 'recusa' 
        );
        foreach ($arrayAcoes as $k=>$ac) {
            if ($k != "false") {
                $acoesInt[$k] = $ac;
            }
        }
        return $acoesInt[min(array_flip($acoesInt))] == "baixa" ? true : false;
    }
    
    /**
     * Recebe os parãmetros da solicitação e replica a avaliação da solicitação para a OS.
     *
     * @param type $idDocmDocumento
     * @param type $sadTbMofaMoviFase
     * @param type $savisAvaliacaoServico
     * @param type $nrDocsRed
     * @return boolean
     */
    public static function addMesmaAvaliacaoSolicitacaoOs($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico, $nrDocsRed)
    {
        /** Verifica se é uma avaliação positiva para ser replicada para a OS */
        if ($sadTbMofaMoviFase['FADM_ID_FASE'] == '1014') {
            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $avaliaSolicitacao = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();
            $osVinculada = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
            $documento = new Application_Model_DbTable_SadTbDocmDocumento();
            $idOsDoc = $SosTbSsolSolicitacao->fetchRow('SSOL_ID_DOCUMENTO = '.$idDocmDocumento);
            /** Se tiver selecionado a opção para replicar a avaliação para a realiza a replicação da avaliação */
            $avaliaOs = false;
            if ($idOsDoc["SSOL_IC_REPLICA_AVALIACAO_OS"] == 'S') {
                /** Pega a OS vinculada a solicitação */
                $os = $osVinculada->getPrincipalOs($idDocmDocumento);
                $dataOs = $documento->fetchRow('DOCM_NR_DOCUMENTO = '.$os[0]["DOCM_NR_DOCUMENTO"]);
                $ultimaFase = array_shift($SosTbSsolSolicitacao->getHistoricoSolicitacao($dataOs['DOCM_ID_DOCUMENTO']));
                /** Veficar se a última fase não é de solicitação avaliada */
                if ($ultimaFase["FADM_ID_FASE"] != '1014') {
                    $idDocumento = $dataOs['DOCM_ID_DOCUMENTO'];
                    $sadTbMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $ultimaFase["MOVI_ID_MOVIMENTACAO"];
                    $avaliaOs = $avaliaSolicitacao->setAvaliaSolicitacao($idDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico, $nrDocsRed);
                }
            }
            return $avaliaOs;
        } else {
            return false;
        }
    }
    
    /**
     * Baixa as solicitações que deram origem a OS com a mesma descrição da avaliação positiva.
     *
     * @param type $dataBaixa
     * @param type $arraySolicitacao
     * @param type $nrDocsRed
     * @return boolean
     */
    public static function baixaComMesmaDescricaoAvaliacaoOs($dataBaixa, $dataOs, $nrDocsRed)
    {
        $solicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $vinculadosOs = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $documento = new Application_Model_DbTable_SadTbDocmDocumento();
        $user = new Zend_Session_Namespace('userNs');
        if ($dataBaixa["BAIXAR_DESCRICAO_AVALIACAO"] == '1') {
            $arrayDataOs = Zend_Json::decode($dataOs[0]);
            $arrayVinculados = $vinculadosOs->getDocVinculado($arrayDataOs["SSOL_ID_DOCUMENTO"]);
            foreach ($arrayVinculados as $v) {
                $dataDoc = $documento->fetchRow('DOCM_NR_DOCUMENTO = '.$v["DOCM_NR_DOCUMENTO"]);
                $historico = $solicitacao->getHistoricoSolicitacao($dataDoc["DOCM_ID_DOCUMENTO"]);
                if ($historico[0]["FADM_ID_FASE"] != "1000") {
                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $historico[0]["MOFA_ID_MOVIMENTACAO"];
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $user->matricula;
                    $dataMofaMoviFase['MOFA_DS_COMPLEMENTO'] = strlen($dataBaixa["descricao"]) > 3 ? 
                            $dataBaixa["descricao"] : 
                            "Baixa realizada devido a avaliação positiva da Ordem de Serviço (OS).";
                    $dataMofaMoviFase["REPLICA_AVALIACAO_OS"] = NULL;
                    $solicitacao->baixaSolicitacao($dataMofaMoviFase, $dataDoc["DOCM_ID_DOCUMENTO"], $nrDocsRed);
                }
                
            }
            return true;
        } else {
            return false;
        }
    }
}