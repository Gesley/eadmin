<?php

/**
 * @category	TRF1
 * @package		Trf1_Sosti_Definicoes
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe genérica de definições, padrões e formatos
 * 
 * ====================================================================================================
 * LICENSA
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
final class Trf1_Sosti_Definicoes {
  
    /**
     * Fase: VINCULAÇÃO DE SOLICITAÇÕES
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO . "') AS ...
     *  
     * @var		FASE_VINCULA_SOLICITACAO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_VINCULA_SOLICITACAO = 1035;
    
    /**
     * Fase: VINCULAÇÃO DE SOLICITAÇÃO À NOVA PRINCIPAL
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL . "') AS ...
     * 
     * @var		FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_VINCULA_SOLICITACAO_A_NOVA_PRINCIPAL = 1037;
    
    /**
     * Fase: PEDIDO DE INFORMAÇÃO PARA SOLICITAÇÃO À TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "') AS ...
     * 
     * @var		FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI = 1024;
    
    /**
     * Fase: INCLUSÃO DE INFORMAÇÃO PARA SOLICITAÇÃO DE TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI . "') AS ...
     * 
     * @var		FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI = 1025;
    
    /**
     * Fase: PEDIDO DE INFORMAÇÃO AO USUÁRIO PARA SOLICITAÇÃO À TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "') AS ...
     * 
     * @var		FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO = 1059;//1024
    
    /**
     * Fase: INCLUSÃO DE INFORMAÇÃO AO USUÁRIO PARA SOLICITAÇÃO DE TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "') AS ...
     * 
     * @var		FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO = 1058;//1025
    
     /**
     * Id do tipo de documento Solicitação de serviços a TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var		TIPO_SOLICITACAO_SERVICO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_SOLICITACAO_SERVICO = 160;
    
    
    /**********************************
    Valores para Homologação dos SOSTIS
    ***********************************/
    
    const FASE_HOMOLOGAR_SOLICITACAO_TI = 1085;
    const FASE_HOMOLOGADO_SOLICITACAO_TI = 1086;
    const FASE_NAOHOMOLOGADO_SOLICITACAO_TI = 1087;
    
    /**
     * Fase: BAIXA SOLICITAÇÃO TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_BAIXA_SOLICITACAO_TI . "') AS ...
     * 
     * @var		FASE_BAIXA_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_BAIXA_SOLICITACAO_TI = 1000;
    
    /**FASE_AVALIACAO_SOLICITACAO_TI
     * Fase: AVALIAÇÃO DE SERVIÇO DE TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_AVALIACAO_SOLICITACAO_TI . "') AS ...
     * 
     * @var		FASE_AVALIACAO_SOLICITACAO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_AVALIACAO_SOLICITACAO_TI = 1014;
        
    /**
     * Fase: CANCELAMENTO DE SOLICITAÇÃO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::FASE_CANCELAMENTO_SOLICITACAO . "') AS ...
     * 
     * @var		FASE_CANCELAMENTO_SOLICITACAO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_CANCELAMENTO_SOLICITACAO = 1026;
    
    /**
     * Descricao do tipo de documento 'Solicitação de serviços a TI'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::TIPO_SOLICITACAO_SERVICO_TI . "') AS ...
     * 
     * @var		TIPO_SOLICITACAO_SERVICO_TI String (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_SOLICITACAO_SERVICO_TI = 'Solicitação de serviços a TI';
    
    /**
     * Id do tipo de vinculação 'Vincular SOSTI'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::ID_VINCULACAO_VINCULAR_SOSTI . "') AS ...
     * 
     * @var		ID_VINCULACAO_VINCULAR_SOSTI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_VINCULACAO_VINCULAR_SOSTI = 4;
    
    /************************************   CAIXAS  ***********************************/
    /**
     * Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA = 2;
     
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_INFRAESTRUTURA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_INFRAESTRUTURA = 3;
     
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_NOC int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_NOC = 4;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_HELPDESK = 1;
    const NIVEL_HELPDESK = 1;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_TERCEIRO_NIVEL = 1;
    const NIVEL_TERCEIRO_NIVEL = 3;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_QUARTO_NIVEL = 1;
    const NIVEL_QUARTO_NIVEL = 4;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_GESTAO_DEMANDAS_DA_INFRAESTRUTURA = 20;
    const GRUPO_GESTAO_DEMANDAS_DA_INFRAESTRUTURA = 119;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS = 21;
    const GRUPO_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS = 120;
    
    /** Id da caixa 'CAIXA DE DESENVOLVIMENTO / SUSTENTAÇÃO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR'
     * @example TO_CHAR(CAMPO, '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "') AS ...
     * 
     * @var		CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CAIXA_GESTAO_DEMANDAS_DO_NOC = 36;
    const GRUPO_GESTAO_DEMANDAS_DO_NOC = 121;
    
}
