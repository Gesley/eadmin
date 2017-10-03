<?php

class App_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface {

    /**
     * @var Doctrine_Table
     */
    private $_table;
    /**
     * The field name which will be the identifier (username...)
     *
     * @var string
     */
    private $_identityCol;
    /**
     * The field name which will be used for credentials (password...)
     *
     * @var string
     */
    private $_credentialCol;
    /**
     * Actual identity value (my_all_known_username)
     *
     * @var string
     */
    private $_identity;
    /**
     * Actual credential value (my_secret_password)
     *
     * @var string
     */
    private $_credential;

    public function __construct(Doctrine_Table $table, $identityCol, $credentialCol) {
        $this->_table = $table;
        $columnList = $this->_table->getColumnNames();
        //Verifique se a identidade e credenciais são um dos nomes de coluna ...
        if (!in_array($identityCol, $columnList) || !in_array($credentialCol, $columnList)) {
            throw new Zend_Auth_Adapter_Exception("Invalid Column names are given as '{$identityCol}' and '{$credentialCol}'");
        }
        $this->_credentialCol = $credentialCol; // Atribuir os nomes de coluna ...
        $this->_identityCol = $identityCol;
    }

    /**
     * @param string $i
     */
    public function setIdentity($i) {
        $this->_identity = $i;
    }

    /**
     * @param string $c
     */
    public function setCredential($c) {
        $this->_credential = $c;
    }

    /**
     * @return Zend_Auth_Result
     */
    public function authenticate() {
        //FIXME: Check if this querying actually works or not...
        $result = $this->_table
                        ->createQuery("dctrn_find")
                        ->select('plue_id_pessoa as id, plue_cd_login, plue_cd_senha')
                        ->where("{$this->_credentialCol} = ?", $this->_credential)
                        ->andWhere("{$this->_identityCol} = ?", $this->_identity)
                        ->execute(array());

        return new Zend_Auth_Result(
                $result[0]->id ? Zend_Auth_Result::SUCCESS : Zend_Auth_Result::FAILURE, //You may define different failure types, however this one is enough
                $result[0]->id ? $result[0] : null
        );
    }

    public function getResultRowObject($returnColumns = null, $omitColumns = null) {
        if (!$this->_resultRow) {
            return false;
        }

        $returnObject = new stdClass();

        if (null !== $returnColumns) {

            $availableColumns = array_keys($this->_resultRow);
            foreach ((array) $returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                }
            }
            return $returnObject;
        } elseif (null !== $omitColumns) {

            $omitColumns = (array) $omitColumns;
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }
            return $returnObject;
        } else {

            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }
            return $returnObject;
        }
    }

}