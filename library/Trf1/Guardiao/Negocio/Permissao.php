<?php

/**
 * Classe negocial - Permissões de acesso
 * 
 * @category	TRF1
 * @package		Trf1_Guardiao_Permissao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Guardiao_Negocio_Permissao {

    /**
     * Retorna todas as permissões do e-Orçamento por usuário
     *
     * @param	string $matriculaUsuario
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function getPermissoesOrcamento($matriculaUsuario) {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Permissao(strtoupper($matriculaUsuario));
        $dados = $cache->lerCache($cacheId);

        if ($dados == false) {
            //Não existindo o cache, busca do banco
            $sql = "
SELECT
	PERF.PERF_DS_PERFIL,
	UNPE.UNPE_SG_SECAO,
	UNPE.UNPE_CD_LOTACAO,
	RHCL.LOTA_SIGLA_LOTACAO
FROM
	OCS_TB_PUPE_PERFIL_UNID_PESSOA		PUPE
Left JOIN
	OCS_TB_UNPE_UNIDADE_PERFIL			UNPE ON
		UNPE.UNPE_ID_UNIDADE_PERFIL		= PUPE.PUPE_ID_UNIDADE_PERFIL
Left JOIN
	OCS_TB_PERF_PERFIL					PERF ON
		PERF.PERF_ID_PERFIL				= UNPE.UNPE_ID_PERFIL
Left JOIN
	RH_CENTRAL_LOTACAO					RHCL ON
		RHCL.LOTA_SIGLA_SECAO			= UNPE.UNPE_SG_SECAO		AND
		RHCL.LOTA_COD_LOTACAO			= UNPE.UNPE_CD_LOTACAO
WHERE
	PUPE.PUPE_CD_MATRICULA				= '" . strtoupper($matriculaUsuario) . "'   AND
	PERF.PERF_DS_PERFIL					Like '" . Trf1_Orcamento_Definicoes::TEXTO_INICIAL_PADRAO_PERFIL_ORCAMENTO . "%'
					";

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchAll($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Retorna um array contendo dados relativos as permissões.
     * @tutorial Pode-se fazer combinação entre $idPerfil e $matricula passando 
     * hora um null, hora outro null ou ambos null.
     * 
     * @param	int	$idDocumento
     * @return	array
     * @author	Leidison Siqueira Barbosa
     */
    public function getPermissoesPessoaPerfil($idPerfil, $matricula, $order = 'PNAT_NO_PESSOA') {
        $completa = '';
        $arrayBind = array();
        $contador = 0;

        if (!is_null($idPerfil)) {
            $completa .= ' AND PERF_ID_PERFIL = ? ';
            $arrayBind[$contador++] = $idPerfil;
        }

        if (!is_null($matricula)) {
            $completa .= ' AND PMAT_CD_MATRICULA = ? ';
            $arrayBind[$contador++] = $matricula;
        }

        if (!is_null($order)) {
            $completa .= ' ORDER BY ? ';
            $arrayBind[$contador++] = $order;
        }

        $sql = ' 
SELECT PMAT_CD_MATRICULA,PNAT_NO_PESSOA,PMAT_CD_MATRICULA, RHLOTA.LOTA_COD_LOTACAO, 
    RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO, 
    RHLOTA.LOTA_SIGLA_LOTACAO,
    RHLOTA.LOTA_SIGLA_SECAO,
    PERF_ID_PERFIL, 
    PERF_DS_PERFIL
FROM  
    OCS_TB_PNAT_PESSOA_NATURAL PNAT
    INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
        ON PNAT.PNAT_ID_PESSOA = PMAT.PMAT_ID_PESSOA
    INNER JOIN OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
        ON PMAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA
    INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE
        ON PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL
    INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
        ON UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
        AND UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
    INNER JOIN OCS_TB_PERF_PERFIL  PERF
        ON PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
' . $completa;

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql, $arrayBind);
    }

    /**
     * Retorna um array contendo dados relativos ao histórico das permissões.
     * 
     * @param	array	$arrayDados array('PMAT_CD_MATRICULA','UNPE_SG_SECAO','UNPE_CD_LOTACAO')
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getHistoricoPermissao(array $arrayDados) {
        $arrayBind = array(
            $arrayDados['PMAT_CD_MATRICULA']
            , $arrayDados['PMAT_CD_MATRICULA']
            , $arrayDados['UNPE_SG_SECAO']
            , $arrayDados['UNPE_CD_LOTACAO']
            , $arrayDados['UNPE_SG_SECAO']
            , $arrayDados['UNPE_CD_LOTACAO']);
        $sql = '
SELECT
    TO_CHAR(PUPE_TS_OPERACAO,\'DD/MM/YYYY HH24:MI:SS\')PUPE_TS_OPERACAO
    , PUPE_CD_OPERACAO
    , PUPE_CD_MATRICULA_OPERACAO
    , PUPE_CD_MAQUINA_OPERACAO
    , PUPE_CD_USUARIO_SO
    , PMAT_OPERADOR.PMAT_CD_MATRICULA AS "PMAT_CD_MATRICULA_OPERACAO"
    , PNAT_OPERADOR.PNAT_NO_PESSOA AS "PNAT_NO_PESSOA_OPERACAO"
    , PERF_OLD.PERF_ID_PERFIL AS "PERF_ID_PERFIL_OLD"
    , PERF_OLD.PERF_DS_PERFIL AS "PERF_DS_PERFIL_OLD"
    , PERF_NEW.PERF_ID_PERFIL AS "PERF_ID_PERFIL_NEW"
    , PERF_NEW.PERF_DS_PERFIL AS "PERF_DS_PERFIL_NEW"
    , UNPE_OLD.UNPE_SG_SECAO AS "UNPE_SG_SECAO_OLD"
    , UNPE_OLD.UNPE_CD_LOTACAO AS "UNPE_CD_LOTACAO_OLD"
    , UNPE_NEW.UNPE_SG_SECAO AS "UNPE_SG_SECAO_NEW"
    , UNPE_NEW.UNPE_CD_LOTACAO AS "UNPE_CD_LOTACAO_NEW"
    , PMAT_OLD.PMAT_CD_MATRICULA AS "PMAT_CD_MATRICULA_OLD"
    , PNAT_OLD.PNAT_NO_PESSOA AS "PNAT_NO_PESSOA_OLD"
    , PMAT_NEW.PMAT_CD_MATRICULA AS "PMAT_CD_MATRICULA_NEW"
    , PNAT_NEW.PNAT_NO_PESSOA AS "PNAT_NO_PESSOA_NEW"
FROM
    OCS_TB_PUPE_AUDITORIA
    LEFT JOIN OCS_TB_PMAT_MATRICULA PMAT_OPERADOR
        ON PUPE_CD_MATRICULA_OPERACAO = PMAT_OPERADOR.PMAT_CD_MATRICULA
    LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_OPERADOR
        ON PMAT_OPERADOR.PMAT_ID_PESSOA = PNAT_OPERADOR.PNAT_ID_PESSOA
        
    LEFT JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE_OLD
        ON OLD_PUPE_ID_UNIDADE_PERFIL = UNPE_OLD.UNPE_ID_UNIDADE_PERFIL
    LEFT JOIN OCS_TB_PERF_PERFIL PERF_OLD
        ON UNPE_OLD.UNPE_ID_PERFIL = PERF_OLD.PERF_ID_PERFIL
    
    LEFT JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE_NEW
        ON NEW_PUPE_ID_UNIDADE_PERFIL = UNPE_NEW.UNPE_ID_UNIDADE_PERFIL
    LEFT JOIN OCS_TB_PERF_PERFIL PERF_NEW
        ON UNPE_NEW.UNPE_ID_PERFIL = PERF_NEW.PERF_ID_PERFIL
        
    LEFT JOIN OCS_TB_PMAT_MATRICULA PMAT_OLD
        ON OLD_PUPE_CD_MATRICULA = PMAT_OLD.PMAT_CD_MATRICULA
    LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_OLD
        ON PMAT_OLD.PMAT_ID_PESSOA = PNAT_OLD.PNAT_ID_PESSOA
        
    LEFT JOIN OCS_TB_PMAT_MATRICULA PMAT_NEW
        ON NEW_PUPE_CD_MATRICULA = PMAT_NEW.PMAT_CD_MATRICULA
    LEFT JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_NEW
        ON PMAT_NEW.PMAT_ID_PESSOA = PNAT_NEW.PNAT_ID_PESSOA
WHERE
        ( PMAT_NEW.PMAT_CD_MATRICULA = ? OR PMAT_OLD.PMAT_CD_MATRICULA = ? )
    AND ( 
            UNPE_NEW.UNPE_SG_SECAO = ? AND UNPE_NEW.UNPE_CD_LOTACAO = ?
            OR UNPE_OLD.UNPE_SG_SECAO = ? AND UNPE_OLD.UNPE_CD_LOTACAO = ? 
        )
ORDER BY PUPE_TS_OPERACAO DESC
';

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql, $arrayBind);
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * Retorna um array contendo dados relativos ao histórico das permissões.
     * 
     * @param	array	$arrayDados array('PMAT_CD_MATRICULA','UNPE_SG_SECAO','UNPE_CD_LOTACAO')
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function auditarPermissaoUsuarioCaixa($arrayDados, $acao, $autoCommit = true) {
        try {

            $aNamespace = new Zend_Session_Namespace('userNs');
            $ocsTbPupeAuditoria = new Application_Model_DbTable_OcsTbPupeAuditoria();
            if ($autoCommit) {
                $banco = Zend_Db_Table::getDefaultAdapter();
                $banco->beginTransaction();
            }
            $dual = new Application_Model_DbTable_Dual();
            $dataTimeStamp = $dual->localtimestampDb();

            if ($acao == 'inclusao') {

                $data_audit['PUPE_TS_OPERACAO'] = $dataTimeStamp['DATA'];
                $data_audit['PUPE_CD_OPERACAO'] = 'I';
                $data_audit['PUPE_CD_MATRICULA_OPERACAO'] = $aNamespace->matricula;
                $data_audit['PUPE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $data_audit['PUPE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                $data_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = 0;
                $data_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = $arrayDados['PUPE_ID_UNIDADE_PERFIL'];
                $data_audit['OLD_PUPE_CD_MATRICULA'] = 0;
                $data_audit['NEW_PUPE_CD_MATRICULA'] = $arrayDados['PUPE_CD_MATRICULA'];
            } else if ($acao == 'exclusao') {

                $data_audit['PUPE_TS_OPERACAO'] = $dataTimeStamp['DATA'];
                $data_audit['PUPE_CD_OPERACAO'] = 'E';
                $data_audit['PUPE_CD_MATRICULA_OPERACAO'] = $aNamespace->matricula;
                $data_audit['PUPE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $data_audit['PUPE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                $data_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = $arrayDados['PUPE_ID_UNIDADE_PERFIL'];
                $data_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = 0;
                $data_audit['OLD_PUPE_CD_MATRICULA'] = $arrayDados['PUPE_CD_MATRICULA'];
                $data_audit['NEW_PUPE_CD_MATRICULA'] = 0;
            }

            $ocsTbPupeAuditoria->createRow($data_audit)->save();
            if ($autoCommit) {
                $banco->commit();
            }
        } catch (Zend_Exception $e) {
            if ($autoCommit) {
                $banco->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Efetua alteração dos perfis associados ao usuário escolhido na caixa escolhida
     * 
     * @param	array	$arrayPapeis
     * @param	array	$matricula
     * @param	array	$arrayCaixa
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function alterarPermissaoUsuarioCaixa($arrayPapeis, $matricula, $arrayCaixa) {
        try {
            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $banco = Zend_Db_Table::getDefaultAdapter();
            $flagAlteracao = FALSE;
            $banco->beginTransaction();

            foreach ($arrayPapeis as $papeis):

                $papelArray1 = (isset($papeis[1])) ? explode(" - ", $papeis[1]) : array();
                $papelArray2 = (isset($papeis[2])) ? explode(" - ", $papeis[2]) : array();
                $codigoAcao1 = (isset($papelArray1[2])) ? $papelArray1[2] : '';
                $codigoAcao2 = (isset($papelArray2[2])) ? $papelArray2[2] : '';
                //Deletar perfil
                if ($codigoAcao1 == "" && $codigoAcao2 == "associado") {

                    $status = $ocsTbPupePerfilUnidPessoa->delete('PUPE_ID_UNIDADE_PERFIL = ' . $papelArray2[0] . ' AND PUPE_CD_MATRICULA = \'' . $matricula . '\'');
                    if ($status) {
                        $flagAlteracao = TRUE;
                        $this->auditarPermissaoUsuarioCaixa(array('PUPE_ID_UNIDADE_PERFIL' => $papelArray2[0], 'PUPE_CD_MATRICULA' => $matricula), 'exclusao', false);
                    }
                }//Inserir Perfil
                else if ($codigoAcao1 == "associar" && $codigoAcao2 == "dissociado") {
                    if ($ocsTbPupePerfilUnidPessoa->find($papelArray2[0], $matricula)->count() == 0) {
                        $flagAlteracao = TRUE;
                        $primary = $ocsTbPupePerfilUnidPessoa->createRow(array('PUPE_ID_UNIDADE_PERFIL' => $papelArray2[0], 'PUPE_CD_MATRICULA' => $matricula))->save();
                        $this->auditarPermissaoUsuarioCaixa($primary, 'inclusao', false);
                    }
                }
            endforeach;
            $banco->commit();
            return $flagAlteracao;
        } catch (Zend_Exception $e) {
            $banco->rollBack();
            throw $e;
        }
    }

    /**
     * Verifica se usuário tem permissão na Caixa da Corregedoria
     * 
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function hasPermissaoCorregedoria($matricula) {

        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                $usuarioCorregedoria = $this->getPermissoesPessoaPerfil(Trf1_Sisad_Definicoes::PERFIL_CORREGEDORIA_DSV, $matricula);
            } else if (APPLICATION_ENV == 'production') {
                $usuarioCorregedoria = $this->getPermissoesPessoaPerfil(Trf1_Sisad_Definicoes::PERFIL_CORREGEDORIA_PRODUCAO, $matricula);
            }
        }
        if (empty($usuarioCorregedoria)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Associa perfis de uma Unidade à uma Pessoa
     * @param type $arrayPerfis
     * @param type $matricula
     * @param array $dadosUnidade
     * @param type $matricula_sessao
     * @return type
     */
    public function associarPerfilPessoaUnidade($arrayPerfis, $matricula, array $dadosUnidade, $matricula_sessao) {

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $array_unpe = array();
        /**
         * Buscar os Ids da tabela OcsTbUnpeUnidadePerfil que correspondam ao perfil e a unidade do parametro
         */
        foreach ($arrayPerfis as $perfil) {
            $array_unpe[] = $OcsTbUnpeUnidadePerfil->getPerfisUnidade($dadosUnidade["LOTA_SIGLA_SECAO"], $dadosUnidade["LOTA_COD_LOTACAO"], $perfil);
        }

        /**
         * Associar os IDS UNPE ao usuário
         */
        return $associar = $ocsTbPupePerfilUnidPessoa->associarPerfilUnidadeAPessoa($matricula, $array_unpe, $matricula_sessao);
    }

    /**
     * Desvincular todas as permissoes do usuário para aquela Unidade
     * @param type $matricula
     * @param type $dadosUnidade
     * @param type $matricula_sessao
     * @return type
     */
    public function desassociarTodoPerfilPessoaUnidade($matricula, $dadosUnidade, $matricula_sessao) {

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        return $desassociar = $ocsTbPupePerfilUnidPessoa->desassociarTodoPerfilPessoaUnidade($matricula, $dadosUnidade, $matricula_sessao);
    }

    /**
     * Desassocia determinados perfis de uma Pessoa em uma Unidade
     * @param array $arrayPerfis
     * @param type $dadosUnidade
     * @param type $matricula
     * @param type $matricula_sessao
     * @return type
     */
    public function desassociarPerfilPessoaUnidade(array $arrayPerfis, $dadosUnidade, $matricula, $matricula_sessao) {

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        return $desassociar = $ocsTbPupePerfilUnidPessoa->desassociarPerfilPessoaUnidade($arrayPerfis, $dadosUnidade, $matricula, $matricula_sessao);
    }

}
