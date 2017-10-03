<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Bd_Movimentacao
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
class Trf1_Sisad_Bd_Movimentacao {

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
            $idDocmDocumento,
            array $dataMoviMovimentacao,
            array $dataModeMoviDestinatario,
            array $dataMofaMoviFase,
            array $dataModpDestinoPessoa,
            $nrDocsRed = null,
            $datahora = null,
            $autoCommit = true) {
        
        $arrayRetorno = array();
        if($autoCommit){
            $this->_db->beginTransaction();
        }
        /* ---------------------------------------------------------------------------------------- */
        /* primeira tabela */
        unset($dataMoviMovimentacao['MOVI_ID_MOVIMENTACAO']);
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $dataMoviMovimentacao['MOVI_DH_ENCAMINHAMENTO'] = $datahora;
        $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
        $idMoviMovimentacao = $rowMoviMovimentacao->save();

        $arrayRetorno['MOVI_ID_MOVIMENTACAO'] = $idMoviMovimentacao;

        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* segunda tabela */
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        $dataModoMoviDocumento['MODO_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $dataModoMoviDocumento['MODO_ID_DOCUMENTO'] = $idDocmDocumento;
        $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
        $rowModoMoviDocumento->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* terceira tabela */
        unset($dataModeMoviDestinatario['MODE_DH_RECEBIMENTO']);
        unset($dataModeMoviDestinatario['MODE_CD_MATR_RECEBEDOR']);
        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
        $dataModeMoviDestinatario['MODE_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
        $rowModeMoviDestinatario->save();
        /* ---------------------------------------------------------------------------------------- */

        /* ---------------------------------------------------------------------------------------- */
        /* quarta tabela */
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $dataMofaMoviFase['MOFA_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $dataMofaMoviFase['MOFA_DH_FASE'] = $datahora;
        $dataMofaMoviFase['MOFA_DS_COMPLEMENTO'] = new Zend_Db_Expr("'" . $dataMofaMoviFase['MOFA_DS_COMPLEMENTO'] . "'");
        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
        $arrayFase = $rowMofaMoviFase->save();

        $arrayRetorno = array_merge($arrayRetorno, $arrayFase);

        /* quinta tabela */
        if (array_key_exists('MODP_CD_MAT_PESSOA_DESTINO', $dataModpDestinoPessoa) && isset($dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'])) {
            unset($dataModpDestinoPessoa['MODP_DH_RECEBIMENTO']);
            unset($dataModpDestinoPessoa['MODP_IC_RESPONSAVEL']);
            $SadTbModpDestinoPessoa = new Application_Model_DbTable_SadTbModpDestinoPessoa();
            $dataModpDestinoPessoa['MODP_ID_MOVIMENTACAO'] = $idMoviMovimentacao;

            $dataModpDestinoPessoa['MODP_SG_SECAO_UNID_DESTINO'] = $dataModeMoviDestinatario['MODE_SG_SECAO_UNID_DESTINO'];
            $dataModpDestinoPessoa['MODP_CD_SECAO_UNID_DESTINO'] = $dataModeMoviDestinatario['MODE_CD_SECAO_UNID_DESTINO'];
            $rowModpDestinoPessoa = $SadTbModpDestinoPessoa->createRow($dataModpDestinoPessoa);
            $rowModpDestinoPessoa->save();
        }
        /* ---------------------------------------------------------------------------------------- */
        $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
        $anexAnexo['ANEX_DH_FASE'] = $datahora;
        $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $anexAnexo['ANEX_NM_ANEXO'] = 'Ata de Distribuição';
        if ($nrDocsRed) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed as $value) {
                $anexAnexo['ANEX_NR_DOCUMENTO_INTERNO'] = $value;
                $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                $rowAnexAnexo->save();
            }
        }

        if ($autoCommit) {
            $this->_db->commit();
        }

        return $arrayRetorno;
    }

}