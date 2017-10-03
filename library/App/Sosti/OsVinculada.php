<?php
/**
 * Verifica se existe uma OS criada pelo id do documento informado
 *
 * @author Marcelo Caixeta Rocha
 */


class App_Sosti_OsVinculada 
{
    
    public static function getVinculada($idDocumento)
    {
        $osVinculada = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $numeroOs = $osVinculada->getDocPrincipal($idDocumento);
        if ($numeroOs != false) {
            return true;
        } else {
            return false;
        }
    }
}
