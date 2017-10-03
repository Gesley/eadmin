<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SadTbGrdvGrupoDivulgacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_GRDV_GRUPO_DIVULGACAO';
    protected $_primary = 'GRDV_ID_GRUPO_DIVULGACAO';
    protected $_sequence = 'SAD_SQ_GRDV';


   public function getGrupos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT GRDV_ID_GRUPO_DIVULGACAO,
                                   GRDV_DS_GRUPO_DIVULGACAO,
                                   GRDV_IC_ATIVO
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE GRDV_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }
    
   public function getGrupobyId($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT GRDV_ID_GRUPO_DIVULGACAO,
                                   GRDV_DS_GRUPO_DIVULGACAO,
                                   GRDV_IC_ATIVO
                            FROM SAD_TB_GRDV_GRUPO_DIVULGACAO
                            WHERE GRDV_ID_GRUPO_DIVULGACAO = $id
                            AND GRDV_IC_ATIVO = 'S'");
        return $stmt->fetch();
    }
    
   public function setNewGrupo($data)
    {
        $tabelaGrupos = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
        $tabelaComponentes = new Application_Model_DbTable_SadTbCompComponenteGrupo();
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
                $dadosGrupo['GRDV_DS_GRUPO_DIVULGACAO'] = strtoupper($data['GRDV_DS_GRUPO_DIVULGACAO']);
                $dadosGrupo['GRDV_IC_ATIVO'] = "S";
                $rowGrupo = $tabelaGrupos->createRow($dadosGrupo);
                $comp_id_grupo_divulgacao = $rowGrupo->save();
                /* Verifica se o usuario incluiu uma Pessoa do TRF */
                if (isset($data["pessoa_trf"]) && $data["pessoa_trf"] != NULL) {
                    foreach ($data["pessoa_trf"] as $pessoa) {
                        $trf_mat = explode("-", $pessoa);
                        $dadosCompPessTrf['COMP_CD_MATRICULA_TRF'] = $trf_mat[0];
                        $dadosCompPessTrf['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                        $dadosCompPessTrf['COMP_IC_ATIVO'] = "S";
                        $rowCompPessTrf = $tabelaComponentes->createRow($dadosCompPessTrf);
                        $rowCompPessTrf->save();
                    }
                }

                /* Verifica se o usuario incluiu uma Pessoa Externa (Fisica) */
                if (isset($data["pess_ext"]) && $data["pess_ext"] != NULL) {
                    foreach ($data["pess_ext"] as $pess_ext_id) {
                        $dadosCompPessFisica['COMP_ID_PESSOA_FISICA'] = $pess_ext_id;
                        $dadosCompPessFisica['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                        $dadosCompPessFisica['COMP_IC_ATIVO'] = "S";
                        $rowCompPessFisica = $tabelaComponentes->createRow($dadosCompPessFisica);
                        $rowCompPessFisica->save();
                    }
                }

                /* Verifica se o usuario incluiu uma Pessoa Juridica */
                if (isset($data["pess_jur"]) && $data["pess_jur"] != NULL) {
                    foreach ($data["pess_jur"] as $pess_jur_id) {
                        $dadosCompPessJur['COMP_ID_PESSOA_JURIDICA'] = $pess_jur_id;
                        $dadosCompPessJur['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                        $dadosCompPessJur['COMP_IC_ATIVO'] = "S";
                        $rowCompPessJur = $tabelaComponentes->createRow($dadosCompPessJur);
                        $rowCompPessJur->save();
                    }
                }

                /* Verifica se o usuario incluiu uma Unidade Adiministrativa do TRF */
                if (isset($data["unidade_adm"]) && $data["unidade_adm"] != NULL) {
                    foreach ($data["unidade_adm"] as $pessoa) {
                        $unidade_adm_sg_cd = explode("-", $pessoa);
                        $dadosCompUnidAdm['COMP_SG_SECAO'] = $unidade_adm_sg_cd[0]; /* SIGLA */
                        $dadosCompUnidAdm['COMP_CD_LOTACAO'] = $unidade_adm_sg_cd[1]; /* CODIGO */
                        $dadosCompUnidAdm['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                        $dadosCompUnidAdm['COMP_IC_ATIVO'] = "S";
                        $rowCompUnidAdm = $tabelaComponentes->createRow($dadosCompUnidAdm);
                        $rowCompUnidAdm->save();
                    }
                }
                $db->commit();
            } catch (Zend_Exception $e) {
                Zend_Debug::dump($e->getMessage());
                exit;
                $db->rollBack();
                return $e->getMessage();
            }
    }

}