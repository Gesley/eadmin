<?php
/**
 * Realiza operações em colunas do tipo CLOB.
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class App_Clob extends Zend_Db_Table_Abstract
{
    
    private static function connect()
    {
        $params = Zend_Db_Table::getDefaultAdapter()->getConfig();
        return oci_connect($params["username"], $params["password"], $params["dbname"], 'AL32UTF8');
    }

    public static function saveClob($coluna, $tabela, $where, $clobData)
    {
        if (strlen($clobData) >= 4000) {
            $sql = "UPDATE ".$tabela." SET $coluna = :LONG_TEXT WHERE ".$where;

            $stid = oci_parse(self::connect(), $sql);
            $clob = oci_new_descriptor(self::connect(), OCI_D_LOB);

            oci_bind_by_name($stid, ':LONG_TEXT', $clob, -1, OCI_B_CLOB);
            $clob->writetemporary($clobData, OCI_TEMP_CLOB);
            $exe = oci_execute($stid, OCI_DEFAULT);

            oci_commit(self::connect());
            oci_free_statement($stid);
            oci_close(self::connect());
        } else {
            $exe = false;
        }
        return $exe;
    }
    
    public static function selectClob($coluna, $tabela, $where)
    {
        $query = "SELECT ".$coluna." FROM  ".$tabela." WHERE ".$where;
        $stmt = oci_parse(self::connect(), $query);

        if ($stmt) {
            oci_execute($stmt);
            if ($ret = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_LOBS)) {
                $ret = $ret[$coluna];
            } else {
                $ret = false;
            }
            oci_free_statement($stmt);
            oci_close(self::connect());
        } else {
            $ret = false;
        }
        return $ret;
    }
}