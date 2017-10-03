<?php
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', true);
	date_default_timezone_set("America/Sao_Paulo");
	
	
	define('DS', DIRECTORY_SEPARATOR);
	define('PS', PATH_SEPARATOR);
	//define('BASEPATH', getcwd() . DS);
	
	
	define('BASEPATH', dirname(dirname(__FILE__)) . DS);
	
	// directory setup and class loading
	set_include_path(
		 'd:' . DS . 'library' . DS
		 . PS . BASEPATH . get_include_path() . DS
		 . PS . '.');
	include('zend/exception.php');
	include('zend/soap/client.php');
	include('zend/soap/client/common.php');
	include('zend/soap/client/dotnet.php');
	include('zend/soap/client/exception.php');
	include('zend/debug.php');
	include('Zend/Mail/Exception.php');
	include('Zend/Mail/Transport/Exception.php');
	include('Zend/Mime.php');
	include('Zend/Mime/Part.php');
	include('Zend/Mime/Message.php');
	include('Zend/Mail/Transport/Abstract.php');
	include('Zend/Mail/Transport/Sendmail.php');
	include('Zend/Mail.php');
	
	
	/*
	$tr = new Zend_Mail_Transport_Sendmail('-freturn_to_me@example.com');
	Zend_Mail::setDefaultTransport($tr);
	*/
	/*
	if (mail('wilton.junior@trf1.gov.br', 'testes srvweb3', 'testes srvweb3','wilton.junior@trf1.gov.br')) {
		echo 'funcionou';
		phpinfo();
	} else {
		echo 'n funcionou';
		phpinfo();
	}
	*/
	$mail = new Zend_Mail();
	$mail->setBodyText('This is the text of the mail.');
	$mail->setFrom('wilton.junior@trf1.gov.br', 'Some Sender');
	$mail->addTo('wilton.junior@trf1.gov.br', 'Some Recipient');
	$mail->setSubject('TestSubject');
	$mail->send();
	
