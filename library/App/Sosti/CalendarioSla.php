<?php
/**
 * Description of CalendarioSla
 *
 * @author Leonan Alves dos Anjos
 */

class App_Sosti_CalendarioSla
{
    public static $_calendario = array();
    public static $_Cache_CalendarioSla_nome_ano_x = '_Cache_CalendarioSla_';
    public static $_Services_Calendario_Recuperar = NULL;
    public static $_datainicial_anos_calendario = 2011;
    
    public function __construct()
    {
        self::$_Services_Calendario_Recuperar = new Services_Calendario_Recuperar();
        self::initCache();
    }
    
    private static function initCache()
    {
       $cache = self::getCache();
       
       $anosCalendario = self::getanosCalendario();
        foreach ($anosCalendario as $ano) {
            if ($cache->load(self::$_Cache_CalendarioSla_nome_ano_x . $ano) === false) {

                $dataInicialGeraCache_a_c = "01/01/" . $ano;
                $dataFinalGeraCache_a_c = "31/12/" . $ano;

                self::entredatasfechada($dataInicialGeraCache_a_c, $dataFinalGeraCache_a_c, 'd/m/Y');
                self::consultaEcalendario();

                $Segundosateofimdodia = self::getSegundosateofimdodia();
                /**
                 * Cache ano corrente renova todos os dias no primeiro acesso
                 * Cache de anos passados são renovados:
                 * Ano passado de 30 em 30 dias
                 * Ano retrazado 60 em 60 dias 
                 * Outros anos: e assim sucessivamente... 90,120,150...
                 * Veja mais com o método getDatasdeExpiracaoAnosCalendario()
                 */
                if ($ano - self::$_datainicial_anos_calendario > 0) {
                    $Segundosateofimdodia = $Segundosateofimdodia + ($ano - self::$_datainicial_anos_calendario + (86400 * 30) );
                }
                $cache->setLifetime($Segundosateofimdodia);
                $cache->save(self::$_calendario, self::$_Cache_CalendarioSla_nome_ano_x . $ano);
            }
        }
        
        self::$_calendario = NULL;
        foreach ($anosCalendario as $ano) {
            $aux_ano = $cache->load(self::$_Cache_CalendarioSla_nome_ano_x . $ano);
            foreach ($aux_ano as $key => $value) {
                self::$_calendario[$key] = $value; 
            }        
        }
    }
    
    public static function getDatasdeExpiracaoAnosCalendario()
    {
       $cache = self::getCache();
       $anosCalendario = self::getanosCalendario();
       $i = 0;
       foreach ($anosCalendario as $ano) {
            $cacheMetadatas = $cache->getMetadatas(self::$_Cache_CalendarioSla_nome_ano_x.$ano);
            $saida[$i++] .=  "ano: ". $ano . " Data de expiração: ". date('d/m/Y  H:i:s',$cacheMetadatas['expire']).". ";
       }
       return $saida;
    }
    
