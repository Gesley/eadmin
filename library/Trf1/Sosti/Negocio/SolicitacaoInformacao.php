<?php

/**
 * @category	TRF1
 * @package	Trf1_Sosti_Negocio_SolicitacaoInformacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Leidison Siqueira Barbosa
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre pedido de informação
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
class Trf1_Sosti_Negocio_SolicitacaoInformacao {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    private $_db;

    public function __construct() {
        //ADAPTADOR DO BANCO DE DADOS
        $this->_db = Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * Retorna o destino e a fase da resposta da solicitação de informação
     *
     * 1 - Regra de negócio
     *
     * - pega ultima movimentação
     *
     * - Ultima solicitação de informação é 1024?
     *   - SIM
     *       - Existe fase 1025 depois da ultima fase 1024?
     *           - SIM
     *               - retorna null (não pode responder algo já respondido)
     *           - NÃO
     *               - retorna fase 1025 como resposta
     *               - e-mail para o gerador da fase 1024
     *   - NÃO
     *       - Quem responde é o encaminhador OU (CAIXA ATUAL É A DO ENCAMINHADOR e quem responde não é o cadastrante)?
     *           - SIM //responde para o desenvolvedor
     *               - tem fase 1025 depois da ultima fase 1024?
     *                   - SIM
     *                       - retorna null(não pode responder algo já respondido)
     *                   - NÃO
     *                       - retorna fase 1025(resposta)
     *                       - e-mail para o gerador da fase 1024(inclusão)
     *           - NÃO //responde para o encaminhador
     *               - Quem responde é o usuário cadastrante OU CAIXA ATUAL É A DO USUÁRIO CADASTRANTE?
     *                   - SIM
     *                       - tem fase 1058 depois da ultima fase 1059?
     *                           - SIM
     *                               - retorna null(não pode responder algo já respondido)
     *                           - NÃO
     *                               - retorna fase 1058(responsta)
     *                               - e-mail para o gerador da fase 1059(inclusão)
     *                   - NÃO
     *                       - retorna null (acesso á solicitação de informação por uma pessoa não autorizada)
     * 
     * @param array $solicitacao
     * @return array |array('fase' => int,'para' => string)|array('erro' => string)|
     */
    public function getDestinoFaseResposta($solicitacao) {
        //valida se tem os dados necessários na variavel passada
        if (!isset($solicitacao['MOFA_ID_MOVIMENTACAO']) || !isset($solicitacao['DOCM_CD_MATRICULA_CADASTRO'])) {
            return array('erro' => 'Necessário passar as variáveis corretas.');
        }

        $rn_fase = new Trf1_Sosti_Negocio_Fase();
        //busca os pedidos de informação da fase especificada
        $solicitacoesDeInformacao = $rn_fase->getFaseMovimentacao(array(Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI, Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO), $solicitacao['MOFA_ID_MOVIMENTACAO']);
        //se tiver alguma solicitação de informação
        if (!is_null($solicitacoesDeInformacao)) {
            $rn_rh = new Trf1_Rh_Negocio_Lotacao();
            //plugin para buscar a unidade atual na sessao
            $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
            $userNs = new Zend_Session_Namespace('userNs');
            //se ultima fase for pedido de informação normal
            if ($solicitacoesDeInformacao[0]['MOFA_ID_FASE'] == Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI) {
                //RESPONDE PARA QUEM REALIZOU A SOLICITAÇÃO DE INFORMAÇÃO
                //já é sabido que a ultima solicitação de informação é normal
                //se tiver alguma resposta depois da fase de pedido
                if ($rn_fase->isFaseDepoisData(Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI, $solicitacao['MOFA_ID_MOVIMENTACAO'], $solicitacoesDeInformacao[0]['MOFA_DH_FASE'])) {
                    //já tem resposta
                    return array('erro' => 'Já existe uma resposta para essa solicitação de informação.');
                } else {
                    //fase inclusão de informação normal para o realizador da fase (quem solicitou)
                    return array('fase' => Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI, 'para' => $solicitacoesDeInformacao[0]['MOFA_CD_MATRICULA']);
                }
            } else {
                //RESPONDE PARA O ENCAMINHADOR PARA O DESENVOLVIMENTO OU PARA O DESENVOLVEDOR
                //já é sabido que a ultima solicitação de informação é ao solicitante

                $lotacaoEncaminhador = $rn_rh->getLotacaoPorMatricula($solicitacoesDeInformacao[0]['MOFA_CD_MATRICULA']);
                //se quem responde é o encaminhador para o desenvolvimento OU (CAIXA ATUAL É A DO ENCAMINHADOR e quem responde não é o cadastrante)
                if ($userNs->matricula == $solicitacoesDeInformacao[0]['MOFA_CD_MATRICULA'] || ($lotacaoEncaminhador[0]['LOTA_SIGLA_SECAO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $lotacaoEncaminhador[0]['LOTA_COD_LOTACAO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade() && $userNs->matricula != $solicitacao['DOCM_CD_MATRICULA_CADASTRO'])) {
                    //RESPONDE PARA O DESENVOLVEDOR
                    //busca o ultimo pedido de informação normal(ao encaminhador) na fase especificada
                    $ultimaSolicitacaoInformacaoNormal = $rn_fase->getFaseMovimentacao(Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI, $solicitacao['MOFA_ID_MOVIMENTACAO']);
                    //se tiver alguma reposta para a solicitação de informação normal
                    if ($rn_fase->isFaseDepoisData(Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI, $solicitacao['MOFA_ID_MOVIMENTACAO'], $ultimaSolicitacaoInformacaoNormal['MOFA_DH_FASE'])) {
                        //já tem resposta
                        return array('erro' => 'Já existe uma resposta para essa solicitação de informação técnica.');
                    } else {
                        return array('fase' => Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI, 'para' => $ultimaSolicitacaoInformacaoNormal['MOFA_CD_MATRICULA']);
                    }
                } else {
                    //RESPONDE PARA O ENCAMINHADOR PARA O DESENVOLVIMENTO
                    $lotacaoUsuarioCadatrante = $rn_rh->getLotacaoPorMatricula($solicitacao['DOCM_CD_MATRICULA_CADASTRO']);
                    //se quem responde é o usuário cadastrante OU CAIXA ATUAL É A DO USUÁRIO CADASTRANTE
                    if ($userNs->matricula == $solicitacao['DOCM_CD_MATRICULA_CADASTRO'] || ($lotacaoUsuarioCadatrante[0]['LOTA_SIGLA_SECAO'] == $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade() && $lotacaoUsuarioCadatrante[0]['LOTA_COD_LOTACAO'] == $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade())) {
                        //se tiver resposta da solicitação de informação
                        if ($rn_fase->isFaseDepoisData(Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO, $solicitacao['MOFA_ID_MOVIMENTACAO'], $solicitacoesDeInformacao[0]['MOFA_DH_FASE'])) {
                            //já tem resposta
                            return array('erro' => 'Já existe uma resposta para essa solicitação de informação ao usuário.');
                        } else {
                            return array('fase' => Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO, 'para' => $solicitacoesDeInformacao[0]['MOFA_CD_MATRICULA']);
                        }
                    } else {
                        //acesso à solicitação de informação por uma pessoa não autorizada
                        return array('erro' => 'Acesso negado para a solicitação de informação.');
                    }
                }
            }
        } else {
            //não existe solicitação de informação a ser respondida
            return array('erro' => 'Não existe solicitação de informação para esta solicitação.');
        }
    }

}