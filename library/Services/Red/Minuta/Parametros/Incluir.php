<?php
class Services_Red_Minuta_Parametros_Incluir implements Services_Red_Minuta_Parametros_Interface
{
	public $login; 
    public $ip;

	public function __construct()
	{
		$this->login = 'TR224PS';
		$this->ip    = '172.16.5.62';
	}
	
	public function getXml()
	{
        $doc = new DOMDocument();
		$root = $doc->createElement('root');
		$doc->appendChild($root);

		$solicitante = $doc->createElement('Solicitante');
		$root->appendChild($solicitante); 
		
        $solicitante->setAttribute( "login", $this->login );
        $solicitante->setAttribute( "ip",    $this->ip );
      
		return $doc->saveXML($root);  
	}
}
?>