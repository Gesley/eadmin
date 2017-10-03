<?php

/**
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_Movimentacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad - Movimentação de documentos dentro do Sisad
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
class Trf1_Sisad_Negocio_Movimentacao {

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
     * Recebe como parametros de entrada
     * 
     * 
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * @param type $idDocmDocumento
     * @param array $dataMoviMovimentacao ('MOVI_SG_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_SECAO_UNID_ORIGEM'=>'', 'MOVI_CD_MATR_ENCAMINHADOR'=>'', 'MOVI_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataModeMoviDestinatario ('MODE_SG_SECAO_UNID_DESTINO'=>'', 'MODE_CD_SECAO_UNID_DESTINO'=>'', 'MODE_IC_RESPONSAVEL'=>'', 'MODE_ID_CAIXA_ENTRADA'=>'')
     * @param array $dataMofaMoviFase ('MOFA_ID_FASE'=>'', 'MOFA_CD_MATRICULA'=>'', 'MOFA_DS_COMPLEMENTO'=>'')
     * @param array $dataModpDestinoPessoa ('MODP_CD_MAT_PESSOA_DESTINO'=>'');
     * @param String $tipoRetorno tipo do retorno da função;

     * * @return String
     */
    public function encaminhaDocumento(
    $idDocmDocumento, array $dataMoviMovimentacao, array $dataModeMoviDestinatario, array $dataMofaMoviFase, array $dataModpDestinoPessoa, $nrDocsRed = null, $datahora = null, $autoCommit = true) {
        Zend_Debug::dump($idDocmDocumento);
        Zend_Debug::dump($dataMoviMovimentacao);
        Zend_Debug::dump($dataModeMoviDestinatario);
        Zend_Debug::dump($dataModpDestinoPessoa);
        Zend_Debug::dump($nrDocsRed);
        Zend_Debug::dump($datahora);
        Zend_Debug::dump($autoCommit);
        
        /**
         * Encaminha Documento
         * Com ou sem troca de nível.
         */
        if ($autoCommit) {
            $this->_db->beginTransaction();
        }
        try {
            if ($datahora == null) {
                $Dual = new Application_Model_DbTable_Dual();
                $datahora = $Dual->sysdate();
            }
            if (empty($dataMoviMovimentacao['MOVI_SG_SECAO_UNID_ORIGEM'])) {
                $e = new Exception('O valor de sigla de seção de origem não pode ser vazio', 1, null);
                throw $e;
            }
            if (empty($dataMoviMovimentacao['MOVI_CD_SECAO_UNID_ORIGEM'])) {
                $e = new Exception('O valor de codigo de lotação de origem não pode ser vazio', 1, null);
                throw $e;
            }

            $bd_Movimentacao = new Trf1_Sisad_Bd_Movimentacao();
            $arrayRetorno = $bd_Movimentacao->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa, $nrDocsRed, $datahora, false);
            if ($autoCommit) {
                $this->_db->commit();
            }
            return $arrayRetorno;
        } catch (Exception $exc) {
            if ($autoCommit) {
                $this->_db->rollBack();
            }
            throw $exc;
        }
    }

}