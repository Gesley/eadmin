<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SadTbCompComponenteGrupo extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_COMP_COMPONENTE_GRUPO';
    protected $_primary = 'COMP_ID_COMPONENTE';
    protected $_sequence = 'SAD_SQ_COMP';


    public function getComponentesGrupo($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COMP_ID_COMPONENTE,
                                   COMP_ID_GRUPO_DIVULGACAO,
                                   PJUR.PJUR_NO_RAZAO_SOCIAL||PNAT.PNAT_NO_PESSOA||PNAT2.PNAT_NO_PESSOA||(LOTA_DSC_LOTACAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO)) AS COMPONENTE
                            FROM SAD_TB_COMP_COMPONENTE_GRUPO CMP
                            LEFT JOIN OCS_TB_PJUR_PESSOA_JURIDICA PJUR
                            ON CMP.COMP_ID_PESSOA_JURIDICA = PJUR.PJUR_ID_PESSOA
                            LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                            ON CMP.COMP_ID_PESSOA_FISICA = PNAT.PNAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PMAT_MATRICULA PMAT
                            ON CMP.COMP_CD_MATRICULA_TRF = PMAT.PMAT_CD_MATRICULA
                            LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT2
                            ON PNAT2.PNAT_ID_PESSOA = PMAT.PMAT_ID_PESSOA
                            LEFT JOIN RH_CENTRAL_LOTACAO
                            ON CMP.COMP_SG_SECAO = LOTA_SIGLA_SECAO
                            AND CMP.COMP_CD_LOTACAO = LOTA_COD_LOTACAO
                            WHERE CMP.COMP_ID_GRUPO_DIVULGACAO = $id
                            AND COMP_IC_ATIVO = 'S'
                            ORDER BY COMPONENTE ASC");
        return $stmt->fetchAll();
    }
    
    public function setNewComponenteGrupo($data) 
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $comp_id_grupo_divulgacao = $data['GRDV_ID_GRUPO_DIVULGACAO'];
            /* Verifica se o usuario incluiu uma Pessoa do TRF */
            if (isset($data["pessoa_trf"]) && $data["pessoa_trf"] != NULL) {
                foreach ($data["pessoa_trf"] as $pessoa) {
                    $trf_mat = explode("-", $pessoa);
                    $dadosCompPessTrf['COMP_CD_MATRICULA_TRF'] = $trf_mat[0];
                    $dadosCompPessTrf['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                    $dadosCompPessTrf['COMP_IC_ATIVO'] = "S";
                    $rowCompPessTrf = $this->createRow($dadosCompPessTrf);
                    $rowCompPessTrf->save();
                }
            }

            /* Verifica se o usuario incluiu uma Pessoa Externa (Fisica) */
            if (isset($data["pess_ext"]) && $data["pess_ext"] != NULL) {
                foreach ($data["pess_ext"] as $pess_ext_id) {
                    $dadosCompPessFisica['COMP_ID_PESSOA_FISICA'] = $pess_ext_id;
                    $dadosCompPessFisica['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                    $dadosCompPessFisica['COMP_IC_ATIVO'] = "S";
                    $rowCompPessFisica = $this->createRow($dadosCompPessFisica);
                    $rowCompPessFisica->save();
                }
            }

            /* Verifica se o usuario incluiu uma Pessoa Juridica */
            if (isset($data["pess_jur"]) && $data["pess_jur"] != NULL) {
                foreach ($data["pess_jur"] as $pess_jur_id) {
                    $dadosCompPessJur['COMP_ID_PESSOA_JURIDICA'] = $pess_jur_id;
                    $dadosCompPessJur['COMP_ID_GRUPO_DIVULGACAO'] = $comp_id_grupo_divulgacao;
                    $dadosCompPessJur['COMP_IC_ATIVO'] = "S";
                    $rowCompPessJur = $this->createRow($dadosCompPessJur);
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
                    $rowCompUnidAdm = $this->createRow($dadosCompUnidAdm);
                    $rowCompUnidAdm->save();
                }
            }
            $db->commit();
        } catch (Zend_Exception $e) {
            $db->rollBack();
            Zend_Debug::dump($e->getMessage());
            exit;
            return $e->getMessage();
        }
    }

}