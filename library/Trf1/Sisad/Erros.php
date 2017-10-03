<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Definicoes
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Pedro Henrique dos Santos Correia
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
 */
final class Trf1_Sisad_Erros {
    /*     * ***********************************************************
     * TIPOS - Constantes referentes a erros do sistema
     * *********************************************************** */
    /**
     * Erro ocorre quando o usuário tenta cadastrar anexos sem o cadastramento de um documento principal
     */
    const CAD_ANEXOS_SEM_PRINCIPAL = 'Não é possivel anexar documentos sem um documento principal.';

    

}
