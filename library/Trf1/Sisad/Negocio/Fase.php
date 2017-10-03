<?php

/**
 * 
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Fase
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre FASE de documentos dentro do Sisad
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
class Trf1_Sisad_Negocio_Fase {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    private $_db;

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
     * Insere uma nova fase na MOVIMENTAÇÂO especificada
     * 
     * @param	array	$arrayMovimentacao
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public static function lancaFase(array $arrayMovimentacao) {

        /*
         * Formato do array a ser utilizado
         * 
         * $arrayMovimentacao = array(
          'MOFA_ID_MOVIMENTACAO' => ''
          , 'MOFA_CD_MATRICULA'    => ''
          , 'MOFA_DH_FASE'         => ''
          , 'MOFA_ID_FASE'         => ''
          , 'MOFA_DS_COMPLEMENTO'  => ''); */

        /* Cria fase */
        $sadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
        $sadTbMofaMoviFase->createRow($arrayMovimentacao)
                ->save();
    }

    /**
     * Retorna um array associando o tipo de juntada 
     * 
     * @param	int	$tipoRelacao
     * @param	int	$tp_vinculo
     * @example chamar da seguinte maneira: Trf1_Sisad_Negocio_Fase::getFaseJuntada($tipoRelacao, $tp_vinculo)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public static function getFaseJuntada($tipoRelacao, $tp_vinculo) {
        /* Valor de $tp_vinculo
         * 
         * 1 - ANEXAR
         * 2 - APENSAR
         * 3 - VINCULAR
         */
        $array = array('documentoadocumento' => array(
                Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR => Trf1_Sisad_Definicoes::FASE_ANEXAR_DOCUMENTO_DOCUMENTO
                //, Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR => Trf1_Sisad_Definicoes::FASE_VINCULA_DOCUMENTO_DOCUMENTO
            )
            , 'documentoaprocesso' => array(
                Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR => Trf1_Sisad_Definicoes::FASE_ADICAO_DOCUMENTO_PROCESSO
                //, Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR => Trf1_Sisad_Definicoes::FASE_VINCULA_DOCUMENTO_PROCESSO
            )
            , 'processoaprocesso' => array(
                Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR => Trf1_Sisad_Definicoes::FASE_ANEXAR_PROCESSO_PROCESSO
                , Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR => Trf1_Sisad_Definicoes::FASE_APENSAR_PROCESSO_PROCESSO
                , Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR => Trf1_Sisad_Definicoes::FASE_VINCULA_PROCESSO_PROCESSO
        ));

        return empty($tp_vinculo) ? $array[$tipoRelacao] : $array[$tipoRelacao][$tp_vinculo];
    }

    public static function getFaseDistribuicao($tipoDistribuicao) {
        /* Valor de $tipoDistribuicao
         * 
         * DA - Distribuição Automática
         * RA - Redistribuição Automática
         * DM - Distribuição Manual
         */
        $array = array(
            'DA' => array('id' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO, 'descricao' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO_DESCRICAO)
            , 'RA' => array('id' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO, 'descricao' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO_DESCRICAO)
            , 'DM' => array('id' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_MANUAL_PROCESSO, 'descricao' => Trf1_Sisad_Definicoes::FASE_DISTRIBUICAO_MANUAL_PROCESSO_DESCRICAO)
        );

        return empty($tipoDistribuicao) ? $array : $array[$tipoDistribuicao];
    }

    /**
     * Verifica se existe uma fase especifica depois de uma data e hora em uma
     * movimentação especifica
     * @param type $fase
     * @param type $idMovimentacao
     * @param type $dataHora
     */
    public function isFaseDepoisData($fase, $idMovimentacao, $dataHora) {
        $sql = "
            SELECT COUNT(*) QTD
            FROM SAD_TB_MOFA_MOVI_FASE
            WHERE
                MOFA_ID_MOVIMENTACAO = $idMovimentacao AND
                MOFA_ID_FASE = $fase AND
                MOFA_DH_FASE > TO_DATE('$dataHora','DD/MM/YYYY HH24:MI:SS')
            ";
        $resultado = $this->_db->fetchRow($sql);
        return ($resultado['QTD'] > 0);
    }

    /**
     * Busca todas as Assinaturas de um documento
     * @param type $idDocumento
     * @param type $idFase
     * @return type
     */
    public function getAssinaturasDocumento($idDocumento, $idFase) {

        $stmt = $this->_db->query("          
                SELECT TO_CHAR(MOFA_DH_FASE, 'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE , PMAT_CD_MATRICULA, PNAT_NO_PESSOA
                FROM 
                SAD_TB_MOFA_MOVI_FASE FASE,
                OCS_TB_PMAT_MATRICULA MAT,
                OCS_TB_PESS_PESSOA PESS,
                SAD_TB_MODO_MOVI_DOCUMENTO MODO,
                OCS_TB_PNAT_PESSOA_NATURAL PNAT
                WHERE 
                FASE.MOFA_ID_FASE = $idFase AND 
                FASE.MOFA_CD_MATRICULA = MAT.PMAT_CD_MATRICULA AND
                MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                PESS.PESS_ID_PESSOA = PNAT.PNAT_ID_PESSOA AND
                FASE.MOFA_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO AND
                MODO.MODO_ID_DOCUMENTO = $idDocumento
            ");

        return $stmt->fetchAll();
    }

}