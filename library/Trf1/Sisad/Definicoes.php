<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Definicoes
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
 * EX: PERFIL_RESPONSAVEL_CAIXA (ESCOPO_NOME_VARIAVEL)
 * 
 * ESCOPOS DAS CONSTANTES ABAIXO
 *  
 * TIPOS - constantes que representam o id de algum tipo
 * FASES
 * PERFIL
 * PARTE
 * CONFIDENCIALIDADE
 * 
 * Não usuado colocar 1000000000 para int e 'nenhum' para String para seguir o padrão da classe
 */
final class Trf1_Sisad_Definicoes
{
    /*     * ***********************************************************
     * TIPOS - constantes que representam o id de algum tipo
     * *********************************************************** */

    /**
     * Id do tipo de documento PROCESSO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "') AS ...
     * 
     * @var		ID_TIPO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_TIPO_PROCESSO = 152;

    const TIPO_PROCESSO_ADMINISTRATIVO = self::ID_TIPO_PROCESSO;
    const TIPO_PROCESSO_JUDICIAL = 260;
    const TIPO_PROCESSO_AVULSO = 261;

    /**
     * Descricao do tipo de documento PROCESSO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_PROCESSO_DESCRICAO . "') AS ...
     * 
     * @var		TIPO_DOCUMENTO_PROCESSO_DESCRICAO String (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_DOCUMENTO_PROCESSO_DESCRICAO = 'Processo administrativo';

    /**
     * Id do tipo de documento DESPACHO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_TIPO_DOCUMENTO_DESPACHO . "') AS ...
     * 
     * @var		ID_TIPO_DOCUMENTO_DESPACHO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_DOCUMENTO_DESPACHO = 39;

    /**
     * Descricao do tipo de documento DESPACHO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::TIPO_DOCUMENTO_DESPACHO_DESCRICAO . "') AS ...
     * 
     * @var		TIPO_DOCUMENTO_DESPACHO_DESCRICAO String (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_DOCUMENTO_DESPACHO_DESCRICAO = 'DESPACHO';

    /**
     * Id do tipo de documento MINUTA
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_TIPO_DOCUMENTO_MINUTA . "') AS ...
     * 
     * @var		ID_TIPO_DOCUMENTO_MINUTA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_DOCUMENTO_MINUTA = 230;

    /**
     * Id do tipo de documento Solicitação de serviços a TI
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::TIPO_SOLICITACAO_SERVICO . "') AS ...
     * 
     * @var		TIPO_SOLICITACAO_SERVICO_TI int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TIPO_SOLICITACAO_SERVICO = 160;

    /**
     * Id do tipo de vinculação Anexar
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR . "') AS ...
     * 
     * @var		ID_VINCULACAO_ANEXAR int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_VINCULACAO_ANEXAR = 1;

    /**
     * Id do tipo de vinculação Apensar
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "') AS ...
     * 
     * @var		ID_VINCULACAO_APENSAR int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_VINCULACAO_APENSAR = 2;

    /**
     * Id do tipo de vinculação Vincular
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "') AS ...
     * 
     * @var		ID_VINCULACAO_VINCULAR int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_VINCULACAO_VINCULAR = 3;

    /**
     * Id do tipo de vinculação Minuta
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::ID_VINCULACAO_MINUTA . "') AS ...
     * 
     * @var		ID_VINCULACAO_MINUTA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const ID_VINCULACAO_MINUTA = 5;

    /*     * ***********************************************************
     * PERFIS
     * *********************************************************** */

    /**
     * Perfil: RESPONSÁVEL PELA CAIXA DA UNIDADE
     * 
     * @var		PERFIL_RESPONSAVEL_UNIDADE int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const PERFIL_RESPONSAVEL_UNIDADE = 9;

    /**
     * Perfil: CORREGEDORIA
     * 
     * @var		PERFIL_CORREGEDORIA_DSV int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const PERFIL_CORREGEDORIA_DSV = 36;

    /**
     * Perfil: CORREGEDORIA
     * 
     * @var		PERFIL_CORREGEDORIA_PRODUCAO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const PERFIL_CORREGEDORIA_PRODUCAO = 38;

    /*     * ***********************************************************
     * PARTE
     * *********************************************************** */

    /**
     * Parte: Vista
     * 
     * @var		PARTE_VISTA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const PARTE_VISTA = 3;

    /**
     * Parte: Parte
     * 
     * @var		PARTE_PARTE int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const PARTE_PARTE = 1;

    /*     * ***********************************************************
     * FASES
     * *********************************************************** */

