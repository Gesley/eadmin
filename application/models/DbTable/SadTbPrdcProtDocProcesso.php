<?php
class Application_Model_DbTable_SadTbPrdcProtDocProcesso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_PRDC_PROT_DOC_PROCESSO';
    protected $_primary = 'PRDC_ID_PROT_DOC_PROCESSO';
    protected $_sequence = 'SAD_SQ_ID_PROT_DOC_PROCESSO';
    
    
    /*
     * Cria o endereçamento de postagem dos documentos especificados, podendo 
     * eles serem endereçados em pacote ou individualmente.
     * 
     * Caso endereçados em pacote o mesmo ID de endereçamento irá para todos os 
     * documentos.
     * 
     * Utilização 1:
     * Modulo: CaixaUnidade
     * Controller: Enderecar
     */
    public function setEnderecar(array $postagem, array $documentos){
        
        $postagem["POST_CD_PESSOA_DESTINO"]         = (int)$postagem["POST_CD_PESSOA_DESTINO"];
        $postagem["POST_NM_DESTINATARIO_EXTERNO"]   = strtoupper($postagem["POST_NM_DESTINATARIO_EXTERNO"]);
        $postagem["POST_DS_TRATAMENTO_EXTERNO"]     = strtoupper($postagem["POST_DS_TRATAMENTO_EXTERNO"]);
        $postagem["POST_DS_ENDERECO_DESTINO"]       = strtoupper($postagem["POST_DS_ENDERECO_DESTINO"]);
        $postagem["POST_DS_BAIRRO_DESTINO"]         = strtoupper($postagem["POST_DS_BAIRRO_DESTINO"]);
        $postagem["POST_DS_CIDADE_DESTINO"]         = strtoupper($postagem["POST_DS_CIDADE_DESTINO"]);
        $postagem["POST_CD_UF_DESTINO"]             = strtoupper($postagem["POST_CD_UF_DESTINO"]);
        $postagem["POST_DS_PAIS_DESTINO"]           = strtoupper($postagem["POST_DS_PAIS_DESTINO"]);
        $postagem["POST_CD_CEP_DESTINO"]            = (int)$postagem["POST_CD_CEP_DESTINO"];
        $postagem["POST_SG_SECAO_ORIGEM"]           = strtoupper($postagem["POST_SG_SECAO_ORIGEM"]);
        $postagem["POST_CD_LOTACAO_ORIGEM"]         = (int)$postagem["POST_CD_LOTACAO_ORIGEM"];

        $LOTACAO_DESTINO = explode(' - ', $postagem['POST_CD_PESSOA_DESTINO']);
        $postagem['POST_CD_PESSOA_DESTINO'] = $LOTACAO_DESTINO[0];
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        
        if($postagem["POST_IC_ENVELOPE_FECHADO"] == 'S')
        {
            try {
                $SadTbPostPostagemProcDoc = new Application_Model_DbTable_SadTbPostPostagemProcDoc();
                $setPostagem = $SadTbPostPostagemProcDoc->createRow($postagem);
                $prdcProtDocProcesso["PRDC_ID_POSTAGEM_PROC_DOC"] = $setPostagem->save();
                
                foreach ($documentos as $value) {
                    $prdcProtDocProcesso["PRDC_ID_DOCUMENTO"] = $value["DOCM_ID_DOCUMENTO"];

                    if($dadosDocumento == "PROCESSO"){
                        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
                        $prdoIdProcFspr = $SadTbPrdcProtDocProcesso->getIdProcesso($value);
                        $prdcProtDocProcesso["PRDC_ID_PROC_FSPR"] = $prdoIdProcFspr[0]["PRDI_ID_PROC_FSPR"];

                        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
                        $setProtocoloProcesso = $SadTbProtProtocolo->createRow($prdcProtDocProcesso);
                        $ProtocoloDocProcesso = $setProtocoloProcesso->save();
                    }else{
                        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
                        $setProtocoloProcesso = $SadTbPrdcProtDocProcesso->createRow($prdcProtDocProcesso);
                        $ProtocoloDocProcesso = $setProtocoloProcesso->save();
                    }
                }
                
                $db->commit();
            } catch (Exception $exc) {
                $db->rollBack();
                Zend_Debug::dump($exc->getMessage());
                exit;
            }
            
        }else if($postagem["POST_IC_ENVELOPE_FECHADO"] == 'N'){
            try {
                foreach ($documentos as $value) {
                    $prdcProtDocProcesso["PRDC_ID_DOCUMENTO"] = $value["DOCM_ID_DOCUMENTO"];
                    
                    $SadTbPostPostagemProcDoc = new Application_Model_DbTable_SadTbPostPostagemProcDoc();
                    $setPostagem = $SadTbPostPostagemProcDoc->createRow($postagem);
                    $prdcProtDocProcesso["PRDC_ID_POSTAGEM_PROC_DOC"] = $setPostagem->save();
                    
                    if($dadosDocumento == "PROCESSO"){
                        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
                        $prdoIdProcFspr = $SadTbPrdcProtDocProcesso->getIdProcesso($value);
                        $prdcProtDocProcesso["PRDC_ID_PROC_FSPR"] = $prdoIdProcFspr[0]["PRDI_ID_PROC_FSPR"];

                        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
                        $setProtocoloProcesso = $SadTbProtProtocolo->createRow($prdcProtDocProcesso);
                        $ProtocoloDocProcesso = $setProtocoloProcesso->save();
                    }else{
                        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
                        $setProtocoloProcesso = $SadTbPrdcProtDocProcesso->createRow($prdcProtDocProcesso);
                        $ProtocoloDocProcesso = $setProtocoloProcesso->save();
                    }
                }
                $db->commit();
            } catch (Exception $exc) {
                $db->rollBack();
                echo $exc->getTraceAsString();
            }
        }
    }
    
    /*
     * Cria um numero de protocolo para os pacotes selecionados.
     * Um protocolo pode conter 1 ou mais pacotes.
     * O numero de protocolo contem um sequencial de:
     * Ano + Código da Seção + Código da Lotação + Sequencial da unidade
     * 
     * Utilização 1:
     * Modulo: CaixaUnidade
     * Controller:Protocolar
     * 
     */
    public function setProtocolo(array $protocolo, array $idpostagem){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $SadTbProtProtocolo = new Application_Model_DbTable_SadTbProtProtocolo();
            $SadTbPrdcProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            
            /*
             * Cria o protocolo
             */
            $id = $protocolo["PROT_ID_PROTOCOLO"];
            $sec = $protocolo["PROT_SG_SECAO"];
            $lot = $protocolo["PROT_CD_LOTACAO"];
            $mat = $protocolo["PROT_CD_MATRICULA"];
            $IdProtocolo = $db->query("INSERT INTO SAD_TB_PROT_PROTOCOLO VALUES($id,'$sec',$lot,'$mat')");
            
            /*
             * Cria o Processo
             */
            $prdcProtDocProcesso["PRDC_ID_PROTOCOLO"] = $protocolo["PROT_ID_PROTOCOLO"];
            $cont = 0;
            foreach ($idpostagem as $value) {
                $query = Zend_Db_Table_Abstract::getDefaultAdapter();
                $stmt = $query->query("SELECT PRDC_ID_PROT_DOC_PROCESSO,
                                              PRDC_ID_DOCUMENTO 
                                       FROM SAD.SAD_TB_PRDC_PROT_DOC_PROCESSO 
                                      WHERE PRDC_ID_POSTAGEM_PROC_DOC = $value");
                $dadosProcesso =  $stmt->fetchAll();
                
                foreach ($dadosProcesso as $values){

                    $prdcProtDocProcesso["PRDC_ID_PROT_DOC_PROCESSO"] = $values["PRDC_ID_PROT_DOC_PROCESSO"];
                    $prdcProtDocProcesso["PRDC_DH_PROTOCOLO_DOC_PROC"] = new Zend_Db_Expr('SYSDATE');
                    $prdcProtDocProcesso["PRDC_ID_DOCUMENTO"] = $values["PRDC_ID_DOCUMENTO"];

                    $rowProcesso = $SadTbPrdcProcesso->find($prdcProtDocProcesso["PRDC_ID_PROT_DOC_PROCESSO"])->current();
                    unset ($prdcProtDocProcesso["PRDC_ID_PROT_DOC_PROCESSO"]);
                    $rowProcesso->setFromArray($prdcProtDocProcesso);
                    $rowProcesso->save();
                }
            }
            $aNamespace = new Zend_Session_Namespace('protocolo');
            $aNamespace->protocolo = $prdcProtDocProcesso["PRDC_ID_PROTOCOLO"];
            $db->commit();
        } catch (Exception $exc) {
            echo $exc->getMessage();
            $db->rollBack();
        }
    }
    
    public function getNrProtocolo($order){
        if(!isset($order)){
            $order = 'PRDC_ID_PROTOCOLO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  B.PRDC_ID_PROTOCOLO PROTOCOLO, 
                                    COUNT(PRDC_ID_PROTOCOLO) QTD,
                                    A.LOTA_SIGLA_LOTACAO||' - '||A.LOTA_DSC_LOTACAO REMETENTE, 
                                    B.PRDC_DH_PROTOCOLO_DOC_PROC CRIACAO,
                                    C.PJUR_NO_FANTASIA DESTINATARIO
                            FROM    RH_CENTRAL_LOTACAO A,
                                    SAD_TB_PRDC_PROT_DOC_PROCESSO B,
                                    OCS_TB_PJUR_PESSOA_JURIDICA C,
                                    SAD_TB_POST_POSTAGEM_PROC_DOC D,
                                    SAD_TB_PROT_PROTOCOLO E
                            WHERE   A.LOTA_SIGLA_SECAO = D.POST_SG_SECAO_ORIGEM
                            AND     A.LOTA_COD_LOTACAO = D.POST_CD_LOTACAO_ORIGEM
                            AND     B.PRDC_ID_PROTOCOLO = E.PROT_ID_PROTOCOLO
                            AND     B.PRDC_ID_POSTAGEM_PROC_DOC = D.POST_ID_POSTAGEM_PROC_DOC 
                            AND     E.PROT_SG_SECAO = A.LOTA_SIGLA_SECAO
                            AND     E.PROT_CD_LOTACAO = A.LOTA_COD_LOTACAO 
                            AND     D.POST_CD_PESSOA_DESTINO = C.PJUR_ID_PESSOA
                            GROUP BY LOTA_SIGLA_LOTACAO,
                                     LOTA_DSC_LOTACAO,
                                     PRDC_ID_PROTOCOLO,
                                     PRDC_DH_PROTOCOLO_DOC_PROC,
                                     PJUR_NO_FANTASIA  
                                ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getDocumentosProcesso($idProtocolo){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRDC_ID_DOCUMENTO,
                                   PRDC_ID_POSTAGEM_PROC_DOC,
                                   PRDC_IC_RECEBIMENTO,
                                   POST_ID_TIPO_POSTAGEM
                             FROM  SAD_TB_PRDC_PROT_DOC_PROCESSO a, sad_tb_post_postagem_proc_doc b
                            WHERE  A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                              AND  PRDC_ID_PROTOCOLO = $idProtocolo");
        return $stmt->fetchAll();
    }
    
    /*
     * Retorna todos os pacotes cujo protocolo foi passado como 
     * parametro e já foram recebidos fisicamente.
     * 
     * Utilização 1:
     * Modulo: SISAD
     * Controller: Protocolo
     * Action: Postagem
     */
    public function getPacotesPostagem($idProtocolo){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(A.PRDC_ID_PROT_DOC_PROCESSO) QTD,
                                        A.PRDC_ID_PROTOCOLO,A.PRDC_ID_POSTAGEM_PROC_DOC,
                                        B.POST_NM_DESTINATARIO_EXTERNO,
                                        B.POST_DS_ENDERECO_DESTINO, 
                                        B.POST_DS_BAIRRO_DESTINO, 
                                        B.POST_DS_CIDADE_DESTINO, 
                                        B.POST_CD_UF_DESTINO, 
                                        B.POST_CD_CEP_DESTINO,
                                        B.POST_DS_PAIS_DESTINO,
                                        B.POST_CD_LOTACAO_ORIGEM,
                                        B.POST_VR_POSTAGEM,
                                        B.POST_CD_CORREIO_ENVIO,
                                        B.POST_NR_PESO_POSTAGEM_PROC_DOC,
                                        C.PJUR_NO_FANTASIA,
                                        I.LOTA_SIGLA_LOTACAO||' - '||I.LOTA_DSC_LOTACAO REMETENTE,
                                        J.tppo_ds_tipo_postagem
                                   FROM SAD_TB_PRDC_PROT_DOC_PROCESSO A,
                                        SAD_TB_POST_POSTAGEM_PROC_DOC B,
                                        OCS_TB_PJUR_PESSOA_JURIDICA C,
                                        OCS_TB_PMAT_MATRICULA D,
                                        SAD_TB_PROT_PROTOCOLO E,
                                        OCS_TB_PNAT_PESSOA_NATURAL F,
                                        SAD_TB_DOCM_DOCUMENTO G,
                                        OCS_TB_DTPD_TIPO_DOC H,
                                        RH_CENTRAL_LOTACAO I,
                                        SAD_TB_TPPO_TIPO_POSTAGEM J
                                  WHERE A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                                    AND C.PJUR_ID_PESSOA = B.POST_CD_PESSOA_DESTINO
                                    AND D.PMAT_CD_MATRICULA = E.PROT_CD_MATRICULA
                                    AND E.PROT_ID_PROTOCOLO = A.PRDC_ID_PROTOCOLO
                                    AND D.PMAT_ID_PESSOA = F.PNAT_ID_PESSOA
                                    AND A.PRDC_ID_DOCUMENTO = G.DOCM_ID_DOCUMENTO
                                    AND G.DOCM_ID_TIPO_DOC = H.DTPD_ID_TIPO_DOC
                                    AND B.POST_CD_LOTACAO_ORIGEM = I.LOTA_COD_LOTACAO
                                    AND B.POST_SG_SECAO_ORIGEM = I.LOTA_SIGLA_SECAO
                                    AND J.TPPO_ID_TIPO_POSTAGEM = B.POST_ID_TIPO_POSTAGEM
                                    AND A.PRDC_ID_PROTOCOLO IS NOT NULL
                                    AND A.PRDC_ID_PROTOCOLO = $idProtocolo
                                    AND A.PRDC_IC_RECEBIMENTO = 'S'
                                 GROUP BY A.PRDC_ID_PROTOCOLO,A.PRDC_ID_POSTAGEM_PROC_DOC,
                                        B.POST_NM_DESTINATARIO_EXTERNO,
                                        B.POST_DS_ENDERECO_DESTINO, 
                                        B.POST_DS_BAIRRO_DESTINO, 
                                        B.POST_DS_CIDADE_DESTINO, 
                                        B.POST_CD_UF_DESTINO, 
                                        B.POST_CD_CEP_DESTINO,
                                        B.POST_DS_PAIS_DESTINO,
                                        B.POST_CD_LOTACAO_ORIGEM,
                                        B.POST_VR_POSTAGEM,
                                        B.POST_CD_CORREIO_ENVIO,
                                        B.POST_NR_PESO_POSTAGEM_PROC_DOC,
                                        C.PJUR_NO_FANTASIA,
                                        I.LOTA_SIGLA_LOTACAO||' - '||I.LOTA_DSC_LOTACAO,
                                        J.TPPO_DS_TIPO_POSTAGEM");
        return $stmt->fetchAll();
    }
    
    public function getEnderecamento($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRDC_ID_PROT_DOC_PROCESSO,
                                  PRDC_ID_DOCUMENTO
                             FROM SAD_TB_PRDC_PROT_DOC_PROCESSO,
                                  SAD_TB_POST_POSTAGEM_PROC_DOC
                            WHERE PRDC_ID_DOCUMENTO = $idDocumento
                              AND PRDC_ID_PROTOCOLO IS NULL");
        return $stmt->fetchAll();
        
    }
    
    
    public function getProtocolado($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRDC_ID_PROT_DOC_PROCESSO,
                                  PRDC_ID_DOCUMENTO,
                                  PRDC_ID_PROTOCOLO,
                                  PRDC_ID_POSTAGEM_PROC_DOC
                             FROM SAD_TB_PRDC_PROT_DOC_PROCESSO,
                                  SAD_TB_POST_POSTAGEM_PROC_DOC
                            WHERE PRDC_ID_DOCUMENTO = $idDocumento
                              AND PRDC_ID_PROTOCOLO IS NOT NULL
                             GROUP BY PRDC_ID_PROT_DOC_PROCESSO,
                                  PRDC_ID_DOCUMENTO,
                                  PRDC_ID_PROTOCOLO,
                                  PRDC_ID_POSTAGEM_PROC_DOC");
        return $stmt->fetchAll();
    }
    
    /*
     * Busca os dados de endereçamento de um documento.
     * 
     * Utilização 1:
     * MODULO: SISAD
     * Controller: detalhedcmto
     * View: detalhedcmto
     * 
     */
    public function getDadosEnderecados($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT B.POST_DS_TRATAMENTO_EXTERNO||' '||B.POST_NM_DESTINATARIO_EXTERNO NOME_EXTERNO,
                                   B.POST_CD_PESSOA_DESTINO||' - '||C.PJUR_NO_FANTASIA DESTINATARIO,
                                   B.POST_DS_ENDERECO_DESTINO, 
                                   B.POST_DS_BAIRRO_DESTINO, 
                                   B.POST_DS_CIDADE_DESTINO, 
                                   B.POST_CD_UF_DESTINO, 
                                   B.POST_CD_CEP_DESTINO,
                                   A.PRDC_ID_PROT_DOC_PROCESSO
                              FROM SAD_TB_PRDC_PROT_DOC_PROCESSO A,
                                   SAD_TB_POST_POSTAGEM_PROC_DOC B,
                                   OCS_TB_PJUR_PESSOA_JURIDICA C 
                            WHERE  A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                            AND A.PRDC_ID_DOCUMENTO = $idDocumento
                            AND C.PJUR_ID_PESSOA = B.POST_CD_PESSOA_DESTINO
                            AND A.PRDC_ID_PROTOCOLO IS NULL");
        return $stmt->fetchAll();
    }
    
    /*
     * Verifica se um determinando documento foi protocolado.
     * 
     * Utilização 1:
     * MODULO: SISAD
     * Controller: detalhedcmto
     * View: detalhedcmto
     * 
     * 
     */
    public function getDadosProtocolados($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PRDC_ID_PROTOCOLO,
                                   A.PRDC_ID_DOCUMENTO,
                                   A.PRDC_DH_PROTOCOLO_DOC_PROC,
                                   E.PROT_CD_MATRICULA||' - '||F.PNAT_NO_PESSOA CRIADOR,
                                   B.POST_DS_TRATAMENTO_EXTERNO||' '||B.POST_NM_DESTINATARIO_EXTERNO NOME_EXTERNO,
                                   B.POST_CD_PESSOA_DESTINO||' - '||C.PJUR_NO_FANTASIA DESTINATARIO,
                                   B.POST_DS_ENDERECO_DESTINO, 
                                   B.POST_DS_BAIRRO_DESTINO, 
                                   B.POST_DS_CIDADE_DESTINO, 
                                   B.POST_CD_UF_DESTINO, 
                                   B.POST_CD_CEP_DESTINO,
                                   B.POST_ID_TIPO_POSTAGEM,
                                   A.PRDC_IC_RECEBIMENTO
                              FROM SAD_TB_PRDC_PROT_DOC_PROCESSO A,
                                   SAD_TB_POST_POSTAGEM_PROC_DOC B,
                                   OCS_TB_PJUR_PESSOA_JURIDICA C,
                                   OCS_TB_PMAT_MATRICULA D,
                                   SAD_TB_PROT_PROTOCOLO E,
                                   OCS_TB_PNAT_PESSOA_NATURAL F 
                            WHERE  A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                            AND A.PRDC_ID_DOCUMENTO = $idDocumento
                            AND C.PJUR_ID_PESSOA = B.POST_CD_PESSOA_DESTINO
                            AND D.PMAT_CD_MATRICULA = E.PROT_CD_MATRICULA
                            AND E.PROT_ID_PROTOCOLO = A.PRDC_ID_PROTOCOLO
                            AND D.PMAT_ID_PESSOA = F.PNAT_ID_PESSOA");
        return $stmt->fetchAll();
    }
    
    /*
     * Retorna todos os Pacotes cujo protocolo foi passado como parametro.
     * 
     * Utilização 1:
     * Modulo: Sisad
     * Controller: Protocolo
     * Action: entrada
     */
    public function getDadosProtocoladosPacote($idprotocolo){
        if(!isset($order)){
            $order = 'DOCM_NR_DOCUMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(A.PRDC_ID_PROT_DOC_PROCESSO) QTD,
                                           A.PRDC_IC_RECEBIMENTO,  
                                           A.PRDC_ID_PROTOCOLO,A.PRDC_ID_POSTAGEM_PROC_DOC,
                                           B.POST_NM_DESTINATARIO_EXTERNO,
                                           B.POST_CD_PESSOA_DESTINO,
                                           C.PJUR_NO_FANTASIA,
                                           B.POST_DS_ENDERECO_DESTINO, 
                                           B.POST_DS_BAIRRO_DESTINO, 
                                           B.POST_DS_CIDADE_DESTINO, 
                                           B.POST_CD_UF_DESTINO, 
                                           B.POST_CD_CEP_DESTINO,
                                           B.POST_DS_PAIS_DESTINO,
                                           B.POST_CD_LOTACAO_ORIGEM,
                                           I.LOTA_SIGLA_LOTACAO||' - '||I.LOTA_DSC_LOTACAO REMETENTE
                                      FROM SAD_TB_PRDC_PROT_DOC_PROCESSO A,
                                           SAD_TB_POST_POSTAGEM_PROC_DOC B,
                                           OCS_TB_PJUR_PESSOA_JURIDICA C,
                                           OCS_TB_PMAT_MATRICULA D,
                                           SAD_TB_PROT_PROTOCOLO E,
                                           OCS_TB_PNAT_PESSOA_NATURAL F,
                                           SAD_TB_DOCM_DOCUMENTO G,
                                           OCS_TB_DTPD_TIPO_DOC H,
                                           RH_CENTRAL_LOTACAO I 
                                     WHERE A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                                       AND C.PJUR_ID_PESSOA = B.POST_CD_PESSOA_DESTINO
                                       AND D.PMAT_CD_MATRICULA = E.PROT_CD_MATRICULA
                                       AND E.PROT_ID_PROTOCOLO = A.PRDC_ID_PROTOCOLO
                                       AND D.PMAT_ID_PESSOA = F.PNAT_ID_PESSOA
                                       AND A.PRDC_ID_DOCUMENTO = G.DOCM_ID_DOCUMENTO
                                       AND G.DOCM_ID_TIPO_DOC = H.DTPD_ID_TIPO_DOC
                                       AND B.POST_CD_LOTACAO_ORIGEM = I.LOTA_COD_LOTACAO
                                       AND B.POST_SG_SECAO_ORIGEM = I.LOTA_SIGLA_SECAO
                                       AND A.PRDC_ID_PROTOCOLO IS NOT NULL
                                       AND A.PRDC_ID_PROTOCOLO = $idprotocolo
                                    GROUP BY A.PRDC_IC_RECEBIMENTO,
                                           A.PRDC_ID_PROTOCOLO,
                                           A.PRDC_ID_POSTAGEM_PROC_DOC,
                                           B.POST_NM_DESTINATARIO_EXTERNO,
                                           B.POST_CD_PESSOA_DESTINO,
                                           C.PJUR_NO_FANTASIA,
                                           B.POST_DS_ENDERECO_DESTINO, 
                                           B.POST_DS_BAIRRO_DESTINO, 
                                           B.POST_DS_CIDADE_DESTINO, 
                                           B.POST_CD_UF_DESTINO, 
                                           B.POST_CD_CEP_DESTINO,
                                           B.POST_DS_PAIS_DESTINO,
                                           B.POST_CD_LOTACAO_ORIGEM,
                                           I.LOTA_SIGLA_LOTACAO||' - '||I.LOTA_DSC_LOTACAO");
        return $stmt->fetchAll();
    }
    
    /*
     * Busca o id do processo para efetuar o recebimento dos pacotes.
     * 
     * Utilização 1:
     * Modulo: SISAD
     * Controller: Protocolo
     * Action: Receber
     * 
     * 
     */
    public function getIdProtocolo($idPostagem,$idProtocolo) {
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $stmt = $db->query("SELECT PRDC_ID_PROT_DOC_PROCESSO
                             FROM SAD_TB_PRDC_PROT_DOC_PROCESSO
                            WHERE PRDC_ID_POSTAGEM_PROC_DOC = $idPostagem
                              AND PRDC_ID_PROTOCOLO = $idProtocolo");
       return $stmt->fetchAll();
    }
    
    /*
     * Retorna a quantidade de protocolos de uma determinada lotação, para gerar
     * o proximo numero de protocolo
     * 
     * Utilização 1:
     * Modulo : SISAD
     * Controller: Protocolo
     * Action: Protocolar
     * 
     */
    public function getProtocolosSecao($nrLotacao, $sgSecao){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(PROT_ID_PROTOCOLO) QTD 
                              FROM SAD_TB_PROT_PROTOCOLO
                             WHERE PROT_CD_LOTACAO = $nrLotacao
                               AND PROT_SG_SECAO = '$sgSecao'");
        return $stmt->fetchAll();
    }
    
    /*
     * Gera as etiquetas de acordo com as datas especificadas
     * 
     * Utilização 1: 
     * Modulo: SISAD
     * Controller: Etiqueta
     * Action: Criar
     */
    public function getEtiquetas($dataInicio,$dataFim) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PRDC_ID_PROTOCOLO,
                                   A.PRDC_ID_POSTAGEM_PROC_DOC,
                                   COUNT(A.PRDC_ID_PROT_DOC_PROCESSO) QTD,
                                   B.POST_NM_DESTINATARIO_EXTERNO,
                                   B.POST_CD_PESSOA_DESTINO,
                                   C.PJUR_NO_FANTASIA,
                                   B.POST_DS_ENDERECO_DESTINO, 
                                   B.POST_DS_BAIRRO_DESTINO, 
                                   B.POST_DS_CIDADE_DESTINO, 
                                   B.POST_CD_UF_DESTINO, 
                                   B.POST_CD_CEP_DESTINO,
                                   B.POST_DS_PAIS_DESTINO
                              FROM SAD_TB_PRDC_PROT_DOC_PROCESSO A,
                                   SAD_TB_POST_POSTAGEM_PROC_DOC B,
                                   OCS_TB_PJUR_PESSOA_JURIDICA C,
                                   OCS_TB_PMAT_MATRICULA D,
                                   SAD_TB_PROT_PROTOCOLO E,
                                   OCS_TB_PNAT_PESSOA_NATURAL F,
                                   SAD_TB_DOCM_DOCUMENTO G,
                                   OCS_TB_DTPD_TIPO_DOC H,
                                   RH_CENTRAL_LOTACAO I 
                             WHERE A.PRDC_ID_POSTAGEM_PROC_DOC = B.POST_ID_POSTAGEM_PROC_DOC
                               AND C.PJUR_ID_PESSOA = B.POST_CD_PESSOA_DESTINO
                               AND D.PMAT_CD_MATRICULA = E.PROT_CD_MATRICULA
                               AND E.PROT_ID_PROTOCOLO = A.PRDC_ID_PROTOCOLO
                               AND D.PMAT_ID_PESSOA = F.PNAT_ID_PESSOA
                               AND A.PRDC_ID_DOCUMENTO = G.DOCM_ID_DOCUMENTO
                               AND G.DOCM_ID_TIPO_DOC = H.DTPD_ID_TIPO_DOC
                               AND B.POST_CD_LOTACAO_ORIGEM = I.LOTA_COD_LOTACAO
                               AND B.POST_SG_SECAO_ORIGEM = I.LOTA_SIGLA_SECAO
                               AND A.PRDC_ID_PROTOCOLO IS NOT NULL
                               AND A.PRDC_DH_PROTOCOLO_DOC_PROC >= TO_DATE('$dataInicio 00:00:00','DD/MM/RRRR HH24:MI:SS')
                               AND A.PRDC_DH_PROTOCOLO_DOC_PROC <= TO_DATE('$dataFim 23:59:59','DD/MM/RRRR HH24:MI:SS')
                               GROUP BY A.PRDC_ID_POSTAGEM_PROC_DOC,
                                        A.PRDC_ID_PROTOCOLO,
                                        B.POST_NM_DESTINATARIO_EXTERNO,
                                        B.POST_CD_PESSOA_DESTINO,
                                        C.PJUR_NO_FANTASIA,
                                        B.POST_DS_ENDERECO_DESTINO, 
                                        B.POST_DS_BAIRRO_DESTINO, 
                                        B.POST_DS_CIDADE_DESTINO, 
                                        B.POST_CD_UF_DESTINO, 
                                        B.POST_CD_CEP_DESTINO,
                                        B.POST_DS_PAIS_DESTINO");
        return $stmt->fetchAll();
    }
}
