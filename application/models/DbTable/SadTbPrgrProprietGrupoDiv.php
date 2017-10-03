<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SadTbPrgrProprietGrupoDiv extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_PRGR_PROPRIET_GRUPO_DIV';
    protected $_primary = 'PRGR_ID_GRUPO_DIVULGACAO';
//    protected $_sequence = 'SAD_SQ_GRDV';/*Não tem SEQUENCE, é uma FK*/


   public function getProprietarios($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PRGR_ID_GRUPO_DIVULGACAO,
                                   PRGR_SG_SECAO_PROPRIET_GR_DIV,
                                   PRGR_CD_LOT_PROPRIET_GR_DIV,
                                   LOTA_DSC_LOTACAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO
                            FROM SAD_TB_PRGR_PROPRIET_GRUPO_DIV
                            INNER JOIN RH_CENTRAL_LOTACAO
                            ON PRGR_SG_SECAO_PROPRIET_GR_DIV = LOTA_SIGLA_SECAO
                            AND PRGR_CD_LOT_PROPRIET_GR_DIV = LOTA_COD_LOTACAO
                            WHERE PRGR_ID_GRUPO_DIVULGACAO = $id");
        return $stmt->fetchAll();
    }
    
//   public function getGrupobyId($id)
//    {
//        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//        $stmt = $db->query("SELECT GRDV_ID_GRUPO_DIVULGACAO,
//                                   GRDV_DS_GRUPO_DIVULGACAO,
//                                   GRDV_IC_ATIVO
//                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
//                            WHERE GRDV_ID_GRUPO_DIVULGACAO = $id");
//        return $stmt->fetch();
//    }
//    
   public function setNewProprietarioGrupo($data)
    {
        $tabelaGrupos = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
        $tabelaComponentes = new Application_Model_DbTable_SadTbCompComponenteGrupo();
        $tabelaProprietarioGrupo = new Application_Model_DbTable_SadTbPrgrProprietGrupoDiv();
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
               
                /* Verifica se o usuario incluiu uma Pessoa do TRF */
//                if (isset($data["pessoa_trf"]) && $data["pessoa_trf"] != NULL) {
//                    foreach ($data["pessoa_trf"] as $pessoa) {
//                        $trf_mat = explode("-", $pessoa);
//                        $dadosCompPessTrf['COMP_CD_MATRICULA_TRF'] = $trf_mat[0];
//                        $dadosCompPessTrf['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
//                        $dadosCompPessTrf['COMP_IC_ATIVO'] = "S";
//                        $rowCompPessTrf = $tabelaComponentes->createRow($dadosCompPessTrf);
//                        $rowCompPessTrf->save();
//                    }
//                }

                /* Verifica se o usuario incluiu uma Unidade Adiministrativa do TRF */
                if (isset($data["unidade_adm"]) && $data["unidade_adm"] != NULL) {
                    foreach ($data["unidade_adm"] as $pessoa) {
                        $unidade_adm_sg_cd = explode("-", $pessoa);
                        $dadosPropriUnidAdm['PRGR_ID_GRUPO_DIVULGACAO'] = $data["grupo"];
                        $dadosPropriUnidAdm['PRGR_SG_SECAO_PROPRIET_GR_DIV'] = $unidade_adm_sg_cd[0]; /* SIGLA */
                        $dadosPropriUnidAdm['PRGR_CD_LOT_PROPRIET_GR_DIV'] = $unidade_adm_sg_cd[1]; /* CODIGO */
                        $dadosPropriUnidAdm['PRGR_IC_ATIVO'] = "S";
                        $rowPropriGrupo = $tabelaProprietarioGrupo->createRow($dadosPropriUnidAdm);
                        $rowPropriGrupo->save();
                    }
                }
                $db->commit();
            } catch (Zend_Exception $e) {
                $db->rollBack();
                return $e->getMessage();
            }
    }

}