    /**
     * Fase: ALTERAÇÃO DE METADADOS
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_ALTERACAO_DE_METADADOS . "') AS ...
     * 
     * @var		FASE_ALTERACAO_DE_METADADOS int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_ALTERACAO_DE_METADADOS = 1079;

    /**
     * Fase: DESPACHO SISAD
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_DESPACHO_SISAD . "') AS ...
     * 
     * @var		FASE_DESPACHO_SISAD int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DESPACHO_SISAD = 1040;

    /**
     * Fase: DESPACHO SISAD
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_DESPACHO_SISAD . "') AS ...
     * 
     * @var		FASE_DESPACHO_SISAD int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_PARECER_SISAD = 1011;

    /**
     * Fase: VINCULAR DOCUMENTO À PROCESSO 
     * 
     * @var		FASE_VINCULA_DOCUMENTO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_VINCULA_DOCUMENTO_PROCESSO = 1067;

    /**
     * Fase: VINCULAR PROCESSO A PROCESSO
     * 
     * @var		FASE_VINCULA_PROCESSO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_VINCULA_PROCESSO_PROCESSO = 1030;

    /**
     * Fase: DESVINCULAR PROCESSO
     * 
     * @var		FASE_VINCULA_PROCESSO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DESVINCULAR_PROCESSO_PROCESSO = 1080;

    /**
     * Fase: VINCULAR DOCUMENTO A DOCUMENTO
     * 
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_VINCULA_DOCUMENTO_DOCUMENTO = 1031;

    /**
     * Fase: AUTUAÇÃO DE PROCESSO ADMINISTRATIVO
     * 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_AUTUACAO_PROCESSO . "') AS ...
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_AUTUACAO_PROCESSO = 1020;

    /**
     * Fase: ADIÇÃO DE DOCUMENTOS A PROCESSO
     * 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_ADICAO_DOCUMENTO_PROCESSO . "') AS ...
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_ADICAO_DOCUMENTO_PROCESSO = 1023;

    /**
     * Fase: REMOVER DOCUMENTO DO PROCESSO
     * 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_REMOVER_DOCUMENTO_PROCESSO . "') AS ...
     * @var		FASE_REMOVER_DOCUMENTO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_REMOVER_DOCUMENTO_PROCESSO = 1076;

    /**
     * Fase: ANEXAR DOCUMENTO À DOCUMENTO
     * 
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_ANEXAR_DOCUMENTO_DOCUMENTO = 1068;

    /**
     * Fase: ANEXAR PROCESSO À PROCESSO
     * 
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_ANEXAR_PROCESSO_PROCESSO = 1069;

    /**
     * Fase: DESANEXAR PROCESSO
     * 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_DESANEXAR_PROCESSO_PROCESSO . "') AS ...
     * @var		FASE_DESANEXAR_PROCESSO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DESANEXAR_PROCESSO_PROCESSO = 1077;

    /**
     * Fase: APENSAR PROCESSO À PROCESSO
     * 
     * @var		FASE_VINCULA_DOCUMENTO_DOCUMENTO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_APENSAR_PROCESSO_PROCESSO = 1070;

    /**
     * Fase: DESAPENSAR PROCESSO
     * 
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::FASE_DESAPENSAR_PROCESSO_PROCESSO . "') AS ...
     * @var		FASE_DESAPENSAR_PROCESSO_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DESAPENSAR_PROCESSO_PROCESSO = 1078;

    /**
     * Fase: DISTRIBUIÇÃO AUTOMÁTICA DE PROCESSOS
     * 
     * @var		FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO = 1045;

    /**
     * Fase: DISTRIBUIÇÃO MANUAL DE PROCESSOS
     * 
     * @var		FASE_DISTRIBUICAO_MANUAL_PROCESSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DISTRIBUICAO_MANUAL_PROCESSO = 1046;

    /**
     * Fase: DISTRIBUIÇÃO AUTOMÁTICA DE PROCESSOS
     * 
     * @var		FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO_DESCRICAO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DISTRIBUICAO_AUTOMATICA_PROCESSO_DESCRICAO = 'DISTRIBUIÇÃO AUTOMÁTICA DE PROCESSOS';

    /**
     * Fase: DISTRIBUIÇÃO MANUAL DE PROCESSOS
     * 
     * @var		FASE_DISTRIBUICAO_MANUAL_PROCESSO_DESCRICAO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const FASE_DISTRIBUICAO_MANUAL_PROCESSO_DESCRICAO = 'DISTRIBUIÇÃO MANUAL DE PROCESSOS';

    /**
     * Fase: ENCAMINHAR DOCUMENTOS/PROCESSOS
     * 
     * @var     FASE_ENCAMINHAR_DOC_PROC int (constant)
     * @author  Dayane O. Freire
     */
    const FASE_ENCAMINHAR_DOC_PROC = 1010;

    /**
     * Fase: ASSINATURA POR SENHA
     * 
     * @var     FASE_ASSINATURA_POR_SENHA int (constant)
     * @author  Leidison Siqueira Barbosa
     */
    const FASE_ASSINATURA_POR_SENHA = 1018;

    /**
     * Fase: ASSINATURA POR CERTIFICADO DIGITAL
     * 
     * @var     FASE_ASSINATURA_POR_CERTIFICADO_DIGIRAL int (constant)
     * @author  Leidison Siqueira Barbosa
     */
    const FASE_ASSINATURA_POR_CERTIFICADO_DIGIRAL = 1018;

    /*     * ***********************************************************
     * CONFIDENCIALIDADE
     * *********************************************************** */

