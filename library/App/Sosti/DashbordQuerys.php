<?php
/**
 * Description of Anexo
 *
 * @author Leonan Alves dos Anjos
 */


class App_Sosti_DashbordQuerys {
    
public function getdadosDash($params){
        
        if($params['SG_SECAO'] == 1){
             $secao = "";
             $caixa = "AND MOD3.MODE_ID_CAIXA_ENTRADA       = ".$params['ID_CAIXA'];
        }else{
             $secao = "AND DOCM.DOCM_SG_SECAO_REDATORA = '".$params['SG_SECAO']."'";
             $caixa = "";
        }
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $stmt = "SELECT (SELECT COUNT(*)QTDE
                           FROM SAD_TB_DOCM_DOCUMENTO DOCM
                      LEFT JOIN (SELECT MODO_ID_DOCUMENTO,
                                 MAX(MODO_ID_MOVIMENTACAO) MODO_ID_MOVIMENTACAO
                           FROM SAD_TB_MODO_MOVI_DOCUMENTO
                       GROUP BY MODO_ID_DOCUMENTO )MODO 
                             ON MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                      LEFT JOIN (SELECT BASE.MOFA_ID_MOVIMENTACAO,
                                       BASE.MOFA_DH_FASE,
                                       MAIS.MOFA_ID_FASE
                                  FROM (SELECT MOFA_ID_MOVIMENTACAO,
                                        MAX(MOFA_DH_FASE) MOFA_DH_FASE
                                   FROM SAD_TB_MOFA_MOVI_FASE
                               GROUP BY MOFA_ID_MOVIMENTACAO ) BASE
                              LEFT JOIN SAD_TB_MOFA_MOVI_FASE MAIS 
                                     ON MAIS.MOFA_ID_MOVIMENTACAO = BASE.MOFA_ID_MOVIMENTACAO   
                                    AND MAIS.MOFA_DH_FASE = BASE.MOFA_DH_FASE) MOFA 
                             ON MOFA.MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO
                      LEFT JOIN SAD_TB_MODE_MOVI_DESTINATARIO   MOD3 
                             ON MOD3.MODE_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO   
                          WHERE DOCM.DOCM_ID_TIPO_DOC       = 160   
                            ".$caixa."
                            ".$secao ."  
                            AND DOCM.DOCM_DH_CADASTRO BETWEEN TO_DATE('".$params['DATA_INICIO']."', 'DD/MM/YYYY') 
                                                          AND TO_DATE('".$params['DATA_FIM']."', 'DD/MM/YYYY')+1-1/24/60/60) AS ABERTA,
                        (SELECT COUNT(*) QTDE
                           FROM SAD_TB_DOCM_DOCUMENTO DOCM
                      LEFT JOIN (SELECT MODO_ID_DOCUMENTO,
                                        MAX(MODO_ID_MOVIMENTACAO) MODO_ID_MOVIMENTACAO
                                   FROM SAD_TB_MODO_MOVI_DOCUMENTO 
                                  GROUP BY MODO_ID_DOCUMENTO) MODO 
                             ON MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                      LEFT JOIN (SELECT BASE.MOFA_ID_MOVIMENTACAO,
                                        BASE.MOFA_DH_FASE,
                                        MAIS.MOFA_ID_FASE
                                  FROM (SELECT MOFA_ID_MOVIMENTACAO,
                                               MAX(MOFA_DH_FASE) MOFA_DH_FASE
                                          FROM SAD_TB_MOFA_MOVI_FASE
                                      GROUP BY MOFA_ID_MOVIMENTACAO) BASE
                             LEFT JOIN SAD_TB_MOFA_MOVI_FASE MAIS 
                                    ON MAIS.MOFA_ID_MOVIMENTACAO = BASE.MOFA_ID_MOVIMENTACAO   
                                   AND MAIS.MOFA_DH_FASE = BASE.MOFA_DH_FASE) MOFA 
                             ON MOFA.MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO 
                      LEFT JOIN SAD_TB_MODE_MOVI_DESTINATARIO   MOD3 
                             ON MOD3.MODE_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO   
                          WHERE DOCM.DOCM_ID_TIPO_DOC       = 160       
                             ".$caixa."
                             ".$secao ."      
                            AND MOFA.MOFA_ID_FASE NOT IN (1000, 1014)   
                            AND DOCM.DOCM_DH_CADASTRO BETWEEN TO_DATE('".$params['DATA_INICIO']."', 'DD/MM/YYYY') 
                            AND TO_DATE('".$params['DATA_FIM']."', 'DD/MM/YYYY')+1-1/24/60/60 

                       ) AS ATENDIDA,

