<?php
require_once('Abstract.php');
class Services
{
	public function __construct() 
	{}
	/*
	public static function getInstance()
    {
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }
	*/
	public function oab()
	{
		require_once('Services_Oab.php');
		return Services_Oab::getInstance();	
	}
	
	public function cjf()
	{
		require_once('Services_Cjf.php');
		return Services_Cjf::getInstance();	
	}
}

$services = new Services();//::getInstance();
//var_dump($services->oab());
echo '<pre>';
/**
 * OAB
 */
var_dump ( $services->oab()->consultarDadosCPF ( '57204179315' ) );
/*var_dump ( $services->oab()->consultarDadosPorParams ('07768940','RO','ADVOGADO' ) );*/

/**
 * CJF
 */
var_dump ( $services->cjf()->consultarDadosCPF ( '35934980149', 'trf1', 'eproc', 'consultanet' ) );
var_dump ( $services->cjf()->consultarDadosCNPJ ( '00781357000154', 'trf1', 'eproc', 'consultanet' ) );
var_dump ( $services->cjf()->consultarDadosCNPJ ( '00396253000126', 'trf1', 'eproc', 'consultanet' ) );


/*

var_dump ( Services_Cjf::consultarDadosCPF ( '00515374199', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCPF ( '69745811149', 'trf1', 'eproc', 'consultanet', $cliente ) );
var_dump ( Services_Cjf::consultarDadosCNPJ ( '03658507000125', 'trf1', 'eproc', 'consultanet' ) );

var_dump ( Services_Cjf::consultarDadosOABPorCPF ( '57204179315', 'trf1', 'eproc', 'consultanet' ) );
*/
/*
var_dump(Services_Cjf::consultarDadosOABPorCPF('85604631191', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('01722263504', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
var_dump(Services_Cjf::consultarDadosOABPorCPF('70931941172', 'trf1', 'eproc', 'consultanet', $cliente));
*/
?>