    /**
     * Fase: PUBLICO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_PUBLICO int (constant)
     * @author	Dayane O. Freire
     */
    const CONFIDENCIALIDADE_PUBLICO = 0;

    /**
     * Fase: RESTRITO AS PARTES
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_AS_PARTES . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_RESTRITO_AS_PARTES int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CONFIDENCIALIDADE_RESTRITO_AS_PARTES = 1;

    /**
     * Fase: RESTRITO A SUBGRUPO INTRANET
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_A_SUBGRUPO_INTRANET . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_RESTRITO_A_SUBGRUPO_INTRANET int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CONFIDENCIALIDADE_RESTRITO_A_SUBGRUPO_INTRANET = 2;

    /**
     * Fase: AS PARTES SEGREDO DE JUSTIÇA
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_AS_PARTES_SEGREDO_JUSTICA . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_RESTRITO_AS_PARTES_SEGREDO_JUSTICA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CONFIDENCIALIDADE_RESTRITO_AS_PARTES_SEGREDO_JUSTICA = 3;

    /**
     * Fase: AO SUBGRUPO SIGILOSO
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_AO_SUBGRUPO_SIGILOSO . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_AO_SUBGRUPO_SIGILOSO int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CONFIDENCIALIDADE_AO_SUBGRUPO_SIGILOSO = 4;

    /**
     * Fase: CORREGEDORIA
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_CORREGEDORIA . "') AS ...
     * 
     * @var		CONFIDENCIALIDADE_CORREGEDORIA int (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CONFIDENCIALIDADE_CORREGEDORIA = 5;

    /**
     * AUDITORIA: INSERIR
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::AUDITORIA_INSERIR . "') AS ...
     * 
     * @var		AUDITORIA_INSERIR string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const AUDITORIA_INSERIR = 'I';

    /**
     * AUDITORIA: EXCLUIR
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::AUDITORIA_EXCLUIR . "') AS ...
     * 
     * @var		AUDITORIA_INSERIR string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const AUDITORIA_EXCLUIR = 'E';

    /**
     * AUDITORIA: ALTERAR
     * @example TO_CHAR(CAMPO, '" . Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR . "') AS ...
     * 
     * @var		AUDITORIA_INSERIR string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const AUDITORIA_ALTERAR = 'A';

    /**
     * CACHE: TODOS OS TIPOS DE DOCUMENTO VALIDOS
     * @example $dados = Trf1_Sisad_Definicoes::CACHE_TIPO_DOCUMENTO
     * 
     * @var		CACHE_TIPO_DOCUMENTO string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_TIPO_DOCUMENTO = 'tipo_documento';

    /**
     * CACHE: TODOS OS TIPOS DE DOCUMENTO VALIDOS
     * @example $dados = Trf1_Sisad_Definicoes::CACHE_TIPO_DOCUMENTO
     * 
     * @var		CACHE_TIPO_DOCUMENTO string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_TIPO_SITUACAO_DOCUMENTO = 'tipo_situacao_documento';

    /**
     * CACHE: TODOS OS TIPOS DE CONFIDENCIALIDADE ADMINISTRATIVA VALIDAS
     * @example $dados = Trf1_Sisad_Definicoes::CACHE_TIPO_CONFIDENCIALIDADE_ADMINISTRATIVA
     * 
     * @var		CACHE_TIPO_CONFIDENCIALIDADE_ADMINISTRATIVA string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_TIPO_CONFIDENCIALIDADE_ADMINISTRATIVA = 'tipo_confidencialidade_administrativa';

    /**
     * CACHE: TODOS OS PCTTS VALIDOS
     * @example $dados = Trf1_Sisad_Definicoes::CACHE_PCTT
     * 
     * @var		CACHE_PCTT string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_PCTT = 'pctt';

    /**
     * TEMPO: 24 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Sisad_Definicoes::TEMPO_24HORAS_EM_SEGUNDOS
     * 
     * @var		TEMPO_24HORAS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_24HORAS_EM_SEGUNDOS = 86400;

    /**
     * TEMPO: 12 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_12HORAS_EM_SEGUNDOS
     * 
     * @var		TEMPO_12HORAS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_12HORAS_EM_SEGUNDOS = 43200;

    /**
     * TEMPO: 6 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_6HORAS_EM_SEGUNDOS
     * 
     * @var		TEMPO_6HORAS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_6HORAS_EM_SEGUNDOS = 21600;

    /**
     * TEMPO: 3 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_3HORAS_EM_SEGUNDOS
     * 
     * @var		TEMPO_3HORAS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_3HORAS_EM_SEGUNDOS = 10800;

    /**
     * TEMPO: 1 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_1HORAS_EM_SEGUNDOS
     * 
     * @var		TEMPO_1HORAS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_1HORAS_EM_SEGUNDOS = 3600;

    /**
     * TEMPO: 30 MINUTOS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_30MINUTOS_EM_SEGUNDOS
     * 
     * @var		TEMPO_30MINUTOS_EM_SEGUNDOS string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const TEMPO_30MINUTOS_EM_SEGUNDOS = 1800;

}
