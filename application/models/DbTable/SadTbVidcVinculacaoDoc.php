<?php
class Application_Model_DbTable_SadTbVidcVinculacaoDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_VIDC_VINCULACAO_DOC';
    protected $_primary = array('VIDC_ID_VINCULACAO_DOCUMENTO');
    protected $_sequence = 'SAD_SQ_VIDC';
    
    public function setVincDocDoc(array $documentos, array $mofaFase){
        $vidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        
        $Dual = new Application_Model_DbTable_Dual();
//        $datahora = $Dual->sysdate();
        
        foreach ($documentos as $documentos_p) {
            /*----------------------------------------------------------------------------------------*/
            /*Cria fase*/
            $datahora = $Dual->sysdate();
            $mofaFase["MOFA_DH_FASE"] = $datahora;
            
            unset ($mofaFase["AUX"]);
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($mofaFase);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            
            /*
             * Vincula o documento
             */
            
            $documentos_p["VIDC_DH_VINCULACAO"] = $datahora;
            $rowVidcVinculo = $vidcVinculacaoDoc->createRow($documentos_p);
            $rowVidcVinculo->save();
        }
    }
    
    public function getDadosVincDocumento($idVinculacao){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT VIDC.VIDC_DH_VINCULACAO, 
                                   TVDC.TVDC_ID_TP_VINCULACAO,
                                   DECODE(TVDC.TVDC_ID_TP_VINCULACAO, 1, 'Anexado',
                                                                      2, 'Apensado',
                                                                      3, 'Vinculado') VINCULO
                              FROM SAD_TB_DOCM_DOCUMENTO DOCM,
                                   SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                                   SAD_TB_TVDC_TIPO_VINC_DOC TVDC
                             WHERE DOCM.DOCM_ID_DOCUMENTO = VIDC.VIDC_ID_DOC_VINCULADO
                               AND VIDC.VIDC_ID_TP_VINCULACAO = TVDC.TVDC_ID_TP_VINCULACAO
                               AND VIDC.VIDC_ID_VINCULACAO_DOCUMENTO = $idVinculacao");
        return $stmt->fetchAll();
    }
    
    public function getFamiliaVinculacao($solicitacoes) {
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $i = 0;
        $qr = array();
        $dadosFamiliaArray = array();
        /*
         * Decodificando o array json enviado através de POST
         */
        foreach ($solicitacoes as $value) {
            $solicitacoes[$i] = Zend_Json_Decoder::decode($value);
            $somenteids[$i] = $solicitacoes[$i]['SSOL_ID_DOCUMENTO'];
            $id_caixa_entrada = $solicitacoes[$i]['MODE_ID_CAIXA_ENTRADA'];
            $i++;
        }
        /*
         * Passagem do array decodificado para método que retorna a familia de tal solicitação 
         */
        $familia = $table->getSolicitacoesVinculadasActions($solicitacoes);
        /*
         * LOOP para capturar os ids dos documentos da família. 
         */
        foreach ($familia as $familia_p) {
            unset($familia_p["ID_CONSULTADA"]);
            foreach ($familia_p as $ids) {
                $qr[] = $ids["VIDC_ID_DOC_PRINCIPAL"];
                $qr[] = $ids["VIDC_ID_DOC_VINCULADO"];
            }
        }
        /*
         * Limpeza do array com as IDS dos Documentos, pois a query recupera valores repetidos.
         */
        $unique = array_unique($qr);
        /*
         * Junção do Array Contendo os IDS enviados pelo POST e os IDS recuperdos no LOOP acima.
         */
        $merge = array_merge_recursive($somenteids, $unique);
        /*
         * Limpeza de valores repetidos de IDS de Documentos. 
         */
        $unique_merge = array_unique($merge);
        /*
         * Busca dos dados da solicitação passando os IDS obtidos acima, codificação JSON para enviar à view
         * E retira solicitações que não estão na mesma caixa.
         */
        foreach ($unique_merge as $value) {
            $dadosFamilia = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $solicitacoes = $dadosFamilia->getDadosSolicitacao($value);
            if (strcmp($solicitacoes['MODE_ID_CAIXA_ENTRADA'],$id_caixa_entrada)==0) {
                $dadosFamiliaArray[] = Zend_Json_Encoder::encode($solicitacoes);
            }
        }
        /*
         * Retorno do array contendo membros da família e solicitações enviadas pelo POST.
         */
        return $dadosFamiliaArray;
    }
    
    
    public function vincularMinuta($idDocPrincipal, $idMinuta, $matricula, $autoCommit = false){
        $vidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();        
        $Dual = new Application_Model_DbTable_Dual();
        /*
         * Vincula o documento
         */                 
        $datahora = $Dual->sysdate();
        $dados["VIDC_ID_DOC_PRINCIPAL"] = $idDocPrincipal;
        $dados["VIDC_ID_DOC_VINCULADO"] = $idMinuta;
        $dados["VIDC_ID_TP_VINCULACAO"] = 5;
        $dados["VIDC_CD_MATR_VINCULACAO"] = $matricula;
        $dados["VIDC_DH_VINCULACAO"] = $datahora;
        
        if ($autoCommit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }
        try{
            $rowVidcVinculo = $vidcVinculacaoDoc->createRow($dados);
            $row = $rowVidcVinculo->save(); 

            if ($autoCommit) {
                $db->commit();
            }
            return $row;
        }catch(Exception $e){
            if($autoCommit){
                $db->rollBack();
            }
            throw $e;
        }
    }
    
    public function getDocVinculado($idDocPrincipal){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM.DOCM_NR_DOCUMENTO
                              FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                                   SAD_TB_DOCM_DOCUMENTO DOCM
                             WHERE VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO
                             and VIDC.VIDC_ID_DOC_PRINCIPAL = $idDocPrincipal");
        return $stmt->fetchAll();
    }
    
    public function getDocPrincipal($idDocVinculado){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM.DOCM_NR_DOCUMENTO
                              FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                                   SAD_TB_DOCM_DOCUMENTO DOCM
                             WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                             AND VIDC.VIDC_ID_DOC_VINCULADO = $idDocVinculado");
        return $stmt->fetchAll();
    }
    
    public function getDadosDocPrincipal($idDocVinculado){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT *
                              FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                                   SAD_TB_DOCM_DOCUMENTO DOCM
                             WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                             AND VIDC.VIDC_ID_DOC_VINCULADO = $idDocVinculado");
        return $stmt->fetchAll();
    }
    
    public function getPrincipalOs($idDocVinculado)
    {
        $documento = new Application_Model_DbTable_SadTbDocmDocumento();
        $nrDocumento = $documento->fetchRow('DOCM_ID_DOCUMENTO = '.$idDocVinculado);
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT *
                FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                     SAD_TB_DOCM_DOCUMENTO DOCM
               WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
               AND VIDC.VIDC_ID_TP_VINCULACAO = 7
               AND VIDC.VIDC_ID_DOC_PRINCIPAL = $idDocVinculado";
        $stmt = $db->query($q);
        $arraySol = $stmt->fetch();
        if ($arraySol["DOCM_NR_DOCUMENTO"] != "") {
            $numeroOs[0]['DOCM_NR_DOCUMENTO'] = $arraySol["DOCM_NR_DOCUMENTO"];
        } else {
            $numeroOs = $this->getDocPrincipal($idDocVinculado);
        }
        if (count($numeroOs) == 0) {
            $numeroOs[0]['DOCM_NR_DOCUMENTO'] = $nrDocumento["DOCM_NR_DOCUMENTO"];
        }
        return $numeroOs;
    }
    
    public function getDadosSolicAssociadas($strAssoc, $idTpVinc)
    {
        $servicoSolic = new Sosti_Model_DataMapper_ServicoSolicitacao();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $SQL .= "SELECT DOCM_NR_DOCUMENTO, ";
        $SQL .= "  VIDC_ID_VINCULACAO_DOCUMENTO, ";
        $SQL .= "  VIDC_ID_DOC_PRINCIPAL, ";
        $SQL .= "  VIDC_ID_DOC_VINCULADO, ";
        $SQL .= "  TO_CHAR (VIDC_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIDC_DH_VINCULACAO, ";
        $SQL .= "  MOFA_ID_FASE, ";
        $SQL .= "  MOFA_ID_MOVIMENTACAO, ";
        $SQL .= "  PMAT_CD_MATRICULA, ";
        $SQL .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) PNAT_NO_PESSOA, ";