    private static function entredatasfechada($dataInicial, $dataFinal, $format = 'd/m/Y')
    {
        if(!strlen($dataInicial) == 10){
            throw new Exception('Formato Invalido', 1, null);
        }
        if(!strlen($dataFinal) == 10){
            throw new Exception('Formato Invalido', 1, null);
        }
        
        $data_inicial = mktime(0,0,0,substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
        $data_atual = mktime(0,0,0,substr($dataFinal,3,2),substr($dataFinal,0,2),substr($dataFinal,6,4));
            
        $dias = ($data_atual - $data_inicial)/86400;
        $dias = ceil($dias);
            
        $timestamp = $data_inicial;
        self::$_calendario = NULL;
        $i = 0;
        for($i = 0; $i <= $dias;$i++){
            self::$_calendario[date('d/m/Y',  strtotime("+$i day",$timestamp))] = NULL; 
        }
    }
    
    
    public static function getanosCalendario()
    {
        $dataInicial = self::$_datainicial_anos_calendario;
        $dataFinal  = date('Y');
        
        $data_inicial = mktime(0,0,0,1,1,$dataInicial);
        $timestamp = $data_inicial;
        
        $anos = array();
        $ano = (int)$dataInicial;
        $anofim = (int)$dataFinal+2;//Mais dois anos a frente do ano atual;
        
        $i = 0;
        while ($ano != $anofim){
            $ano = (int) date('Y',  strtotime("+$i year",$timestamp));
            $anos[$i] = $ano;
            $i++;
        }
        return $anos;
    }
    
    private static function consultaEcalendario()
    {
        $recuperar = self::$_Services_Calendario_Recuperar;
        foreach (self::$_calendario as $key => $value) {
            self::$_calendario[$key] = $recuperar->verificaDataSemExpediente($key, 1, NULL, NULL, NULL)->return;
        }
        /**
         * Validação para verificar a consistencia do da consulta ao e-calendario
         */
        if(array_search(NULL,self::$_calendario) != false){
            throw new Exception('Erro ao consultar o e-calendario', 1, null);
        }
    }
    
    public static function getSegundosateofimdodia()
    {
        $data_inicial = time();
        /*Dia seguinte*/
        $temp = strtotime("+1 day",time());
        $dataFinal = date('d/m/Y',$temp);
        $data_FimDia = mktime(0,0,0,substr($dataFinal,3,2),substr($dataFinal,0,2),substr($dataFinal,6,4));
        
        $segundos_ate_fim_do_dia = $data_FimDia - $data_inicial;
        return $segundos_ate_fim_do_dia;
    }
    
    public static function getCache()
    {
         $frontendOptions = array(
           'lifetime' => self::getSegundosateofimdodia(),
           'automatic_serialization' => true
        );
//        $cache_dir = APPLICATION_PATH . '/../temp';
        $cache_dir = sys_get_temp_dir();
        $backendOptions = array(
            'cache_dir' => $cache_dir 
        );
        // getting a Zend_Cache_Core object
        $cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);
        
        return $cache;
    }
    
    
    
    /**
     * Funções emuladas do webServiçe do E-CALENDÁRIO
     */
    /*****************************************************************************************/
    
    
    public function obterDiaUtilApos($data)
    {
        $indice = array_search(substr($data, 0,10), array_keys(self::$_calendario));
        $temp = array_slice(self::$_calendario,$indice);
        $i = 0;
        foreach ($temp as $key => $value) {
            if($i > 0){
                 if((self::$_calendario[$key]) == 0){
                     return $key;
                 }
            }
            $i++;
        }
    }
    
    public function obterDiaUtilAntes($data)
    {
        $temp = array_reverse(self::$_calendario);
        $indice = array_search(substr($data, 0,10), array_keys($temp));
        $temp = array_slice($temp,$indice);
        $i = 0;
        foreach ($temp as $key => $value) {
            if($i > 0){
                 if((self::$_calendario[$key]) == 0){
                     return $key;
                 }
            }
            $i++;
        }
    }
    
    public function obterDiasUteisPeriodo($dataIni, $dataFim)
    {
        $dataIni = substr($dataIni, 0,10);
        $dataFim = substr($dataFim, 0,10);
        
        $indice = array_search($dataIni, array_keys(self::$_calendario));
        $temp = array_slice(self::$_calendario,$indice);
        $diasUteis = 0;
        foreach ($temp as $key => $value) {
             if($value == 0){
                 $diasUteis++;
             }
             if(strcmp($key, $dataFim) === 0){
                     return $diasUteis;
             }
        }
        return $diasUteis;
    }
    
    public function verificaDataSemExpediente($data)
    {
        return ((int)self::$_calendario[substr($data, 0,10)] == 1)?(1):(0);
    }
    
    
    public static function getDescricaoMes($mes){
        
        switch ($mes){
            case '01': 
                return 'Janeiro';
                break;
            case '02':
                return 'Fevereiro';
                break;
            case '03':
                return 'Março';
                break;
            case '04':
                return 'Abril';
                break;
            case '05':
                return 'Maio';
                break;
            case '06':
                return 'Junho';
                break;
            case '07':
                return 'Julho';
                break;
            case '08':
                return 'Agosto';
                break;
            case '09':
                return 'Setembro';
                break;
            case '10':
                return 'Outubro';
                break;
            case '11':
                return 'Novembro';
                break;
            case '12':
                return 'Dezembro';
                break;
}
        
    }

    public function isDiaUtil($date)
    {
        $date = substr($date, 0,10);
        $match = preg_match("/\d{2}\/\d{2}\/\d{4}/i", $date);

        if($match && !self::$_calendario[$date])
            return true;
        return false;
}
}
