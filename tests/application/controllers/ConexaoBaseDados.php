<?php

/**
 * Classe responsável por criar conexão com banco de dados.
 * @example Ester esta classe, chamar o metodo setUp() depois getConnection() e por fim fazer a autenticação.
 * @author Equipe desenvolvimento Stefanini <www.stefanini.com>
 * @copyright (c) 2015 TRF1.
 * @version 1.1
 */
class ConexaoBaseDados extends Zend_Test_PHPUnit_DatabaseTestCase {

    /**
     * Zend_Application
     * @var Zend_Application 
     */
    public $_application;

    /**
     * Connection
     * @var Zend_Test_PHPUnit_Db_Connection
     */
    private $_connection;

    /**
     * Returns the test database connection.
     * @link http://framework.zend.com/wiki/display/ZFPROP/Zend_Test_PHPUnit_Database+-+Benjamin+Eberlei
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection () {

        if ($this->_connection === null) {

            $Resources = $this->_application->getOption("resources");

            $conn = Zend_Db::factory($Resources["db"]["adapter"], $Resources["db"]["params"]);

            $this->_connection = $this->createZendDbConnection($conn, $Resources["db"]["params"]["dbname"]);
            Zend_Db_Table_Abstract::setDefaultAdapter($conn);
        }

        return $this->_connection;
    }

    /**
     * Returns the test dataset.
     * @link http://framework.zend.com/wiki/display/ZFPROP/Zend_Test_PHPUnit_Database+-+Benjamin+Eberlei
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet () {

        return $this->createFlatXMLDataSet(__DIR__ . "/seed_data.xml");
    }

    /**
     * Setup
     */
    public function setUp () {

        $this->_application = new Zend_Application(
            APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini'
        );
    }

}
