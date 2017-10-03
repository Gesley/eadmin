<?php
/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados 
 * DbTable e o criar o objeto Model.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Os_Model_DataMapper_Solicitacao extends Zend_Db_Table_Abstract
{
    protected $_sysdate;
    protected $_dbTable;

    public function __construct() 
    {
        $this->setDbTable(new Application_Model_DbTable_SosTbSsolSolicitacao);
        $this->setSysdate();
    }

    private function setDbTable(Application_Model_DbTable_SosTbSsolSolicitacao $dbtable) 
    {
        $this->_dbTable = $dbtable;
    }
    
    private function setSysdate()
    {
        $this->_sysdate = $this->_dbTable->sysdate();
    }
    
    /**
     * Recebe como parametros de entrada
     * @param array $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
     * @param array $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = ;
     * @param array $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = ;
     * @param array $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = ;
     * @param array $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_PCTT"] = 414; //PCTT Solicitação de TI
     * @param array $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = ;
     * @param array $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
     * @param array $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
     * @param array $dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"]; 

     * @param array $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = ;
     * @param array $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_NR_TOMBO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = ;
     * @param array $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = ;
     * @param array $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = ;
     * @param array $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = ;
     * 
     * @param array $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = ;
     * @param array $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = ;
     * @param array $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = ;

     * @param array $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = ;
     * @param array $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = 1146; //Unidade de Destino DIATU PRIMEIRO ATENDIMENTO
     * @param array $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
     * @param array $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU


     * @param array $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
     * @param array $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * @param array $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação para primeiro atendiamento no HELPDESK";


     * @param array $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 1; //Primeiro Nivel HelpDesk ;

     * @param array $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
     * 
     * 
     * @example      
     * $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = ;
     * $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
     * $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = ;
     * $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = ;
     * $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = ;
     * $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = ;
     * $dataDocmDocumento["DOCM_ID_PCTT"] = 414; //PCTT Solicitação de TI
     * $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = ;
     * $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
     * $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
     * unset($dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"]);

     * $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = ;
     * $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = ;
     * $dataSsolSolicitacao["SSOL_NR_TOMBO"] = ;
     * $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = ;
     * $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = ;
     * $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = ;
     * $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = ;
     * 
     * $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = ;
     * $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = ;
     * $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU
     * $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = ;
     * 
     * $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = ;
     * $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = ;
     * $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
     * $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU


     * $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
     * $dataMofaMoviFase["MOFA_CD_MATRICULA"] = ;
     * $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação para primeiro atendiamento no HELPDESK";


     * $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 1; //Primeiro Nivel HelpDesk ;

     * $dataSsesServicoSolic["SSES_ID_SERVICO"] = ;
     * 
     * $dataAnexAnexo["ANEX_ID_DOCUMENTO"] = $idDocmDocumento;
     * $dataAnexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = ;
     * $dataAnexAnexo["ANEX_ID_MOVIMENTACAO"] = ;
     * $dataAnexAnexo["ANEX_DH_FASE"] = ;
     * 
     * @return $dataRetorno 
     * $dataRetorno["DOCM_ID_DOCUMENTO"] = $idDocmDocumento;
     * $dataRetorno["DOCM_NR_DOCUMENTO"] = $dataDocmDocumento["DOCM_NR_DOCUMENTO"];
     * 
     * 
     */
    public function setCadastrarSolicitacao(array $dataDocmDocumento, array $dataSsolSolicitacao, array $dataMoviMovimentacao, array $dataModeMoviDestinatario, array $dataMofaMoviFase, array $dataSsesServicoSolic, array $dataSnasNivelAtendSolic, $nrDocsRed = null, $dataAcompanhantes = null, $dataPorOrdemDe = null)
    {
        /*
         * Cadastro da Solicitação Interna
         */
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();

        $dataDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC']);

        $dataDocmDocumento["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($dataDocmDocumento['DOCM_SG_SECAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'], $dataDocmDocumento['DOCM_CD_LOTACAO_GERADORA'], $dataDocmDocumento['DOCM_ID_TIPO_DOC'], $dataDocmDocumento['DOCM_NR_SEQUENCIAL_DOC']);
        /* ---------------------------------------------------------------------------------------- */
        /* Primeira tabela a ser inserida */
        unset($dataDocmDocumento["DOCM_ID_DOCUMENTO"]);
        $dataDocmDocumento["DOCM_DH_CADASTRO"] = $this->_sysdate;
        $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = new Zend_Db_Expr("'" . substr($dataDocmDocumento['DOCM_DS_ASSUNTO_DOC'], 0, 4000) . "'");
        $rowDocmDocumento = $tabelaSadTbDocmDocumento->createRow($dataDocmDocumento);
        $idDocmDocumento = $rowDocmDocumento->save();
        /* ---------------------------------------------------------------------------------------- */
        /* ---------------------------------------------------------------------------------------- */
        /* Segunda tabela */
        $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $idDocmDocumento;
        $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = new Zend_Db_Expr("'" . $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] . "'");
        unset($dataSsolSolicitacao["SSOL_HH_INICIO_ATEND"]);
        unset($dataSsolSolicitacao["SSOL_HH_FINAL_ATEND"]);
        $rowSsolSolicitacao = $this->_dbTable->createRow($dataSsolSolicitacao);
        $idSsolSolicitacao = $rowSsolSolicitacao->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* terceira tabela */
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
        $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $this->_sysdate;
        $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
        $idMoviMovimentacao = $rowMoviMovimentacao->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* quarta tabela */
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
        $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
        $rowModoMoviDocumento->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* quinta tabela */
        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]);
        unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
        $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
        $rowModeMoviDestinatario->save();
        /* ---------------------------------------------------------------------------------------- */
        /* ---------------------------------------------------------------------------------------- */
        /* sexta tabela */
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataMofaMoviFase["MOFA_DH_FASE"] = $this->_sysdate;
        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $rowMofaMoviFase->save();
        /* ---------------------------------------------------------------------------------------- */

        //Ultima Fase do lançada na Solicitação.//
        /* ---------------------------------------------------------------------------------------- */
        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataUltima_fase["DOCM_DH_FASE"] = $this->_sysdate;
        $rowUltima_fase = $tabelaSadTbDocmDocumento->find($idDocmDocumento)->current();
        $rowUltima_fase->setFromArray($dataUltima_fase);
        $rowUltima_fase->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* setima tabela */
        $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
        $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
        $dataSsesServicoSolic["SSES_DH_FASE"] = $this->_sysdate;
        if(isset($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]) && !is_null($dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"])){
            $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = new Zend_Db_Expr("TO_DATE('".$dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"]."','dd/mm/yyyy HH24:MI:SS')"); 
            $dataSsesServicoSolic["SSES_IC_VIDEO_REALIZADA"] = "N";
        }
        $dataSsesServicoSolic['SSES_ID_DOCUMENTO'] = $idDocmDocumento;
        $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
        $rowSsesServicoSolic->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* oitava tabela */
        if ($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] && isset($dataSnasNivelAtendSolic["SNAS_ID_NIVEL"])) {
            $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
            $dataSnasNivelAtendSolic["SNAS_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataSnasNivelAtendSolic["SNAS_DH_FASE"] = $this->_sysdate;
            $dataSnasNivelAtendSolic["SNAS_ID_DOCUMENTO"] = $idDocmDocumento;
            $rowSnasNivelAtendSolic = $SosTbSnasNivelAtendSolic->createRow($dataSnasNivelAtendSolic);
            $rowSnasNivelAtendSolic->save();
        }
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* nona tabela */
        $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
        $anexAnexo['ANEX_DH_FASE'] = $this->_sysdate;
        $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
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
        /* ---------------------------------------------------------------------------------------- */

        /*-----------------ACOMPANHAMENTO DE BAIXA DE SOLICITAÇÃO NO CADASTRO --------------------*/
        if (!is_null($dataAcompanhantes)) {
            $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            foreach ($dataAcompanhantes as $acompanhante) {
                $arr_exploded = explode(" - ", $acompanhante);
                $matricula = $arr_exploded[0];
                $tabelaPapd->addAcompanhanteSostiCadastroSolicitacao($idDocmDocumento, $matricula);
            }
        }
        /*----------------------------------------------------------------------------------------*/

        /*-----------------CADASTRO DE SOLICITAÇÃO POR ORDEM DE  --------------------*/
        if (!is_null($dataPorOrdemDe)) {                
                $arr_exploded_porordemde = explode(" - ", $dataPorOrdemDe);
                $matricula = $arr_exploded_porordemde[0];
                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                $tabelaPapd->addPorOrdemDeCadastroSolicitacao($idDocmDocumento, $matricula);
        }
        /*----------------------------------------------------------------------------------------*/
        $dataRetorno["DOCM_ID_DOCUMENTO"] = $idDocmDocumento;
        $dataRetorno["DOCM_NR_DOCUMENTO"] = $dataDocmDocumento["DOCM_NR_DOCUMENTO"];
        $dataRetorno["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;

        return $dataRetorno;
    }
    
    /**
     * Vincular as solicitações de OS
     * @param type $solicitacoes
     * @param type $idsSolicitacoespost
     * @param type $todas_principais
     * @param type $justificativa
     * @return boolean
     * @throws Zend_Exception
     */
    public function setVincularSolicitacao($rows, $principal, $justificativa, $tipoVinculacao)
    {
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SadTbVidcAuditoria();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $Dual = new Application_Model_DbTable_Dual();

        if (!empty($rows)) {
            foreach ($rows as $row) {
                //Verifica vinculo
                $res = $SadTbVidcVinculacaoDoc->select()->where('vidc_id_doc_vinculado = ?', $row["DOCM_ID_DOCUMENTO"])->query();
                $res = $res->fetchAll();
                $exists = (!empty($res)) ? true : false;
                sleep(1);
                $datahora = $Dual->sysdate();
                $userNs = new Zend_Session_Namespace('userNs');
                if (!$exists) {
                    $id_vinculacao = new Zend_Db_Expr("NULL");
                    if ($row["DOCM_ID_DOCUMENTO"] != $principal) {
                        $rowVinc = $SadTbVidcVinculacaoDoc->createRow(array(
                            "VIDC_ID_DOC_PRINCIPAL" => $principal,
                            "VIDC_ID_DOC_VINCULADO" => $row["DOCM_ID_DOCUMENTO"],
                            "VIDC_DH_VINCULACAO" => $datahora,
                            "VIDC_ID_TP_VINCULACAO" => $tipoVinculacao,
                            "VIDC_CD_MATR_VINCULACAO" => $userNs->matricula
                        ));
                        $id_vinculacao = $rowVinc->save();
                    }

                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow(array(
                        "MOFA_ID_MOVIMENTACAO" => $row["MOVI_ID_MOVIMENTACAO"] ,
                        "MOFA_DH_FASE" => $datahora,
                        "MOFA_CD_MATRICULA" => $userNs->matricula, //Matricula de quem fez a vinculação da solicitação
                        "MOFA_DS_COMPLEMENTO" => $justificativa,
                        'MOFA_ID_FASE' => 1035 //Vinculação de solicitações
                    ));
                    $rowMofaMoviFase->save();

                    $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $row["MOFA_ID_MOVIMENTACAO"];
                    $dataUltima_fase["DOCM_DH_FASE"] = $datahora;
                    $rowUltima_fase = $tabelaSadTbDocmDocumento->find($row["DOCM_ID_DOCUMENTO"])->current();
                    $rowUltima_fase->setFromArray(array(
                        "DOCM_ID_MOVIMENTACAO" => $row["MOFA_ID_MOVIMENTACAO"],
                        "DOCM_DH_FASE" => $datahora
                    ));
                    $rowUltima_fase->save();

                    $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(
                        array(
                            'VIDC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                            'VIDC_IC_OPERACAO' => 'I',
                            'VIDC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                            'VIDC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                            'VIDC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                            'OLD_VIDC_ID_VINCULACAO_DOC' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_VINCULACAO_DOC' => $id_vinculacao,
                            'OLD_VIDC_ID_DOC_PRINCIPAL' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_DOC_PRINCIPAL' => $principal,
                            'OLD_VIDC_ID_DOC_VINCULADO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_DOC_VINCULADO' => $row["DOCM_ID_DOCUMENTO"],
                            'OLD_VIDC_ID_TP_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_ID_TP_VINCULACAO' => $tipoVinculacao,
                            'OLD_VIDC_DH_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_DH_VINCULACAO' => $datahora,
                            'OLD_VIDC_CD_MATR_VINCULACAO' => new Zend_Db_Expr("NULL"),
                            'NEW_VIDC_CD_MATR_VINCULACAO' => $userNs->matricula
                        )
                    );
                    $rowVidcAuditoria->save();
                }
            }
        }
    }
    
    /**
     * Verifica se a solicitação é uma OS.
     * @param type $idDocumento
     * @return boolean
     */
    public function getVerificaSeOs($idDocumento)
    {
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        try {
            $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($idDocumento);
            $ultimaFase = array_pop($DocmDocumentoHistorico);
        } catch (Exception $ex) {
            return false;
        }
        return $ultimaFase["FADM_ID_FASE"] == "1092" ? true : false;
    }
    
    /**
     * Verifica se existe alguma OS cadastrada nas solicitações do array.
     * @param array $os
     * @return boolean
     */
    public static function vericaOsCadastrada(array $os)
    {
        $vinculos = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        foreach ($os as $i => $sos) {
            $arraySolicit = Zend_Json::decode($sos);
            $vinculoOs = $vinculos->getDadosDocPrincipal($arraySolicit["SSOL_ID_DOCUMENTO"]);
        $verifcaSeOs[] = $vinculoOs[0]["VIDC_ID_TP_VINCULACAO"] == 7 ? true : false;
        }
        return in_array(true, $verifcaSeOs) ? true : false;
    }
    

}