//        $SQL .= "  PNAT_NO_PESSOA, ";
        $SQL .= "  DOCM_CD_MATRICULA_CADASTRO, ";
        $SQL .= "  DOCM_DH_CADASTRO ";
        $SQL .= "FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC ";
        $SQL .= "INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM ";
        $SQL .= "ON VIDC.VIDC_ID_DOC_VINCULADO = DOCM.DOCM_ID_DOCUMENTO ";
        $SQL .= "INNER JOIN SAD_TB_MOFA_MOVI_FASE ";
        $SQL .= "ON MOFA_DH_FASE = VIDC_DH_VINCULACAO ";
        $SQL .= "INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO ";
        $SQL .= "ON MOFA_ID_MOVIMENTACAO = MODO_ID_MOVIMENTACAO ";
        $SQL .= "AND MODO_ID_DOCUMENTO   = VIDC_ID_DOC_VINCULADO ";
        $SQL .= "INNER JOIN OCS_TB_PMAT_MATRICULA ";
        $SQL .= "ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA ";
        $SQL .= "INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL ";
        $SQL .= "ON PMAT_ID_PESSOA             = PNAT_ID_PESSOA ";
        $SQL .= "WHERE DOCM.DOCM_NR_DOCUMENTO IN ($strAssoc) ";
        $SQL .= "AND VIDC.VIDC_ID_TP_VINCULACAO = $idTpVinc";
