<?php

/**
 * Description of TimeInterval
 *
 * @author Leonan Alves dos Anjos
 */
/**
 * Include needed Date classes
 */
require_once 'Zend/Date/DateObject.php';
require_once 'App/Sosti/CalendarioSla.php';


class App_TimeInterval  extends Zend_Date_DateObject{

    protected static $_data_atual = '';
    
    public static $_ORIGEM_DB = 'ORIGEM_DB';
    public static $_ORIGEM_SERVER = 'ORIGEM_SERVER';
    //public $_CalendarioSla = NULL;
    
    public static function sysdateDb()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy HH24:MI:SS') DATAHORA FROM DUAL");
        $datahora_aux =  $stmt->fetch();
        return $datahora_aux["DATAHORA"];
    }
      
    public function __construct($date_origem = 'ORIGEM_DB')
    {
        if($date_origem === self::$_ORIGEM_DB){
            self::$_data_atual = new Zend_Date($this->sysdateDb(),'dd/MM/yyyyHH:mm:ss');
        }else if(self::$_ORIGEM_SERVER){
            self::$_data_atual = Zend_Date::now();
        }
    }
    
    /**
     * @param string de data ou Zend_Date objeto.
     * 
     */
    public static function interval($date, $format = 'dd/MM/yyyyHH:mm:ss')
    {
        if (is_string($date)) {
             $date_ob_referente = new Zend_Date($date, $format);
        } elseif ($date instanceof Zend_Date) {
            $date_ob_referente = $date;
        }
        
        $date_ob_atual = self::$_data_atual;
        
        $date = $date_ob_referente->getUnixTimestamp();
        $date_atual = $date_ob_atual->getUnixTimestamp();
        
        unset($date_ob_atual);
        unset($date_ob_referente);
        
        $dias = ($date_atual - $date)/86400;

        $n_dias = floor($dias);
        $frac_dias =  $dias - $n_dias;
        
        $horas = $frac_dias*24;
        
        $n_horas = floor($horas);
        //Zend_Debug::dump($n_horas,'n horas');
        $frac_horas =  $horas - $n_horas;
        //Zend_Debug::dump($frac_horas,'frac_horas');
        
        
        $minutos = $frac_horas*60;
        
        $n_minutos = floor($minutos);
        ///Zend_Debug::dump($n_minutos,'n minutos');
        $frac_minutos =  $minutos - $n_minutos;
        //Zend_Debug::dump($frac_minutos,'frac_minutos');
        
        
        $segundos = $frac_minutos*60;
        
        $n_segundos = floor($segundos);
        //Zend_Debug::dump($n_segundos,'n segundos');
        $frac_segundos =  $segundos - $n_segundos;
        //Zend_Debug::dump($frac_segundos,'frac_segundos');
        
        return $n_dias.'D '.$n_horas.'h '.$n_minutos.'m '.$n_segundos.'s';
    }
    
    /**
     * Calcula o tempo total entre duas datas
     */
    public static function tempoTotal($data_inicial, $data_final, $format = 'dd/MM/yyyyHH:mm:ss')
    {
        if (is_string($data_final)) {
             $date_ob_final = new Zend_Date($data_final, $format);
        } elseif ($data_final instanceof Zend_Date) {
            $date_ob_final = $data_final;
        }
        
        if (is_string($data_inicial)) {
             $date_ob_inicial = new Zend_Date($data_inicial, $format);
        } elseif ($data_inicial instanceof Zend_Date) {
            $date_ob_inicial = $data_inicial;
        }
        
        $data_final = $date_ob_final->getUnixTimestamp();
        $data_inicial = $date_ob_inicial->getUnixTimestamp();
        
        unset($date_ob_inicial);
        unset($date_ob_final);
        
        $dias = ($data_final - $data_inicial)/86400;

        $n_dias = floor($dias);
        //Zend_Debug::dump($n_dias,'n dias');
        $frac_dias =  $dias - $n_dias;
        //Zend_Debug::dump($frac_dias,'frac_dias');
        
        $horas = $frac_dias*24;
        
        $n_horas = floor($horas);
        //Zend_Debug::dump($n_horas,'n horas');
        $frac_horas =  $horas - $n_horas;
        //Zend_Debug::dump($frac_horas,'frac_horas');
        
        
        $minutos = $frac_horas*60;
        
        $n_minutos = floor($minutos);
        ///Zend_Debug::dump($n_minutos,'n minutos');
        $frac_minutos =  $minutos - $n_minutos;
        //Zend_Debug::dump($frac_minutos,'frac_minutos');
        
        
        $segundos = $frac_minutos*60;
        
        $n_segundos = floor($segundos);
        //Zend_Debug::dump($n_segundos,'n segundos');
        $frac_segundos =  $segundos - $n_segundos;
        //Zend_Debug::dump($frac_segundos,'frac_segundos');
        
        return $n_dias.'D '.$n_horas.'h '.$n_minutos.'m '.$n_segundos.'s';
    }
    
    /**
     * Calcula o tempo total entre duas datas
     */
    public static function tempoTotalSegundos($data_inicial, $data_final, $format = 'dd/MM/yyyyHH:mm:ss')
    {
        if (is_string($data_final)) {
             $date_ob_final = new Zend_Date($data_final, $format);
        } elseif ($data_final instanceof Zend_Date) {
            $date_ob_final = $data_final;
        }
        
        if (is_string($data_inicial)) {
             $date_ob_inicial = new Zend_Date($data_inicial, $format);
        } elseif ($data_inicial instanceof Zend_Date) {
            $date_ob_inicial = $data_inicial;
        }
        
        $data_final = $date_ob_final->getUnixTimestamp();
        $data_inicial = $date_ob_inicial->getUnixTimestamp();
        
        return $data_final - $data_inicial;
    }
    
    /**
     * Calcula o tempo total entre duas datas com o dia util de 13 horas
     */
    public static function FormataSaidaSegundos($segundos,$horasUteis = 13)
    {
        $segundos = abs($segundos);
        $dias = $segundos/46800;
        $n_dias = floor($dias);
        $frac_dias =  $dias - $n_dias;
        $horas = $frac_dias*$horasUteis;
        $n_horas = floor($horas);
        $frac_horas =  $horas - $n_horas;
        $minutos = $frac_horas*60;
        $n_minutos = floor($minutos);
        $frac_minutos =  $minutos - $n_minutos;
        $segundos = $frac_minutos*60;
        $n_segundos = floor($segundos);
        $frac_segundos =  $segundos - $n_segundos;
        return $n_dias.'D '.$n_horas.'h '.$n_minutos.'m '.$n_segundos.'s';
    }
    
    /**
     * Calcula o tempo total entre duas datas
     */
    public static function tempoTotalDias($data_inicial, $data_final)
    {
        $format = 'dd/MM/yyyyHH:mm:ss';
        if (is_string($data_final)) {
             $date_ob_final = new Zend_Date($data_final, $format);
        } elseif ($data_final instanceof Zend_Date) {
            $date_ob_final = $data_final;
        }
        if (is_string($data_inicial)) {
             $date_ob_inicial = new Zend_Date($data_inicial, $format);
        } elseif ($data_inicial instanceof Zend_Date) {
            $date_ob_inicial = $data_inicial;
        }
        $data_final = $date_ob_final->getUnixTimestamp();
        $data_inicial = $date_ob_inicial->getUnixTimestamp();
        unset($date_ob_inicial);
        unset($date_ob_final);
        $dias = ($data_final - $data_inicial)/86400;
        return $dias;
    }
    
    /**
     * Calcula a diferença entre dua horas
     */
    public static function difDeHoras($hIni, $hFinal)
    {        
        // Separa á hora dos minutos
        $hIni = explode(':', $hIni);
        $hFinal = explode(':', $hFinal);

        // Converte a hora e minuto para segundos
        $hIni = (60 * 60 * $hIni[0]) + (60 * $hIni[1]);
        $hFinal = (60 * 60 * $hFinal[0]) + (60 * $hFinal[1]);

        // Verifica se a hora final é maior que a inicial
        if(!($hIni < $hFinal)) {
            return false;
        }

        // Calcula diferença de horas
        $difDeHora = $hFinal - $hIni;

        //Converte os segundos para Hora e Minuto
        $tempo = $difDeHora / (60 * 60);
        $tempo = explode('.', $tempo); // Aqui divide o restante da hora, pois se não for inteiro, retornará um decimal, o minuto, será o valor depois do ponto.
        $hora = $tempo[0];
        @$minutos = (float) (0) . '.' . $tempo[1]; // Aqui forçamos a conversão para float, para não ter erro.
        $minutos = $minutos * 60; // Aqui multiplicamos o valor que sobra que é menor que 1, por 60, assim ele retornará o minuto corretamente, entre 0 á 59 minutos.
        $minutos = explode('.', $minutos); // Aqui damos explode para retornar somente o valor inteiro do minuto. O que sobra será os segundos
        $minutos = $minutos[0];
        //Aqui faz uma verificação, para retornar corretamente as horas, mas se não quiser, só mandar retornar a variavel hora e minutos
        if (!(isset($tempo[1]))) {
            if($hora == 1){
                return $hora;
            } else {
                return $hora;
            }
        } else {
            if($hora == 1){
                if($minutos == 1){
                    return 'intervalo de ' . $hora . ' Hora e ' .$minutos . ' Minuto.';
                } else {
                    return 'intervalo de ' . $hora . ' Hora e ' .$minutos . ' Minutos.';
                }
            } else {
                if($minutos == 1){
                    return 'intervalo de ' . $hora . ' Horas e ' .$minutos . ' Minuto.';
                } else {
                    return 'intervalo de ' . $hora . ' Horas e ' .$minutos . ' Minutos.';
                }
            }
        }
    }
    
    /**
     * Calcula a diferença entre dua horas
     */
    public static function horaExpediente($hora,$horainicioExpediente,$horafimExpediente)
    {        
        $hora_aux = self::converteHorasParaSegundos($hora);
        $horainicioExpediente_aux = self::converteHorasParaSegundos($horainicioExpediente);
        $horafimExpediente_aux = self::converteHorasParaSegundos($horafimExpediente);
        if($hora_aux < $horainicioExpediente_aux) {
            return $horainicioExpediente;
        }else if($hora_aux > $horafimExpediente_aux){
            return $horafimExpediente;
        }else{
            return $hora;
        }
    }
    
    public static function formataDiasDHMS($dias)
    {
        $n_dias = floor($dias);
        $frac_dias =  $dias - $n_dias;
        $horas = $frac_dias*24;
        $n_horas = floor($horas);
        $frac_horas =  $horas - $n_horas;
        $minutos = $frac_horas*60;
        $n_minutos = floor($minutos);
        $frac_minutos =  $minutos - $n_minutos;
        $segundos = $frac_minutos*60;
        $n_segundos = floor($segundos);
        $frac_segundos =  $segundos - $n_segundos;
        return $n_dias.'D'.' '.$n_horas.'h'.' '.$n_minutos.'m'.' '.$n_segundos.'s';
    }
    
    /**
     * Recebe um array contendo mais de uma hora e retorna a soma de todas elas
     * @param type $times array
     * @return type horas string
     */
    public static function somaHoras($times)
    {
        $seconds = 0;
        foreach ( $times as $time ){
           list( $g, $i, $s ) = explode( ':', $time );
           $seconds += $g * 3600;
           $seconds += $i * 60;
           $seconds += $s;
        }
        $hours = floor( $seconds / 3600 );
        $seconds -= $hours * 3600;
        $minutes = floor( $seconds / 60 );
        $seconds -= $minutes * 60;
        return "{$hours}:{$minutes}:{$seconds}";
    }
    
    /**
     * Recebe a string de hora formatada pelo Zend e retorna somente a data
     * @param $date
     */
    public static function somenteDate($date)
    {
        $aux = explode ("'", $date);
        $dataInicio = explode (" ", $aux[1]);
        return $dataInicio[0];
    }
    
    /**
     * Recebe a string de hora formatada pelo Zend e retorna somente a hora
     * @param $date
     */
    public static function somenteHora($date)
    {
        $aux = explode ("'", $date);
        $horaInicio = explode (" ", $aux[1]);
        return $horaInicio[1];
    }
    
    /**
     * Converte para segundos
     */
    public static function converteHorasParaSegundos($hora)
    {
        $aux = explode(":", $hora);
        $hora = $aux[0];
        $minutos = $aux[1];
        $segundos = $aux[2];
        return ($hora * 3600) + ($minutos * 60) + $segundos;
    }
    
    /**
     * Converte de segundos para o formato hh:mm:ss
     */
    public static function converteHoraMinSeg($seconds)
    {
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        return "$hours:$minutes:$seconds";  
    }
    
    /**
     * Calcula o fuso horário de cada localidade
     */
    public static function calculaFusoHorario($siglaSecao)
    {
        $fuso = new Application_Model_DbTable_PSecaoSubsecao();
        return $fuso->getFusoHorario($siglaSecao);
    }
}