<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Distribuicao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad-Distribuição de Processos Administrativos
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
class Trf1_Sisad_Negocio_Distribuicao {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Armazena dados da sessão do usuário logado
     *
     * @var Zend_Session_Namespace $_userNs
     */
    protected $_userNs;

    /**
     * Armazena mensagens de erro
     *
     * @var array $_erro
     */
    private $_erro = array();

    /**
     * Armazena mensagens de sucesso
     *
     * @var array $_sucesso
     */
    private $_sucesso = array();

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    private function addErro($mensagemErro) {
        $this->_erro[] = $mensagemErro;
    }

    private function addSucesso($mensagemSucesso) {
        $this->_sucesso[] = $mensagemSucesso;
    }

    public function getErros() {
        return $this->_erro;
    }

    public function getSucesso() {
        return $this->_sucesso;
    }

    public function processoDistribuicao($arrayData) {
        $rn_processo = new Trf1_Sisad_Negocio_Processo();
        $rn_lotacao = new Trf1_Rh_Negocio_Lotacao();
        /* Substituir por data e hora com milesimos do ZEND */
        $dual = new Application_Model_DbTable_Dual();
        foreach ($arrayData['documento'] as $documento) {
            //Por default a distribuição está negada
            $distribuir = false;
            try {
                $documento = Zend_Json::decode($documento);
                $this->_db->beginTransaction();
                // Pega os dados do processo
                $arrayDadosProcesso = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);

                $idProcesso = $arrayDadosProcesso['PRDI_ID_PROCESSO_DIGITAL'];
                $idOrgao = $arrayData['ORGJ_CD_ORGAO_JULGADOR'];

                if ($arrayData['GRUPO_DISTRIBUICAO'] == 'distautomatica') {
                    /* Distribuição Automática */
                    //pega a flag icPlenário
                    $icPlenario = $arrayData['promo-' . $documento['DOCM_ID_DOCUMENTO']];
                    $dadosRelator = $this->getSorteio($idOrgao, $icPlenario, $idProcesso);
                    //se algum relator(a) for sorteado
                    if (isset($dadosRelator['PMAT_CD_MATRICULA'])) {
                        $tipoDistribuicao = $dadosRelator['HDPA_IC_FORMA_DISTRIBUICAO'];
                        //permite a distribuição
                        $distribuir = true;
                    } else {
                        $mensagemErro = 'Não foi possível sortear um(a) relator(a) para o processo administrativo nº ' . $documento['DOCM_NR_DOCUMENTO'] . '. Verifique se estão todos impedidos. ';
                    }
                } else {
                    /* Distribuição Manual */
                    //pega a flag icPlenário
                    $icPlenario = $arrayData['promo-' . $documento['DOCM_ID_DOCUMENTO']];
                    $tipoDistribuicao = 'DM';
                    $dadosRelator['PMAT_CD_MATRICULA'] = $arrayData['matricula_membro'];
                    //pega a lotação do usuário escolhido
                    $dadosLotacao = $rn_lotacao->getLotacaoPorMatricula($dadosRelator['PMAT_CD_MATRICULA']);
                    //se tiver retornado os dados da lotação
                    if (count($dadosLotacao) > 0) {
                        $dadosRelator = array_merge($dadosRelator, $dadosLotacao[0]);
                        //verifica se o membro possui algum impedimento no orgão julgador na distribuição do processo selecionado
                        if ($this->isImpedidoDistribuicao($idProcesso, $idOrgao, $dadosRelator['PMAT_CD_MATRICULA'])) {
                            $mensagemErro = 'O relator(a) ' . $dadosRelator['PMAT_CD_MATRICULA'] . ' - '. $dadosRelator['PNAT_NO_PESSOA'] . ' está impedido(a) na distribuição do processo administrativo nº ' . $documento['DOCM_NR_DOCUMENTO'];
                            $mensagemErro .= ' no ' . $arrayData['nome_orgao'] . '.';
                        } else {
                            //se possuir flag promoção
                            //verificar se tem flag tambem cabeça
                            if ($icPlenario == 'S') {
                                if ($this->isPromocaoDistribuicaoEspecial($idOrgao, $dadosRelator['PMAT_CD_MATRICULA'])) {
                                    //permite a distribuição
                                    $distribuir = true;
                                } else {
                                    $mensagemErro = 'O relator(a) ' . $dadosRelator['PMAT_CD_MATRICULA'] . ' - '. $dadosRelator['PNAT_NO_PESSOA'] . ' não possi a flag promoção sinalizada como S (sim)';
                                    $mensagemErro .= ' no ' . $arrayData['nome_orgao'] . '.';
                                }
                            } else {
                                $distribuir = true;
                            }
                        }
                    } else {
                        $mensagemErro = 'Não foi possível localizar a lotação do(a) relator(a) ' . $dadosRelator['PMAT_CD_MATRICULA'] . ' - '. $dadosRelator['PNAT_NO_PESSOA'];
                    }
                }
                /* Trocar para Chamada pelo ZEND (pegar milisegundos também) */
                $dataEhoraMili = $dual->localtimestampDb();
                if ($distribuir) {
                    //Efetua a distribuição do processo administrativo
                    $this->distribuirProcessoAdministrativo($dadosRelator, $idOrgao, $documento, $arrayDadosProcesso, $tipoDistribuicao, $dataEhoraMili['DATA'], false);
                    $mensagemSucesso = ' Processo administrativo nº ' . $documento['DOCM_NR_DOCUMENTO'] . ' distribuído com sucesso para o(a) relator(a) ' . $dadosRelator['PMAT_CD_MATRICULA'] . ' - '. $dadosRelator['PNAT_NO_PESSOA'] . '.';
                    $this->addSucesso($mensagemSucesso);
                } else {
                    $mensagemErro .= ' Distribuição do processo administrativo nº ' . $documento['DOCM_NR_DOCUMENTO'] . ' cancelada.';
                    $this->addErro($mensagemErro);
                }
                $this->_db->commit();
            } catch (Exception $e) {
                $this->_db->rollBack();
                $mensagemErro .= ' Não foi possível distribuir o processo administrativo nº ' . $documento['DOCM_NR_DOCUMENTO'] . '. ERRO: ' . $e->getMessage() . '.';
                $this->addErro($mensagemErro);
            }
        }
    }

    private function getSorteio($idOrgao, $icPlenario, $idProcesso = null) {
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();
        $rn_orgaoJulgador = new Trf1_Sisad_Negocio_OrgaoJulgador();
        $matExcluido = null;
        if ($idProcesso != null) {
            //busca relator(a)es do processo
            $arrayRelatores = $bd_distribuicao->getRelatoresProcesso($idProcesso);
            if ($arrayRelatores != null) {
                //pega o ultimo pois ele não poderá ser o proximo relator(a) caso seja Redistribuição
                $arrayRelador = array_pop($arrayRelatores);
                if ($rn_orgaoJulgador->isOrgaoEspecial($arrayRelador['HDPA_CD_ORGAO_JULGADOR'])) {
                    $matExcluido = $arrayRelador['HDPA_CD_JUIZ'];
                } else {
                    $matExcluido = $arrayRelador['HDPA_CD_SERVIDOR'];
                }
            }
        }
        if ($rn_orgaoJulgador->isOrgaoEspecial($idOrgao)) {
            $arraySorteados = $this->getSorteioDesembargadores($idOrgao, $idProcesso, $icPlenario, $matExcluido);
        } else {
            $arraySorteados = $this->getSorteioComissao($idOrgao, $idProcesso, $matExcluido);
        }
        //faz o sorteio entre os membros
        $sorteado = $arraySorteados[array_rand($arraySorteados)];
        if ($matExcluido == null) {
            $sorteado['HDPA_IC_FORMA_DISTRIBUICAO'] = 'DA';
        } else {
            $sorteado['HDPA_IC_FORMA_DISTRIBUICAO'] = 'RA';
        }
        return $sorteado;
    }

    private function getSorteioDesembargadores($idOrgao, $idProcesso, $icPlenario, $matExcluido = null) {

        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();

        if ($icPlenario == null) {
            $icPlenario = 'N';
        }
        //se não tiver desembargadores disponiveis
        if (!$this->isDisponivelDistribuicao($idOrgao, $idProcesso, $icPlenario)) {
            //coloca todo mundo como disponivél na distribuição
            $alteracao = $bd_distribuicao->setaDisponibilidadeDistEspecial($idOrgao, $idProcesso, $icPlenario);
        }
        return $bd_distribuicao->getSorteioDesembargadores($idOrgao, $idProcesso, $icPlenario, $matExcluido);
    }

    private function getSorteioComissao($idOrgao, $idProcesso, $matExcluido = null) {

        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();
        //se não tiver membro de comissão disponivel
        if (!$this->isDisponivelDistribuicao($idOrgao, $idProcesso)) {
            //coloca todo mundo como disponivél na distribuição
            $alteracao = $bd_distribuicao->setaDisponibilidadeDistComissao($idOrgao, $idProcesso);
        }
        return $bd_distribuicao->getSorteioComissao($idOrgao, $idProcesso, $matExcluido);
    }

    public function isDisponivelDistribuicao($idOrgao, $idProcesso, $icPlenario = null) {
        $rn_orgaoJulgador = new Trf1_Sisad_Negocio_OrgaoJulgador();
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();

        if ($rn_orgaoJulgador->isOrgaoEspecial($idOrgao)) {
            return $bd_distribuicao->isDisponivelDistribuicaoEspecial($idOrgao, $idProcesso, $icPlenario);
        } else {
            return $bd_distribuicao->isDisponivelDistribuicaoComissao($idOrgao, $idProcesso);
        }
    }

    public function isImpedidoDistribuicao($idProcesso, $idOrgao, $matriculaMembro) {
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();
        $rn_orgaoJulgador = new Trf1_Sisad_Negocio_OrgaoJulgador();
        if ($rn_orgaoJulgador->isOrgaoEspecial($idOrgao)) {
            return $bd_distribuicao->isImpedidoDistribuicaoEspecial($idProcesso, $idOrgao, $matriculaMembro);
        } else {
            return $bd_distribuicao->isImpedidoDistribuicaoComissao($idProcesso, $idOrgao, $matriculaMembro);
        }
    }

    public function isPromocaoDistribuicaoEspecial($idOrgao, $matriculaMembro) {
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();
        return $bd_distribuicao->isPromocaoDistribuicaoEspecial($idOrgao, $matriculaMembro);
    }

    private function trataDevolucaoProcesso($idProcesso) {

        $rn_orgaoJulgador = new Trf1_Sisad_Negocio_OrgaoJulgador();
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();

        $dadosUltimaDist = $bd_distribuicao->dadosUltimaDistribuicaoProcesso('todos', '', $idProcesso);
        if (count($dadosUltimaDist) != 0) {
            if ($rn_orgaoJulgador->isOrgaoEspecial($dadosUltimaDist[0]['ORGJ_CD_ORGAO_JULGADOR'])) {
                $bd_distribuicao->trataDevolucaoProcessoEspecial($dadosUltimaDist[0]['ORGJ_CD_ORGAO_JULGADOR'], $dadosUltimaDist[0]['PMAT_CD_MATRICULA']);
            } else {
                $bd_distribuicao->trataDevolucaoProcessoComissao($dadosUltimaDist[0]['ORGJ_CD_ORGAO_JULGADOR'], $dadosUltimaDist[0]['PMAT_CD_MATRICULA']);
            }
        }
    }

    private function distribuirProcessoAdministrativo($dadosRelator, $idOrgao, $documento, $arrayDadosProcesso, $tipoDistribuicao, $dataHoraMili, $autoCommit = true) {
        $rn_movimentacao = new Trf1_Sisad_Negocio_Movimentacao();
        $rn_orgaoJulgador = new Trf1_Sisad_Negocio_OrgaoJulgador();
        $rn_Processo = new Trf1_Sisad_Negocio_Processo();
        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();

        $rel_distribuicao = new Trf1_Sisad_Relatorios_Distribuicao();

        if ($autoCommit) {
            $this->_db->beginTransaction();
        }


        $zend_date = new Zend_Date($dataHoraMili, 'dd/MM/YY HH:mm:ss');
        $datahora = $zend_date->get(Zend_Date::DATETIME);
        $datahora = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/YY HH24:MI:SS')");
        $dataHoraMiliSQL = new Zend_Db_Expr("TO_TIMESTAMP('$dataHoraMili','dd/mm/YY HH24:MI:SS,FF')");

        $dataMoviMovimentacao = array(
            'MOVI_SG_SECAO_UNID_ORIGEM' => $documento["MODE_SG_SECAO_UNID_DESTINO"]
            , 'MOVI_CD_SECAO_UNID_ORIGEM' => $documento["MODE_CD_SECAO_UNID_DESTINO"]
            , 'MOVI_CD_MATR_ENCAMINHADOR' => $this->_userNs->matricula
        );

        $dataModeMoviDestinatario = array(
            'MODE_SG_SECAO_UNID_DESTINO' => $dadosRelator['LOTA_SIGLA_SECAO']
            , 'MODE_CD_SECAO_UNID_DESTINO' => $dadosRelator['LOTA_COD_LOTACAO']
            , 'MODE_IC_RESPONSAVEL' => 'N'); //,  default N

        $dataModpDestinoPessoa = array(
            'MODP_CD_MAT_PESSOA_DESTINO' => $dadosRelator['PMAT_CD_MATRICULA']);

        //busca os dados da fase
        $fase = Trf1_Sisad_Negocio_Fase::getFaseDistribuicao($tipoDistribuicao);

        $dataMofaMoviFase = array(
            'MOFA_ID_FASE' => $fase['id']
            , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
            , 'MOFA_DS_COMPLEMENTO' => $fase['descricao']);

        //gerando ata de distribuição

        $nrDocsRed = $rel_distribuicao->gerarAtaDeDistribuicao($arrayDadosProcesso['PRDI_ID_PROCESSO_DIGITAL'], $documento['DOCM_NR_DOCUMENTO'], $idOrgao, $dadosRelator['PMAT_CD_MATRICULA'], $tipoDistribuicao, $dataHoraMili);

        $rn_movimentacao->encaminhaDocumento($documento['DOCM_ID_DOCUMENTO'], $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, array(), $nrDocsRed['incluidos'], $datahora, false);

        $dadosAlteracaoProcesso = array();
        $dadosAlteracaoProcesso['PRDI_DH_DISTRIBUICAO'] = $datahora;
        $dadosAlteracaoProcesso['PRDI_CD_MATR_DISTRIBUICAO'] = $this->_userNs->matricula;
        $dadosAlteracaoProcesso['PRDI_IC_TP_DISTRIBUICAO'] = $tipoDistribuicao;
        $dadosAlteracaoProcesso['PRDI_CD_ORGAO_JULGADOR'] = $orgaoJulgador;

        if (strstr($dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'], 'DS')) {
            $rn_gabinete = new Trf1_Rh_Negocio_Gabinete();
            $dadosGabinete = $rn_gabinete->getGabineteDesembargador($dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO']);
            $dataModeMoviDestinatario['MODE_CD_SECAO_UNID_DESTINO'] = $dadosGabinete['CODIGO_LOTACAO'];
        }

        //Se for uma redistribuição de processo administrativo
        //O sistema irá tratar a devolução
        $this->trataDevolucaoProcesso($arrayDadosProcesso['PRDI_ID_PROCESSO_DIGITAL']);

        $dadosHistorico = array();
        if ($rn_orgaoJulgador->isOrgaoEspecial($idOrgao)) {
            $dadosAlteracaoProcesso['PRDI_CD_JUIZ_RELATOR_PROCESSO'] = $dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'];
            $dadosAlteracaoProcesso['PRDI_CD_MATR_SERV_RELATOR'] = null;
            $dadosHistorico['HDPA_CD_JUIZ'] = $dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'];
            $bd_distribuicao->inseriStatusDistEspecial($idOrgao, $dadosRelator['PMAT_CD_MATRICULA']);
        } else {
            $dadosAlteracaoProcesso['PRDI_CD_MATR_SERV_RELATOR'] = $dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'];
            $dadosAlteracaoProcesso['PRDI_CD_JUIZ_RELATOR_PROCESSO'] = null;
            $dadosHistorico['HDPA_CD_SERVIDOR'] = $dataModpDestinoPessoa['MODP_CD_MAT_PESSOA_DESTINO'];
            $bd_distribuicao->inseriStatusDistComissao($idOrgao, $dadosRelator['PMAT_CD_MATRICULA']);
        }

        $rn_Processo->alteraDadosProcesso($arrayDadosProcesso['PRDI_ID_PROCESSO_DIGITAL'], $dadosAlteracaoProcesso);

        $dadosHistorico['HDPA_CD_PROC_ADMINISTRATIVO'] = $arrayDadosProcesso['PRDI_ID_PROCESSO_DIGITAL'];
        $dadosHistorico['HDPA_TS_DISTRIBUICAO'] = $dataHoraMiliSQL;
        $dadosHistorico['HDPA_IC_FORMA_DISTRIBUICAO'] = $tipoDistribuicao;
        $dadosHistorico['HDPA_CD_ORGAO_JULGADOR'] = $idOrgao;
        $dadosHistorico['HDPA_CD_SERVIDOR_RESPONSAVEL'] = $userNs->matricula;

        $bd_distribuicao->setHistoricoDistribuicao($dadosHistorico);

        if ($autoCommit) {
            $this->_db->commit();
        }
    }

}