<?php
/**
 * @category            TRF1
 * @package		Trf1_Soseg_Definicoes
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Dayane Oliveira Freire
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial            Tutorial abaixo
 * 
 * TRF1, Classe genérica de definições, padrões e formatos
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
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * 
 * ESCOPOS DAS CONSTANTES ABAIXO
 *  
 */
final class Trf1_Soseg_Definicoes {
    
     /**
     * Fase: CADASTRO DE SOLICITAÇÃO DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_CADASTRO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_CADASTRO_SOLICITACAO_SERVICO  int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_CADASTRO_SOLICITACAO_SERVICO = 1060;
    
    /**
     * Fase: BAIXA NA SOLICITAÇÃO DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_BAIXA_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_BAIXA_SOLICITACAO_SERVICO  int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_BAIXA_SOLICITACAO_SERVICO = 1061;
    
    /**
     * Fase: CANCELAMENTO DE SOLICITAÇÃO DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_CANCELAMENTO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_CANCELAMENTO_SOLICITACAO_SERVICO  int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_CANCELAMENTO_SOLICITACAO_SERVICO = 1062;
    
    /**
     * Fase: ENCAMINHAMENTO DE SOLICITAÇÃO DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO = 1063;
    
    /**
     * Fase: PARECER NA SOLICITAÇÃO DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_PARECER_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_PARECER_SOLICITACAO_SERVICO  int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_PARECER_SOLICITACAO_SERVICO = 1064;
    
     /**
     * Fase: TROCA DE SERVIÇO 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_TROCA_SERVICO . "') AS ...
     * 
     * @var	FASE_TROCA_SERVICO  int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_TROCA_SERVICO = 1065;
    
    /**
     * Fase: ENCAMINHAMENTO DE SOLICITAÇÃO DE SERVIÇO PARA CAIXA PESSOAL
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO_CX_PESSOAL . "') AS ...
     * 
     * @var	FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO_CX_PESSOAL int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO_CX_PESSOAL = 1066;
    
    /**
     * Fase: FASE PEDIDO INFORMACAO SOLICITACAO DE SERVICO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_PEDIDO_INFORMACAO_SOLICITACAO_SERVICO int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_PEDIDO_INFORMACAO_SOLICITACAO_SERVICO = 1071;
    
    /**
     * Fase: FASE INCLUSÃO DE INFORMACAO SOLICITACAO DE SERVICO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var	FASE_INCLUSAO_INFORMACAO_SOLICITACAO_SERVICO int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_INCLUSAO_INFORMACAO_SOLICITACAO_SERVICO = 1072;
  
    /**
     * Id do tipo de documento Solicitação de serviços gráficos
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO . "') AS ...
     * 
     * @var		ID_TIPO_SOLICITACAO_SERVICO_GRAFICO int (constant)
     * @author	Dayane O. Freire
     */
    const ID_TIPO_SOLICITACAO_SERVICO_GRAFICO = 272;
        
    /**
     * Fase: CANCELAMENTO DE SOLICITAÇÃO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_CANCELAMENTO_SOLICITACAO . "') AS ...
     * 
     * @var	FASE_CANCELAMENTO_SOLICITACAO int (constant)
     * @author	Dayane Oliveira Freire 
     */
    const FASE_CANCELAMENTO_SOLICITACAO = 1026;
    
    
   
    
    /************************************   CAIXAS  ***********************************/
    /**
     * Id da caixa 'CAIXA_ATENDIMENTO_SERVICO_DIGRA'
     * 
     * @var		CAIXA_ATENDIMENTO_SERVICO_DIGRA int (constant)
     * @author	Dayane O. Freire
     */
    const CAIXA_ATENDIMENTO_SERVICO_DIGRA = 37;
    
    /**
     * Id da caixa 'CAIXA_ATENDIMENTO_SERVICO_DIEDI'
     * 
     * @var		CAIXA_ATENDIMENTO_SERVICO_DIEDI int (constant)
     * @author	Dayane O. Freire
     */
    const CAIXA_ATENDIMENTO_SERVICO_DIEDI = 38;
    
    /**
     * Id da caixa 'CAIXA_ATENDIMENTO_SERVICO_DIGET'
     * 
     * @var		CAIXA_ATENDIMENTO_SERVICO_DIGET int (constant)
     * @author	Dayane O. Freire
     */
    const CAIXA_ATENDIMENTO_SERVICO_DIGET = 39;
    
     /************************************   GRUPO_SERVICO   ***********************************/
    /**
     * Id do grupo de serviço 'GRUPO_SERVICO_DIGET'
     * 
     * @var		GRUPO_SERVICO_DIGET int (constant)
     * @author	Dayane O. Freire
     */
    const ID_GRUPO_SERV_DIGET = 139;
    
    /**
     * Id do grupo de serviço 'GRUPO_SERVICO_DIEDI'
     * 
     * @var		GRUPO_SERVICO_DIEDI int (constant)
     * @author	Dayane O. Freire
     */
    const ID_GRUPO_SERV_DIEDI = 140;
    
     /**
     * Id do grupo de serviço 'GRUPO_SERVICO_DIGRA'
     * 
     * @var		GRUPO_SERVICO_DIGRA int (constant)
     * @author	Dayane O. Freire
     */
    const ID_GRUPO_SERV_DIGRA = 141;
    
}
