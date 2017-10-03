<?php
/**
 * Esta classe serve para formatar o tempo total.
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta[at]trf1.jus.br
 * @license Free to use - no strings.
 */
class App_View_Helper_TempoTotal extends Zend_View_Helper_Abstract
{
    
    public static function helpdesk($tempo)
    {
        $tempoInicial = new App_Sosti_TempoSla(); 
        try {
            $tempoTotal = $tempoInicial->tempoTotalHelpdesk($tempo, '', '07:00:00', '20:00:00');
        } catch (Exception $ex) {
            $tempoTotal = $tempo;
        }
        return $tempoTotal;
    }
    
//    public static function desenvSustentacao()
//    {
//        
//        <span style="color: <?php echo $prazo['percentual']['cor'] ">
//        echo $prazo['prazo_restante'][0]."D ".$prazo['prazo_restante'][1]."h ".$prazo['prazo_restante'][2]."m ".$prazo['prazo_restante'][3]."s "."<strong>".$prazo['percentual']['pct']."</strong>% "
//        <!--</span>-->
        
//    }
}