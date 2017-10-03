<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_ParteVistas
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad
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
class Trf1_Sisad_Negocio_ParteVistas {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     *
     * @var Zend_Session_Namespace 
     */
    private $_userNs;

    /**
     *
     * @var String 
     */
    private $_dateTime;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');

        $zend_date = new Zend_Date(null, 'dd/MM/YY HH:mm:ss');
        $this->_dateTime = $zend_date->get(Zend_Date::DATETIME);
    }

    /**
     * Valida se o usuário solicitado pode visualizar o documento especificado.
     * 
     * @param	array      $documento	
     * @param	String     $mat_usuario	
     * @param	array      $array_vista	
     * @return	array('sigilo' => 'S' ou 'N', 'tem_vista' => 'S' ou 'N')
     * @author	Leidison Siqueira Barbosa
     * @tutorial se não for passado um array de vistas a função busca elas 
     * @deprecated since version 11300
     * de acordo com o documento ou processo
     */
    public function statusSigiloVista($documento, $matricula, $array_vista = null) {
        $validaExibicaoDocumento = array();
        $validaExibicaoDocumento['sigiloso'] = 'N';
        //trata a coluna de confidencialidade caso venha como DOCM_ID_CONFIDENCIALIDADE passa para CONF_ID_CONFIDENCIALIDADE
        //e se vier CONF_ID_CONFIDENCIALIDADE continua sendo ela mesmo
        $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);

        if (!is_null($array_vista)) {
            $validaExibicaoDocumento['tem_vista'] = $this->validaParteVista($matricula, $array_vista);
        } else {
            if ($documento['DTPD_NO_TIPO'] == Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_PROCESSO_DESCRICAO) {

                $validaExibicaoDocumento['tem_vista'] = $this->validaParteVista($matricula, null, $documento['PRDI_ID_PROCESSO_DIGITAL'], $documento['DTPD_NO_TIPO']);
            } elseif ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                $validaExibicaoDocumento['tem_vista'] = $this->validaParteVista($matricula, null, $documento['PRDI_ID_PROCESSO_DIGITAL'], $documento['DTPD_NO_TIPO']);
            } else {
                $validaExibicaoDocumento['tem_vista'] = $this->validaParteVista($matricula, null, $documento['DOCM_ID_DOCUMENTO'], $documento['DTPD_NO_TIPO']);
            }
        }

        /* VERIFICA SE DOCUMENTO É SIGILOSO */
        if (in_array($documento['CONF_ID_CONFIDENCIALIDADE'], array(
                    Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_AS_PARTES
                    , Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_AS_PARTES_SEGREDO_JUSTICA
                    , Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_AO_SUBGRUPO_SIGILOSO
                    , Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_CORREGEDORIA))) {
            $validaExibicaoDocumento['sigiloso'] = 'S';

            if ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_CORREGEDORIA) {
                $rn_Permissao = new Trf1_Guardiao_Negocio_Permissao();
                $temPermissao = $rn_Permissao->hasPermissaoCorregedoria($matricula);
            }

            if (($validaExibicaoDocumento['tem_vista']) || (!empty($temPermissao) && $temPermissao)) {
                //se o usuário puder ler o documento ou seja não sigiloso ao usuário
                $validaExibicaoDocumento['sigiloso'] = 'N';
            } else {
                //se o usuário não puder ler ou seja sigiloso ao usuário
                $validaExibicaoDocumento['sigiloso'] = 'S';
            }
        }

        return $validaExibicaoDocumento;
    }

    /**
     * Verifica se a matricula passada tem parte ou vista para o documento solicitado
     * 
     * @param	string     $matricula	
     * @param	array      $arrayParteVista	
     * @param	int        $idDocumento	
     * @param	int        $tipo
     * @return	boolean
     * @author	Desconhecido
     * @tutorial se não for passado um array de partes a função busca elas 
     * de acordo com o documento ou processo e o tipo de parte passado como parametro
     */
    public function validaParteVista($matricula
    , array $arrayParteVista = null
    , $idDocumento = null
    , $tipoDocumento = null
    , $tipoParte = Trf1_Sisad_Definicoes::PARTE_VISTA) {

        if (!is_array($arrayParteVista)) {
            $arrayParteVista = $this->getPartesVistas($idDocumento, $tipoDocumento, $tipoParte);
        }

        $rn_CaixaUnidade = new Trf1_Sisad_Negocio_CaixaUnidade();

        $caixasUnidade = $rn_CaixaUnidade->getCaixasPorResponsavel($matricula);

        //verifico se a pessoa que esta cadastrando é parte/interessado
        foreach ($arrayParteVista as $parteVista) {
            foreach ($caixasUnidade as $caixa) {
                $unidade = $caixa['LOTA_SIGLA_SECAO'] . '-' . $caixa['LOTA_COD_LOTACAO'];
                if (strcmp($matricula, $parteVista['USUARIO']) == '0' || strcmp($unidade, $parteVista['VALUE']) == '0') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retorna todas as partes e vistas que o documento possui
     * @param type $id
     * @param type $tipoDocumento
     * @param type $tipoParte
     * @return type
     */
    public function getPartesVistas($id, $tipoDocumento, $tipoParte = Trf1_Sisad_Definicoes::PARTE_VISTA) {
        $and = "";

        if ($tipoDocumento == Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_PROCESSO_DESCRICAO) {
            $and = " AND PAPD_ID_PROCESSO_DIGITAL = $id ";
        } elseif ($tipoDocumento == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $and = " AND PAPD_ID_PROCESSO_DIGITAL = $id ";
        } else {
            $and = " AND PAPD_ID_DOCUMENTO = $id ";
        }

        $query = "
SELECT PNAT_NO_PESSOA||' - '||PAPD_CD_MATRICULA_INTERESSADO NOME,
        PAPD_ID_DOCUMENTO ID_DOC, 
        PAPD_ID_PESSOA_FISICA ID, 
        PAPD_CD_MATRICULA_INTERESSADO||'-'||PAPD_ID_PESSOA_FISICA VALUE,
        'partes_pessoa_trf[]' TIPO,
        1 ORDEM, -- nao alterar esse valor
        PAPD_CD_MATRICULA_INTERESSADO USUARIO,
        TPDP_ID_PARTE TIPO_PARTE
FROM OCS_TB_PNAT_PESSOA_NATURAL,
        SAD_TB_PAPD_PARTE_PROC_DOC,
        OCS_TB_PMAT_MATRICULA,
        SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
WHERE PMAT_CD_MATRICULA = PAPD_CD_MATRICULA_INTERESSADO
        AND PAPD_ID_PESSOA_FISICA = PNAT_ID_PESSOA 
        AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
        AND TPDP_ID_PARTE = $tipoParte
        AND PAPD_DH_EXCLUSAO IS NULL  
        $and
UNION
SELECT  PNAT_NO_PESSOA NOME,
        PAPD_ID_DOCUMENTO ID_DOC, 
        PAPD_ID_PESSOA_FISICA ID, 
        PAPD_ID_PESSOA_FISICA||'' VALUE,
        'partes_pess_ext[]' TIPO,
        2 ORDEM, -- nao alterar esse valor
        '' USUARIO,
        TPDP_ID_PARTE TIPO_PARTE
FROM OCS_TB_PNAT_PESSOA_NATURAL,
        SAD_TB_PAPD_PARTE_PROC_DOC,
        OCS_TB_PMAT_MATRICULA,
        SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
WHERE PMAT_CD_MATRICULA(+) = PAPD_CD_MATRICULA_INTERESSADO
        AND PAPD_ID_PESSOA_FISICA = PNAT_ID_PESSOA 
        AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
        AND TPDP_ID_PARTE = $tipoParte
        AND PAPD_DH_EXCLUSAO IS NULL
        $and
        AND PAPD_ID_PESSOA_FISICA NOT IN (SELECT PAPD_ID_PESSOA_FISICA
                                            FROM SAD_TB_PAPD_PARTE_PROC_DOC P
                                            WHERE P.PAPD_ID_PESSOA_FISICA = PAPD_ID_PESSOA_FISICA
                                            AND PAPD_CD_MATRICULA_INTERESSADO IS NOT NULL
                                            $and)
UNION
SELECT PJUR_NO_RAZAO_SOCIAL NOME,
        PAPD_ID_DOCUMENTO ID_DOC,
        PAPD_ID_PESSOA_JURIDICA ID,
        PAPD_ID_PESSOA_JURIDICA||'' VALUE,
        'partes_pess_jur[]' TIPO,
        3 ORDEM, -- nao alterar esse valor
        '' USUARIO,
        TPDP_ID_PARTE TIPO_PARTE
FROM OCS_TB_PJUR_PESSOA_JURIDICA,
        SAD_TB_PAPD_PARTE_PROC_DOC,
        SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
WHERE PAPD_ID_PESSOA_JURIDICA = PJUR_ID_PESSOA
    AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
    AND TPDP_ID_PARTE = $tipoParte
    AND PAPD_DH_EXCLUSAO IS NULL
    $and
    UNION
SELECT  LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(PAPD_SG_SECAO,PAPD_CD_LOTACAO),'-',' ') ||' - '||PAPD_CD_LOTACAO||' - '||PAPD_SG_SECAO||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(PAPD_SG_SECAO,PAPD_CD_LOTACAO) NOME,
            PAPD_ID_DOCUMENTO ID_DOC,
            PAPD_CD_LOTACAO ID,
            PAPD_SG_SECAO||'-'||PAPD_CD_LOTACAO VALUE,
            'partes_unidade[]' TIPO,
            4 ORDEM, -- nao alterar esse valor
            TO_CHAR(PAPD_CD_LOTACAO) USUARIO,
            TPDP_ID_PARTE TIPO_PARTE
FROM SAD_TB_PAPD_PARTE_PROC_DOC,
        RH_CENTRAL_LOTACAO,
        SAD_TB_TPDP_TIPO_PARTE_DOC_PRO
WHERE PAPD_SG_SECAO = LOTA_SIGLA_SECAO
    AND PAPD_CD_LOTACAO = LOTA_COD_LOTACAO
    AND PAPD_ID_TP_PARTE = TPDP_ID_PARTE
    AND TPDP_ID_PARTE = $tipoParte
    AND PAPD_DH_EXCLUSAO IS NULL
    $and 
ORDER BY ORDEM";

        return $this->_db->query($query)->fetchAll();
    }

    /**
     * Passa as partes ou vistas de um documento ou processo para outro documento ou processo
     * 
     * @param array $documentoPai
     * @param array $documentoFilho
     * @param int $tipoParte
     * @param boolean $autoCommit
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function passaParteVista($documentoPai, $documentoFilho, $tipoParte, $autoCommit = true) {

        $partes = array(
            'partes_pessoa_trf[]' => array()
            , 'partes_unidade[]' => array()
            , 'partes_pess_ext[]' => array()
            , 'partes_pess_jur[]' => array()
        );

        if ($documentoPai['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //busca as partes do documento principal
            $tuplasPartes = $this->getPartesVistas($documentoPai['PRDI_ID_PROCESSO_DIGITAL'], $documentoPai['DTPD_NO_TIPO'], $tipoParte);
            if (!empty($tuplasPartes)) {
                foreach ($tuplasPartes as $parte) {
                    $partes[$parte['TIPO']][] = $parte['VALUE'] . '-' . $parte['TIPO_PARTE'];
                }
            }
        } else {
            //busca as partes do documento principal
            $tuplasPartes = $this->getPartesVistas($documentoPai['DOCM_ID_DOCUMENTO'], $documentoPai['DTPD_ID_TIPO_DOC'], $tipoParte);
            if (!empty($tuplasPartes)) {
                foreach ($tuplasPartes as $parte) {
                    $partes[$parte['TIPO']][] = $parte['VALUE'] . '-' . $parte['TIPO_PARTE'];
                }
            }
        }

        //JOGAR AS REGRAS DA DB TABLE PARA ESSA CLASSE 
        if (count($partes) > 0) {
            $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            $SadTbPapdParteProcDoc->adicionaPartesDocmProc(
                    $partes['partes_pessoa_trf[]']
                    , $partes['partes_unidade[]']
                    , $partes['partes_pess_ext[]']
                    , $partes['partes_pess_jur[]']
                    , $documentoFilho
                    , ($documentoFilho['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO ? $documentoFilho : array())
                    , $autoCommit);
        }
    }

    /**
     * Adiciona, ativa ou completa as partes ou vistas de um documento
     * @param array $documento
     * @param array $data
     * @param bool $commit
     * @return array
     * @throws Exception
     */
    public function addParteVistas($documento, $data, $commit = true) {
        $dataPartePessoa = array();
        $dataParteLotacao = array();
        $dataPartePessExterna = array();
        $dataPartePessJur = array();
        $cont = 0;
        //verifica se não é confidencialidade pública
        $isNotConfPublico = ($data['DOCM_ID_CONFIDENCIALIDADE'] != Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO);
        if (isset($data['partes_pessoa_trf']) && count($data['partes_pessoa_trf']) > 0) {
            $dataPartePessoa = $data['partes_pessoa_trf'];
            if ($isNotConfPublico) {
                $cont += $this->hasVistasArray($dataPartePessoa);
            }
        }
        if (isset($data['partes_unidade']) && count($data['partes_unidade']) > 0 && $data['DOCM_ID_CONFIDENCIALIDADE'] != Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_AO_SUBGRUPO_SIGILOSO) { //se for doc sigiloso nao pode ter unidade como interessada
            $dataParteLotacao = $data['partes_unidade'];
            if ($isNotConfPublico) {
                $cont += $this->hasVistasArray($dataParteLotacao);
            }
        }
        if (isset($data['partes_pess_ext']) && count($data['partes_pess_ext']) > 0) {
            $dataPartePessExterna = $data['partes_pess_ext'];
            if ($isNotConfPublico) {
                $cont += $this->hasVistasArray($dataPartePessExterna);
            }
        }
        if (isset($data['partes_pess_jur']) && count($data['partes_pess_jur']) > 0) {
            $dataPartePessJur = $data['partes_pess_jur'];
            if ($isNotConfPublico) {
                $cont += $this->hasVistasArray($dataPartePessExterna);
            }
        }
        if ($commit) {
            $this->_db->beginTransaction();
        }

        try {

            //se é publico ou não é publico mas tem vistas, e tem alguma parte ou vista para cadastrar
            if ((!$isNotConfPublico || ($isNotConfPublico && $cont > 0)) && (count($dataPartePessoa) > 0 || count($dataParteLotacao) > 0 || count($dataPartePessExterna) > 0 || count($dataPartePessJur) > 0)) {
                //JOGAR AS REGRAS DA DB TABLE PARA ESSA CLASSE 
                $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $documento, $documento, false);
            } else {
                //somente entra nesta parte se a função foi chamada mas não foram passado valores para serem alterados.
                //se for público
                if ($isNotConfPublico) {
                    //verifica se tem vistas
                    $hasVistasDocumento = $this->hasVistasDocumento($documento);
                    if (!$hasVistasDocumento) {
                        throw new Exception('Para documentos não públicos é necessário cadastrar vistas.');
                    }
                }
                //se não cair nas restrições retorna a mensagem
                return array('mensagem' => 'Não foi solicitada a alteração de nenhum dado.', 'validado' => true);
            }
            if ($commit) {
                $this->_db->commit();
            }
            return array('mensagem' => 'Partes / vistas adicionadas com sucesso.', 'validado' => true);
        } catch (Exception $exc) {
            if ($commit) {
                $this->_db->rollBack();
            }
            return array('mensagem' => $exc->getMessage(), 'validado' => false);
        }
    }

    /**
     * Deleta as vistas de um documento
     * @param boolean $commit
     * @param array $data
     */
    public function desativaVistas($documento) {
        $dbTablePapd = new Application_Model_DbTable_Sisad_SadTbPapdParteProcDoc();
        $array = array(
            'PAPD_CD_MATRICULA_EXCLUSAO' => $this->_userNs->matricula
            , 'PAPD_DH_EXCLUSAO' => new Zend_Db_Expr('TO_DATE(\'' . $this->_dateTime . '\',\'dd/mm/YYYY HH24:MI:SS\')')
        );
        $where = array();
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $where[] = 'PAPD_ID_PROCESSO_DIGITAL = ' . $documento['PRDI_ID_PROCESSO_DIGITAL'];
        } else {
            $where[] = 'PAPD_ID_DOCUMENTO = ' . $documento['DOCM_ID_DOCUMENTO'];
        }
        $where[] = 'PAPD_ID_TP_PARTE =' . Trf1_Sisad_Definicoes::PARTE_VISTA;

        return $dbTablePapd->update($array, $where);
    }

    /**
     * Verifica se no array com o formato utilizado para manipular vistas e partes.
     * Possui alguma vista.
     * 
     * @param array $data
     * @return array
     */
    public function hasVistasArray($data) {
        $cont = 0;
        foreach ($data as $dados) {
            $value = explode("-", $dados);
            if ($value[2] == Trf1_Sisad_Definicoes::PARTE_VISTA) {
                $cont++;
            }
        }
        return ($cont > 0);
    }

    /**
     * Verifica se o documento possui alguma vista cadastrada.
     * 
     * @param array $documento
     * @return boolean
     */
    public function hasVistasDocumento($documento) {
        $dbTablePapd = new Application_Model_DbTable_Sisad_SadTbPapdParteProcDoc();
        $documento['DTPD_ID_TIPO_DOC'] = (isset($documento['DTPD_ID_TIPO_DOC']) ? $documento['DTPD_ID_TIPO_DOC'] : $documento['DOCM_ID_TIPO_DOC']);
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $where[] = 'PAPD_ID_PROCESSO_DIGITAL = ' . $documento['PRDI_ID_PROCESSO_DIGITAL'];
        } else {
            $where[] = 'PAPD_ID_DOCUMENTO = ' . $documento['DOCM_ID_DOCUMENTO'];
        }
        $where[] = 'PAPD_ID_TP_PARTE =' . Trf1_Sisad_Definicoes::PARTE_VISTA;
        return ($dbTablePapd->fetchAll($where)->count() > 0);
    }

}