                       (SELECT COUNT(*) QTDE
                          FROM SAD_TB_DOCM_DOCUMENTO DOCM
                     LEFT JOIN (SELECT MODO_ID_DOCUMENTO,
                                       MAX(MODO_ID_MOVIMENTACAO) MODO_ID_MOVIMENTACAO
                                  FROM SAD_TB_MODO_MOVI_DOCUMENTO
                              GROUP BY MODO_ID_DOCUMENTO) MODO 
                            ON MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                     LEFT JOIN (SELECT BASE.MOFA_ID_MOVIMENTACAO,
                                       BASE.MOFA_DH_FASE,
                                       MAIS.MOFA_ID_FASE
                                  FROM (SELECT MOFA_ID_MOVIMENTACAO,
                                               MAX(MOFA_DH_FASE) MOFA_DH_FASE
                                          FROM SAD_TB_MOFA_MOVI_FASE
                                      GROUP BY MOFA_ID_MOVIMENTACAO) BASE
                             LEFT JOIN SAD_TB_MOFA_MOVI_FASE MAIS 
                                    ON MAIS.MOFA_ID_MOVIMENTACAO = BASE.MOFA_ID_MOVIMENTACAO   
                                   AND MAIS.MOFA_DH_FASE = BASE.MOFA_DH_FASE) MOFA 
                                    ON MOFA.MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO
                             LEFT JOIN SAD_TB_MODE_MOVI_DESTINATARIO   MOD3 
                                    ON MOD3.MODE_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO   
                                 WHERE DOCM.DOCM_ID_TIPO_DOC       = 160   
                                    ".$caixa."
                                    ".$secao ."  
                                   AND MOFA.MOFA_ID_FASE = 1007            
                                   AND DOCM.DOCM_DH_CADASTRO BETWEEN TO_DATE('".$params['DATA_INICIO']."', 'DD/MM/YYYY') 
                                   AND TO_DATE('".$params['DATA_FIM']."', 'DD/MM/YYYY')+1-1/24/60/60 ) AS ESPERA,
                       (SELECT COUNT(*) QTDE 
                          FROM SAD_TB_DOCM_DOCUMENTO DOCM
                     LEFT JOIN (SELECT MODO_ID_DOCUMENTO,
                                       MAX(MODO_ID_MOVIMENTACAO) MODO_ID_MOVIMENTACAO
                                  FROM SAD_TB_MODO_MOVI_DOCUMENTO
                              GROUP BY MODO_ID_DOCUMENTO) MODO 
                            ON MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                     LEFT JOIN (SELECT BASE.MOFA_ID_MOVIMENTACAO,
                                       BASE.MOFA_DH_FASE,
                                       MAIS.MOFA_ID_FASE
                                  FROM (SELECT MOFA_ID_MOVIMENTACAO,
                                               MAX(MOFA_DH_FASE) MOFA_DH_FASE
                                          FROM SAD_TB_MOFA_MOVI_FASE
                                      GROUP BY MOFA_ID_MOVIMENTACAO) BASE
                             LEFT JOIN SAD_TB_MOFA_MOVI_FASE MAIS 
                                    ON MAIS.MOFA_ID_MOVIMENTACAO = BASE.MOFA_ID_MOVIMENTACAO   
                                   AND MAIS.MOFA_DH_FASE = BASE.MOFA_DH_FASE ) MOFA 
                            ON MOFA.MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO   
                     LEFT JOIN SAD_TB_MODE_MOVI_DESTINATARIO   MOD3 
                            ON MOD3.MODE_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO   
                         WHERE DOCM.DOCM_ID_TIPO_DOC       = 160   
                            ".$caixa."
                            ".$secao ."  
                           AND MOFA.MOFA_ID_FASE = 1000            
                           AND DOCM.DOCM_DH_CADASTRO BETWEEN TO_DATE('".$params['DATA_INICIO']."', 'DD/MM/YYYY') 
                           AND TO_DATE('".$params['DATA_FIM']."', 'DD/MM/YYYY')+1-1/24/60/60 ) AS BAIXADA
                  FROM DUAL";
        
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }
}
