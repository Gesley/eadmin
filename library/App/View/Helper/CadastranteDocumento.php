<?php
/**
 * Esta classe serve para mostrar o nome do usuário que cadastrou determinado documento
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta[at]trf1.jus.br
 * @license Free to use - no strings.
 */
class App_View_Helper_CadastranteDocumento extends Zend_View_Helper_Abstract
{ 
   /**
     * Cadastrante do documento
     * @var string
     */
    private static $_cadastrante = '';
    /**
     * Método Principal
     * @param string $documento Valor passado para verificar quem cadastrou
     * @param string $_cadastrante O nome de quem cadastrou é a saída
     * @return string Matricula e nome de quem cadastrou concatenados com -
     */
    public function cadastrante($documento)
    {
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        self::$_cadastrante = $mapperDocumento->getCadastranteDoc($documento);
        return self::$_cadastrante;
    }
 
}