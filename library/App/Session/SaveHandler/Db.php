<?php
/**
 * Salvar a sessão da aplicação no banco de dados
 * A classe original era para gerenciar a sessão no Mysql e foi modificada para
 * gerenciar no Oracle por: Marcelo Caixeta Rocha
 */
class App_Session_SaveHandler_Db implements Zend_Session_SaveHandler_Interface
{
    private static $_conn = array();
    private static $_table = array();
    private static $_db = '';
    private static $_lifeTime = '';

    public function __construct($conn, $tabela)
    {
        self::$_conn = $conn;
        self::$_table = $tabela;
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc'));
        register_shutdown_function('session_write_close');
    }

    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::close()
     */
    public function close()
    {
        // todo Auto-generated method stub
        $this->gc(ini_get('session.gc_maxlifetime'));
        return OCILogoff(self::$_db);
    }

    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::destroy()
     */
    public function destroy($id) 
    {
        // todo Auto-generated method stub
        $sql = "DELETE FROM ".self::$_table["saveHandler"]["options"]["name"].
               " WHERE ".self::$_table["saveHandler"]["options"]["primary"][0]." = '".$id."'";
        $statement = OCIParse(self::$_db, $sql);
        return OCIExecute($statement);
    }

    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::gc()
     */
    public function gc($maxlifetime) 
    {
        $sql = "DELETE FROM ".self::$_table["saveHandler"]["options"]["name"].
               " WHERE ".self::$_table["saveHandler"]["options"]["lifetimeColumn"].
               " < '".(time() - $maxlifetime)."'";
        $statement = OCIParse(self::$_db, $sql);
        return OCIExecute($statement);
    }
    
    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::open()
     */
    public function open($save_path, $name) 
    {
        // todo Auto-generated method stub
        self::$_lifeTime = get_cfg_var("session.gc_maxlifetime");
        try {
            self::$_db = OCILogon(self::$_conn["params"]["username"], self::$_conn["params"]["password"], self::$_conn["params"]["dbname"], 'AL32UTF8');
        } catch (Exception $e) {
            self::$_db = $e;
        }
        return self::$_db;
    }

    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::read()
     */
    public function read($id) 
    {
        /* Build and execute the database query. */
        $query = "SELECT ".self::$_table["saveHandler"]["options"]["dataColumn"].
                 " FROM  ".self::$_table["saveHandler"]["options"]["name"].
                 " WHERE ".self::$_table["saveHandler"]["options"]["primary"][0]." = '".$id."'";
        $stmt = OCIParse(self::$_db, $query);

        if ($stmt) {
            $result = OCIExecute($stmt);
            if (OCIFetchInto($stmt,$result, OCI_ASSOC+OCI_RETURN_LOBS)) {
                $ret = $result['SEAD_DS_DATA'];
            } else {
                $ret = false;
            }
            OCIFreeStatement($stmt);
        } else {
            $ret = false;
        }
        return $ret;
    }

    /* (non-PHPdoc)
     * @see library/Zend/Session/SaveHandler/Zend_Session_SaveHandler_Interface::write()
     */
    public function write($id, $data) 
    {
        $query = "MERGE INTO ".self::$_table["saveHandler"]["options"]["name"]." M ";
        $query .= "USING (SELECT '".$id."' AS ID, :TIME AS LIFETIME, :DADOS AS DATAVAL FROM DUAL) N ";
        $query .= "ON (M.".self::$_table["saveHandler"]["options"]["primary"][0]." = N.ID ) ";
        $query .= "WHEN MATCHED THEN ";
        $query .= "UPDATE SET M.".self::$_table["saveHandler"]["options"]["lifetimeColumn"]." = N.LIFETIME, "; 
        $query .= "M.".self::$_table["saveHandler"]["options"]["dataColumn"]." = N.DATAVAL ";
        $query .= "WHEN NOT MATCHED THEN INSERT( ".self::$_table["saveHandler"]["options"]["primary"][0].", ";
        $query .= self::$_table["saveHandler"]["options"]["lifetimeColumn"].", ";
        $query .= self::$_table["saveHandler"]["options"]["dataColumn"]." ) ";
        $query .= "VALUES(N.ID, N.LIFETIME, N.DATAVAL) ";

        $stmt = OCIParse(self::$_db, $query);
        $clob = OCINewDescriptor(self::$_db, OCI_D_LOB);
//        OCIBindByName($stmt, ':ID', $id, CHAR);
        OCIBindByName($stmt, ':TIME', time());
        OCIBindByName($stmt, ':DADOS', $clob, -1, OCI_B_CLOB);
        $clob->WriteTemporary($data, OCI_TEMP_CLOB);
        $exe = OCIExecute($stmt, OCI_DEFAULT);

        if ($exe === true) {
            $ret = true;
            OCICommit(self::$_db);
	} else {
            $ret = false;
            OCIRollback(self::$_db);
	}
        $clob->close();
	$clob->free();
	OCIFreeStatement($stmt);
	return $ret;
    }

    public function __destruct() 
    {
        session_write_close();
    }

}