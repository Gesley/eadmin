<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Bd_Distribuicao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de persistencia de dados
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Sisad_Bd_Distribuicao {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Retorna os relatores do processo
     * 
     * @param
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getRelatoresProcesso($id_proc) {
        return $this->_db->fetchAll("SELECT 
                                        HDPA_CD_PROC_ADMINISTRATIVO
                                        ,HDPA_CD_SERVIDOR
                                        ,HDPA_CD_JUIZ
                                        ,HDPA_IC_FORMA_DISTRIBUICAO
                                        ,HDPA_CD_ORGAO_JULGADOR
                                    FROM 
                                        SAD_TB_HDPA_HIST_DISTRIBUICAO
                                    WHERE
                                        HDPA_CD_PROC_ADMINISTRATIVO = '$id_proc'
                                    ");
    }

    public function getSorteioComissao($idOrgao, $idProcesso, $matExcluido) {

        $matriculaExcluido = ($matExcluido != null && $matExcluido = !'') ? " AND A.CCPA_CD_SERVIDOR <> '$matExcluido' " : '';

        return $this->_db->fetchAll("SELECT P.PNAT_NO_PESSOA, 
                                   M.PMAT_CD_MATRICULA,
                                   M.PMAT_CD_UNIDADE_LOTACAO LOTA_COD_LOTACAO,
                                   M.PMAT_SG_SECSUBSEC_LOTACAO LOTA_SIGLA_SECAO
                              FROM SAD_TB_CCPA_CONT_DIST_COMISSAO A,
                                   OCS_TB_PMAT_MATRICULA M,
                                   OCS_TB_PNAT_PESSOA_NATURAL P
                             WHERE M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA 
                               $matriculaExcluido
                               AND M.PMAT_CD_MATRICULA = A.CCPA_CD_SERVIDOR
                               AND A.CCPA_IC_ATIVO = 'S'
                               AND A.CCPA_IC_DISTRIBUICAO = 'N'
                               AND PMAT_DT_FIM IS NULL
                               AND RH_RETORNA_INDISPONIVEL(A.CCPA_CD_SERVIDOR,SYSDATE) = 0
                               AND A.CCPA_CD_ORGAO_JULGADOR = '$idOrgao'
                               AND NVL(A.CCPA_QT_DEVOLVIDO_SERVIDOR,0) = (SELECT MAX(NVL(B.CCPA_QT_DEVOLVIDO_SERVIDOR,0))
                                                                            FROM SAD_TB_CCPA_CONT_DIST_COMISSAO B
                                                                           WHERE B.CCPA_CD_ORGAO_JULGADOR = A.CCPA_CD_ORGAO_JULGADOR
                                                                             AND B.CCPA_CD_SERVIDOR = A.CCPA_CD_SERVIDOR
                                                                             AND B.CCPA_IC_ATIVO = 'S')
                               AND A.CCPA_CD_SERVIDOR NOT IN (
                                   SELECT IMDI_CD_MATRICULA_SERVIDOR
                                   FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                   WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                                         AND IMDI_CD_COMISSAO = '$idOrgao')
                                ");
    }

    public function getSorteioDesembargadores($idOrgao, $idProcesso, $icPlenario, $matExcluido) {

        $matriculaExcluido = ($matExcluido != null && $matExcluido = !'') ? " AND A.CDPA_CD_JUIZ <> '$matExcluido' " : '';
        $icPlenario = ($icPlenario == 'S') ? 'AND CDPA_IC_PLENARIO = \'S\'' : '';
        return $this->_db->fetchAll("SELECT M.PMAT_CD_MATRICULA,
                                   P.PNAT_NO_PESSOA, 
                                   M.PMAT_CD_UNIDADE_LOTACAO LOTA_COD_LOTACAO,
                                   M.PMAT_SG_SECSUBSEC_LOTACAO LOTA_SIGLA_SECAO
                              FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM A,
                                   OCS_TB_PMAT_MATRICULA M,
                                   OCS_TB_PNAT_PESSOA_NATURAL P
                             WHERE M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA 
                                AND M.PMAT_CD_MATRICULA = A.CDPA_CD_JUIZ
                                AND A.CDPA_IC_ATIVO = 'S'
                                AND A.CDPA_IC_DISTRIBUICAO = 'N'
                                AND M.PMAT_DT_FIM IS NULL
                                AND RH_RETORNA_INDISPONIVEL(A.CDPA_CD_JUIZ,SYSDATE) = 0
                                AND A.CDPA_CD_ORGAO_JULGADOR = '$idOrgao'
                                $matriculaExcluido
                                $icPlenario
                                AND NVL(A.CDPA_QT_DEVOLVIDO_JUIZ,0) = (SELECT MAX(NVL(B.CDPA_QT_DEVOLVIDO_JUIZ,0))
                                                                        FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM B
                                                                        WHERE B.CDPA_CD_ORGAO_JULGADOR = A.CDPA_CD_ORGAO_JULGADOR
                                                                        AND B.CDPA_CD_JUIZ = A.CDPA_CD_JUIZ
                                                                        AND B.CDPA_IC_ATIVO = 'S')
                                AND CDPA_CD_JUIZ NOT IN (
                                                SELECT IMDI_CD_MATRICULA_JUIZ
                                                FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                                WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                                                    AND IMDI_CD_ORGAO_JULGADOR = '$idOrgao'
                                                )");
    }

    /**
     * Verifica se existe algum membro de comissao disponivel na distribuição de processos administrativos
     * 
     * @param int $idOrgao
     * @param int $idProcesso
     * @return boolean
     */
    public function isDisponivelDistribuicaoComissao($idOrgao, $idProcesso) {
        $array = $this->_db->fetchRow("SELECT COUNT(*) TOTAL
                                    FROM SAD_TB_CCPA_CONT_DIST_COMISSAO
                                    , OCS_TB_PMAT_MATRICULA
                                    WHERE PMAT_CD_MATRICULA = CCPA_CD_SERVIDOR
                                        AND PMAT_DT_FIM IS NULL 
                                        AND CCPA_CD_ORGAO_JULGADOR = '$idOrgao'                                                                             
                                        AND CCPA_IC_DISTRIBUICAO = 'N'
                                        AND CCPA_CD_SERVIDOR NOT IN (
                                            SELECT IMDI_CD_MATRICULA_SERVIDOR
                                            FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                            WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                                                AND IMDI_CD_COMISSAO = '$idOrgao'
                                        )");
        return $array['TOTAL'] > 0;
    }

    /**
     * Verifica se existem algum membro de orgão especial disponivél na distribuição de processos administrativos
     * 
     * @param int $idOrgao
     * @param char $icPlenario
     * @return boolean 
     */
    public function isDisponivelDistribuicaoEspecial($idOrgao, $idProcesso, $icPlenario) {

        $completa = ($icPlenario == 'S') ? 'AND CDPA_IC_PLENARIO = \'' . $icPlenario . '\'' : '';
        $array = $this->_db->fetchRow("SELECT COUNT(*) TOTAL 
                                        FROM SAD_TB_CDPA_CONT_DIST_PROC_ADM, OCS_TB_PMAT_MATRICULA
                                        WHERE PMAT_CD_MATRICULA = CDPA_CD_JUIZ
                                            AND PMAT_DT_FIM IS NULL
                                            AND CDPA_CD_ORGAO_JULGADOR = '$idOrgao'
                                            $completa
                                            AND CDPA_IC_DISTRIBUICAO = 'N'
                                            AND CDPA_IC_ATIVO = 'S'
                                            AND CDPA_CD_JUIZ NOT IN (
                                                SELECT IMDI_CD_MATRICULA_JUIZ
                                                FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                                                WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                                                    AND IMDI_CD_ORGAO_JULGADOR = '$idOrgao'
                                                )");
        return $array['TOTAL'] > 0;
    }

    /**
     * Seta todos os membros de orgão julgadores especiais para disponiveis na distribuição de processos administrativos
     * 
     * @param int $idOrgao
     * @param char $icPlenario 
     */
    public function setaDisponibilidadeDistEspecial($idOrgao, $idProcesso, $icPlenario) {
        $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
        if ($icPlenario == 'S') {
            $where = "CDPA_IC_PLENARIO = '$icPlenario' AND CDPA_CD_ORGAO_JULGADOR = '$idOrgao'";
        } else {
            $where = "CDPA_CD_ORGAO_JULGADOR = $idOrgao";
        }
        $where .= " AND CDPA_CD_JUIZ NOT IN (
                        SELECT IMDI_CD_MATRICULA_JUIZ
                        FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                        WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                              AND IMDI_CD_ORGAO_JULGADOR = '$idOrgao'
                    )";
        $data = array('CDPA_IC_DISTRIBUICAO' => 'N');

        return $sadTbCdpaContDistProcAdm->update($data, $where);
    }

    /**
     * Seta todos os membros de comissões julgadoras para disponiveis na distribuição de processos administrativos
     * 
     * @param int $idOrgao
     * @param int $idProcesso 
     */
    public function setaDisponibilidadeDistComissao($idOrgao, $idProcesso) {
        $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
        $where = "CCPA_CD_ORGAO_JULGADOR = '$idOrgao' 
                    AND CCPA_CD_SERVIDOR NOT IN (
                        SELECT IMDI_CD_MATRICULA_SERVIDOR
                        FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                        WHERE IMDI_ID_PROCESSO_DIGITAL = '$idProcesso'
                              AND IMDI_CD_COMISSAO = '$idOrgao'
                    )";
        $data = array('CCPA_IC_DISTRIBUICAO' => "N");
        return $sadTbCcpaContDistComissao->update($data, $where);
    }

    public function isImpedidoDistribuicaoEspecial($idProcesso, $idOrgao, $matriculaMembro) {
        $array = $this->_db->fetchRow('SELECT COUNT(*) COUNT
                            FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                            WHERE IMDI_ID_PROCESSO_DIGITAL   = ?
                                  AND IMDI_CD_ORGAO_JULGADOR = ?
                                  AND IMDI_CD_MATRICULA_JUIZ = ?'
                , array(
            $idProcesso
            , $idOrgao
            , $matriculaMembro));
        return $array['COUNT'] > 0;
    }

    public function isImpedidoDistribuicaoComissao($idProcesso, $idOrgao, $matriculaMembro) {
        $array = $this->_db->fetchRow("SELECT COUNT(*) COUNT
                            FROM SAD_TB_IMDI_IMPEDE_DISTRIBUI
                            WHERE IMDI_ID_PROCESSO_DIGITAL       = ?
                                  AND IMDI_CD_COMISSAO           = ?
                                  AND IMDI_CD_MATRICULA_SERVIDOR = ?"
                , array(
            $idProcesso
            , $idOrgao
            , $matriculaMembro));
        return $array['COUNT'] > 0;
    }

    public function isPromocaoDistribuicaoEspecial($idOrgao, $matriculaMembro) {
        $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
        $array = $sadTbCdpaContDistProcAdm->fetchRow('  CDPA_CD_ORGAO_JULGADOR = \'' . $idOrgao . '\'
                                                        AND CDPA_CD_JUIZ = \'' . $matriculaMembro . '\'
                                                        AND CDPA_IC_PLENARIO = \'S\'');
        return (is_null($array)) ? false : true;
    }

    /**
     * Recebe como parametros de entrada
     * 
     * @param string  $tipo |todos, julgados, nao_julgados
     * * @return array
     */
    public function dadosUltimaDistribuicaoProcesso($tipo = 'todos', $order = '', $idProcessoAdministrativo = null, $dhDistribuicao = null) {
        //Tipo de distribuiçao
        $arrayTipo = array(
            'julgados' => 'AND HDPA.HDPA_DT_JULGAMENTO IS NOT NULL'
            , 'nao_julgados' => 'AND HDPA.HDPA_DT_JULGAMENTO IS NULL'
            , 'todos' => ''
        );

        $order = ($order == '') ? 'HDPA_TS_DISTRIBUICAO DESC' : '';
        $idProcessoAdministrativo = ($idProcessoAdministrativo == null || $idProcessoAdministrativo == '') ? '' : "AND HDPA_CD_PROC_ADMINISTRATIVO = '$idProcessoAdministrativo'";
        $dhDistribuicao = ($dhDistribuicao == null || $dhDistribuicao == '') ? '' : "AND TO_CHAR(HDPA_TS_DISTRIBUICAO,'DD/MM/YYYY hh24:mi:ss') = '$dhDistribuicao'";

        return $this->_db->fetchAll("
            SELECT  DOCM_NR_DOCUMENTO
                    ,HDPA_ID_DISTRIBUICAO
                    ,HDPA_CD_PROC_ADMINISTRATIVO
                    ,TO_CHAR(HDPA_TS_DISTRIBUICAO,'DD/MM/YYYY hh24:mi:ss') HDPA_TS_DISTRIBUICAO
                    ,HDPA_DT_JULGAMENTO
                    ,HDPA_DS_RESUMO_DECISAO
                    ,HDPA_DT_PUBLIC_JULGAMENTO_DJ
                    ,HDPA_DT_PUBLIC_JULGAMENTO_BS
                    ,ORGJ_CD_ORGAO_JULGADOR
                    ,ORGJ_NM_ORGAO_JULGADOR
                    ,ORGJ_DS_ORGAO_JULGADOR
                    ,PMAT_CD_MATRICULA
                    ,PNAT_NO_PESSOA
            FROM    SAD_TB_HDPA_HIST_DISTRIBUICAO HDPA
                    ,SAD_TB_ORGJ_ORGAO_JULGADOR ORGJ
                    ,OCS_TB_PMAT_MATRICULA PMAT
                    ,OCS_TB_PNAT_PESSOA_NATURAL PNAT
                    ,SAD_TB_PRDI_PROCESSO_DIGITAL
                    ,SAD_TB_DCPR_DOCUMENTO_PROCESSO
                    ,SAD_TB_DOCM_DOCUMENTO
            WHERE HDPA.HDPA_ID_DISTRIBUICAO IN (SELECT MAX(HDPA_ID_DISTRIBUICAO)
                                                FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                                                GROUP BY HDPA_CD_PROC_ADMINISTRATIVO)
                AND HDPA.HDPA_CD_ORGAO_JULGADOR = ORGJ.ORGJ_CD_ORGAO_JULGADOR
                AND (HDPA.HDPA_CD_JUIZ = PMAT.PMAT_CD_MATRICULA
                OR HDPA.HDPA_CD_SERVIDOR = PMAT.PMAT_CD_MATRICULA)
                AND PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                AND HDPA_CD_PROC_ADMINISTRATIVO = PRDI_ID_PROCESSO_DIGITAL
                AND DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                AND DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                {$arrayTipo[$tipo]}
                $idProcessoAdministrativo
                $dhDistribuicao
            ORDER BY $order
            ");
    }

    public function trataDevolucaoProcessoEspecial($idOrgao, $matricula) {
        $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();

        $dataDevolucao = array();
        $dataDevolucao['CDPA_IC_DISTRIBUICAO'] = 'N';
        $dataDevolucao['CDPA_QT_DEVOLVIDO_JUIZ'] = new Zend_Db_Expr('NVL(CDPA_QT_DEVOLVIDO_JUIZ,0) + 1');
        $dataDevolucao['CDPA_QT_TOTAL_DEVOLVIDO_ORGAO'] = new Zend_Db_Expr('NVL(CDPA_QT_TOTAL_DEVOLVIDO_ORGAO,0) + 1');

        return $sadTbCdpaContDistProcAdm
                        ->find($idOrgao, $matricula)
                        ->current()
                        ->setFromArray($dataDevolucao)
                        ->save();
    }

    public function trataDevolucaoProcessoComissao($idOrgao, $matricula) {
        $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
        $dataDevolucao = array();
        $dataDevolucao['CCPA_IC_DISTRIBUICAO'] = 'N';
        $dataDevolucao['CCPA_QT_DEVOLVIDO_SERVIDOR'] = new Zend_Db_Expr('NVL(CCPA_QT_DEVOLVIDO_SERVIDOR,0) + 1');
        $dataDevolucao['CCPA_QT_TOTAL_DEVOLVIDO_ORGAO'] = new Zend_Db_Expr('NVL(CCPA_QT_TOTAL_DEVOLVIDO_ORGAO,0) + 1');

        return $sadTbCcpaContDistComissao
                        ->find($idOrgao, $matricula)
                        ->current()
                        ->setFromArray($dataDevolucao)
                        ->save();
    }

    public function inseriStatusDistEspecial($idOrgao, $matricula) {
        $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
        $dataStatus = array();
        $dataStatus['CDPA_IC_DISTRIBUICAO'] = 'S';
        return $sadTbCdpaContDistProcAdm->find($idOrgao, $matricula)
                        ->current()
                        ->setFromArray($dataStatus)
                        ->save();
    }

    public function inseriStatusDistComissao($idOrgao, $matricula) {
        $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
        $dataStatus = array();
        $dataStatus['CCPA_IC_DISTRIBUICAO'] = 'S';
        return $sadTbCcpaContDistComissao->find($idOrgao, $matricula)
                        ->current()
                        ->setFromArray($dataStatus)
                        ->save();
    }

    public function setHistoricoDistribuicao(array $dadosHistorico) {
        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        return $rowSadTbHdpaHistDistribuicao = $sadTbHdpaHistDistribuicao->createRow($dadosHistorico)->save();
    }

    public function montaDadosAtaDistribuicao($idProcesso, $matricula, $idOrgao, $formaDistribuicao) {

        return $this->_db->fetchAll('
            SELECT 
                PNAT_NO_PESSOA
                , ( SELECT COUNT(*)
                    FROM SAD_TB_HDPA_HIST_DISTRIBUICAO
                    WHERE HDPA_CD_PROC_ADMINISTRATIVO = ?
                        AND HDPA_IC_FORMA_DISTRIBUICAO = ?) AS QTD_DISTRIBUICAO
                , ( SELECT ORGJ_NM_ORGAO_JULGADOR
                    FROM SAD_TB_ORGJ_ORGAO_JULGADOR
                    WHERE ORGJ_CD_ORGAO_JULGADOR = ?) AS ORGJ_NM_ORGAO_JULGADOR
            FROM 
                OCS_TB_PMAT_MATRICULA PMAT
                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                    ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                    AND PMAT_DT_FIM IS NULL
                    AND PMAT_CD_MATRICULA = ?', array($idProcesso, $formaDistribuicao, $idOrgao, $matricula));
    }

}