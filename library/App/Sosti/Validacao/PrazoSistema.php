<?php
require_once 'Zend/Validate/Abstract.php';

class App_Sosti_Validacao_PrazoSistema extends Zend_Validate_Abstract {
	const PRAZO_INVALIDO = 'prazoinvalido';
	
	protected static $_filter = null;
	protected $_messageTemplates = array (self::PRAZO_INVALIDO => "A hora escolhida está fora do horário de expediente." );
	protected $INICIO_EXPEDIENTE;
	protected $FIM_EXPEDIENTE;
    
    /**
     * Sets validator options
     *
     * @param  string|Zend_Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
        }

        if (array_key_exists('INICIO_EXPEDIENTE', $options)) {
            $this->INICIO_EXPEDIENTE = $options['INICIO_EXPEDIENTE'];
        }else{
            throw new Exception("Parametro INICIO_EXPEDIENTE ausente.");
        }
        
        if (array_key_exists('FIM_EXPEDIENTE', $options)) {
            $this->FIM_EXPEDIENTE = $options['FIM_EXPEDIENTE'];
        }else{
            throw new Exception("Parametro FIM_EXPEDIENTE ausente.");
        }
     }
    
    /**
     *
     * @param type $dataPrazo
     * @return boolean 
     */
	public function isValid($dataPrazo) {
        $TimeInterval = new App_TimeInterval();
        $horaPrazo = explode(" ",$dataPrazo);
        $horaPrazo = $horaPrazo[1];
        
        $horaPrazoSegundos = $TimeInterval->converteHorasParaSegundos($horaPrazo);
        $horaInicioSegundos = $TimeInterval->converteHorasParaSegundos($this->INICIO_EXPEDIENTE);
        $horaFimSegundos = $TimeInterval->converteHorasParaSegundos($this->FIM_EXPEDIENTE);
        
        if($horaPrazoSegundos <  $horaInicioSegundos || $horaPrazoSegundos >  $horaFimSegundos ){
            $this->_error ( self::PRAZO_INVALIDO );
            return false;
        }
        return true;
	}
    
}