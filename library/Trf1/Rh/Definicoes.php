<?php

/**
 * @category	TRF1
 * @package     Trf1_Rh_Definicoes
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Dayane Oliveira Freire
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
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

 * 
 */
final class Trf1_Rh_Definicoes
{
    /*     * ***********************************************************
     * TIPOS - constantes que representam o tipo da lotação
     * *********************************************************** */

    /**
     * Tipo de lotação: Seção Judiciária
     * 
     * @var	TIPO_LOTA_SECAO_JUDICIARIA int (constant)
     * @author	Dayane O. Freire
     */
    const TIPO_LOTA_SECAO_JUDICIARIA = 1;

    /**
     * Tipo de lotação: Subseção Judiciária
     * 
     * @var	TIPO_LOTA_SUBSECAO_JUDICIARIA int (constant)
     * @author	Dayane O. Freire
     */
    const TIPO_LOTA_SUBSECAO_JUDICIARIA = 2;

    /**
     * Tipo de lotação: Vara
     * 
     * @var	TIPO_LOTA_VARA int (constant)
     * @author	Dayane O. Freire
     */
    const TIPO_LOTA_VARA = 3;

    /**
     * Tipo de lotação: Tribunal Regional Federal
     * 
     * @var	TIPO_LOTA_TRF int (constant)
     * @author	Dayane O. Freire
     */
    const TIPO_LOTA_TRF = 9;

    /**
     * Tipo de lotação: Turma
     * 
     * @var	TIPO_LOTA_TURMA int (constant)
     * @author	Dayane O. Freire
     */
    const TIPO_LOTA_TURMA = 19;

    /**
     * CACHE: TRF E SECOES
     * @example $dados = Trf1_Rh_Definicoes::CACHE_TRF_E_SECOES
     * 
     * @var		CACHE_TRF_E_SECOES string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_TRF_E_SECOES = 'trf_secao';

    /**
     * CACHE: SUBSECOES
     * @example $dados = Trf1_Rh_Definicoes::CACHE_SUBSECOES
     * 
     * @var		CACHE_SUBSECOES string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_SUBSECOES = 'subsecao';

    /**
     * CACHE: UNIDADES POR SECAO
     * @example $dados = Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SUBSECAO
     * 
     * @var		CACHE_UNIDADES_POR_SUBSECAO string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_UNIDADES_POR_SUBSECAO = 'unidade_subsecao';

    /**
     * CACHE: UNIDADES POR SIGLA DA SECAO
     * @example $dados = Trf1_Rh_Definicoes::CACHE_UNIDADES_POR_SUBSECAO
     * 
     * @var		CACHE_UNIDADES_POR_SIGLA_SECAO string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_UNIDADES_POR_SIGLA_SECAO = 'unidade_sigla_secao';

    /**
     * CACHE: TODAS UNIDADES
     * @example $dados = Trf1_Rh_Definicoes::CACHE_UNIDADES
     * 
     * @var		CACHE_UNIDADES string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_UNIDADES = 'unidade';

    /**
     * CACHE: PESSOAS FISICAS DO TRF 1
     * @example $dados = Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1
     * 
     * @var		CACHE_PESSOAS_FISICAS_TRF1 string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_PESSOAS_FISICAS_TRF1 = 'pessoas_fisicas_trf1';

    /**
     * CACHE: CACHE PESSOAS JURIDICAS DO TRF1
     * @example $dados = Trf1_Rh_Definicoes::CACHE_PESSOAS_JURIDICAS_TRF1
     * 
     * @var		CACHE_PESSOAS_JURIDICAS_TRF1 string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_PESSOAS_JURIDICAS_TRF1 = 'pessoas_juridicas_trf1';

    /**
     * CACHE: PESSOAS JURIDICAS DO TRF 1 AGRUPADAS POIR UNIDADES DE LOTACAO
     * @example $dados = Trf1_Rh_Definicoes::CACHE_PESSOAS_FISICAS_TRF1_POR_UNIDADE_DE_LOTACAO
     * 
     * @var		CACHE_PESSOAS_FISICAS_TRF1_POR_UNIDADE_DE_LOTACAO string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_PESSOAS_FISICAS_TRF1_POR_UNIDADE_DE_LOTACAO = 'pessoas_fisicas_trf1_por_unidade_de_lotacao';

    /**
     * CACHE: CAIXAS AGRUPADAS POR RESPONSAVEL
     * @example $dados = Trf1_Rh_Definicoes::CACHE_CAIXAS_AGRUPADAS_POR_RESPONSAVEL
     * 
     * @var		CACHE_CAIXAS_AGRUPADAS_POR_RESPONSAVEL string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_UNIDADES_AGRUPADAS_POR_RESPONSAVEL = 'unidades_agrupadas_por_responsavel';

    /**
     * CACHE: RESONSAVEIS AGRUPADOS POR CAIXA
     * @example $dados = Trf1_Rh_Definicoes::CACHE_RESONSAVEIS_AGRUPADOS_POR_CAIXA
     * 
     * @var		CACHE_RESONSAVEIS_AGRUPADOS_POR_CAIXA string (constant)
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    const CACHE_RESONSAVEIS_AGRUPADOS_POR_UNIDADE = 'resonsaveis_agrupados_por_unidades';

    /**
     * TEMPO: 24 HORAS EM SEGUNDOS
     * @example $SEGUNDOS = Trf1_Rh_Definicoes::TEMPO_24HORAS_EM_SEGUNDOS
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