//        Zend_Debug::dump($SQL);
        try {
            $stmt = $db->query($SQL);
            $arraySol = $stmt->fetchAll();
            foreach ($arraySol as $k=>$aa) {
                $arrayFinal[] = $aa;
                $arrayFinal[$k]['DOCM_NR_DOCUMENTO']          = $aa['DOCM_NR_DOCUMENTO'];
                $arrayFinal[$k]['SSOL_ID_DOCUMENTO']          = $aa['VIDC_ID_DOC_VINCULADO'];
                $arrayFinal[$k]['MOFA_ID_MOVIMENTACAO']       = $aa['MOFA_ID_MOVIMENTACAO'];
                $arrayFinal[$k]['NOME_USARIO_CADASTRO']       = $aa["PNAT_NO_PESSOA"];
                $arrayFinal[$k]['DOCM_NR_DOCUMENTO']          = $aa['DOCM_NR_DOCUMENTO'];
                $arrayFinal[$k]['MOFA_ID_FASE']               = $aa['MOFA_ID_FASE'];
                $arrayFinal[$k]['VIDC_DH_VINCULACAO']         = $aa['VIDC_DH_VINCULACAO'];
                $arrayFinal[$k]['USUARIO_CADASTRO']           = $aa['USUARIO_CADASTRO'];
                $arrayFinal[$k]['DOCM_DH_CADASTRO']           = $aa['DOCM_DH_CADASTRO'];
                $arrayFinal[$k]['SSER_DS_SERVICO']            = $servicoSolic->getAtual($aa['VIDC_ID_DOC_VINCULADO']);
            }
        } catch (Exception $ex) {
            $arrayFinal = false;
        }
        return $arrayFinal;
    }
} 