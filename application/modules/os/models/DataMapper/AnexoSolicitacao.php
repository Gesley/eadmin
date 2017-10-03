<?php

class Os_Model_DataMapper_AnexoSolicitacao extends Zend_Db_Table_Abstract
{
    public static function listAll($arraySolicit)
    {
        $anexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        $doc = new Application_Model_DbTable_SadTbDocmDocumento();
        foreach ($arraySolicit as $K=>$a) {
            $key = $doc->fetchRow("DOCM_ID_DOCUMENTO = $a")->toArray();
            $vlc[$key["DOCM_NR_DOCUMENTO"]] = $anexAnexo->fetchAll("ANEX_ID_DOCUMENTO = $a", 'ANEX_DH_FASE DESC')->toArray();
            foreach ($vlc[$key["DOCM_NR_DOCUMENTO"]]  as $i=>$s) {
                $ext = explode('.', $s["ANEX_NM_ANEXO"]);
                $vlc[$key["DOCM_NR_DOCUMENTO"]][$i]['EXT'] = end($ext);
            }
        }
        return $vlc;
    }
}

