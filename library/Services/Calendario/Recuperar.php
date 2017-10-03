<?php
/**
 * Web service para recuperar informações do calendário do TRF1
 * Para verificar as passagens de parametros do web service:
 * http://srvapphomo.trf1.gov.br/aplicacoesapoio/calendarioWS?xsd=1
 * Endereço da produção: http://prd.trf1.gov.br/aplicacoesapoio/calendarioWS?wsdl
 * Endereço do desenvolvimento: http://srvapphomo.trf1.gov.br/aplicacoesapoio/calendarioWS?wsdl
 * 
 * @author Marcelo Caixeta Rocha
 */
class Services_Calendario_Recuperar
{
    private static $client = '';
    private static $wsdl = "http://prd.trf1.gov.br/aplicacoesapoio/calendarioWS?wsdl";
    private static $options = array('soap_version' => SOAP_1_1,
                                    'encoding'     => 'UTF-8'
                                   );

    public function __construct()
    {
        ini_set("soap.wsdl_cache_enabled", 0); //** Limpa o cache
        self::$client = new Zend_Soap_Client(self::$wsdl, self::$options);
    }
    
    public function verificaDataSemExpediente($date, $abrangencia, $uf, $idSecao, $idVara)
    {
        try {
            $params = array("date"        => $date,
                            "abrangencia" => $abrangencia,
                            "uf"          => $uf,
                            "idSecao"     => $idSecao,
                            "idVara"      => $idVara
                );
            $resposta = self::$client->verificaDataSemExpediente($params);
        } catch (SoapFault $soapFault) {
            $resposta = $soapFault->getMessage();
            $resposta .= $soapFault->getMessage();
            $resposta .= htmlentities($client->getLastResponse());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequest());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequestHeaders());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastResponseHeaders());
        }
        return $resposta;
    }
    
    public function obterDiaUtilApos($date, $abrangencia, $uf, $idSecao, $idVara)
    {
        try {
            $params = array("data"        => $date,
                            "abrangencia" => $abrangencia,
                            "uf"          => $uf,
                            "idVara"      => $idSecao,
                            "idSecao"     => $idVara
                );
            $resposta = self::$client->obterDiaUtilApos($params);
        } catch (SoapFault $soapFault) {
            $resposta = $soapFault->getMessage();
            $resposta .= $soapFault->getMessage();
            $resposta .= htmlentities($client->getLastResponse());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequest());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequestHeaders());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastResponseHeaders());
        }
        return $resposta;
    }
    
    public function obterDiaUtilAntes($date, $abrangencia, $uf, $idSecao, $idVara)
    {
        try {
            $params = array("data"        => $date,
                            "abrangencia" => $abrangencia,
                            "uf"          => $uf,
                            "idVara"      => $idVara,
                            "idSecao"     => $idSecao
                );
            $resposta = self::$client->obterDiaUtilAntes($params);
        } catch (SoapFault $soapFault) {
            $resposta = $soapFault->getMessage();
            $resposta .= $soapFault->getMessage();
            $resposta .= htmlentities($client->getLastResponse());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequest());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequestHeaders());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastResponseHeaders());
        }
        return $resposta;
    }
    
    public function obterDiasUteisPeriodo($dataIni, $dataFim, $abrangencia, $uf, $idSecao, $idVara)
    {
        try {
            $params = array("dataIni"     => $dataIni,
                            "dataFim"     => $dataFim,
                            "abrangencia" => $abrangencia,
                            "uf"          => $uf,
                            "idSecao"     => $idSecao,
                            "idVara"      => $idVara
                );
            $resposta = self::$client->obterDiasUteisPeriodo($params);
        } catch (SoapFault $soapFault) {
            $resposta = $soapFault->getMessage();
            $resposta .= $soapFault->getMessage();
            $resposta .= htmlentities($client->getLastResponse());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequest());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastRequestHeaders());
            $resposta .= '<br><br><br>';
            $resposta .= htmlentities($client->getLastResponseHeaders());
        }
        return $resposta;
    }

}