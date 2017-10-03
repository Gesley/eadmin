<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initSession()
    {
        $this->bootstrap('db');
        $options = $this->getOptions();
        Zend_Session::setSaveHandler(new App_Session_SaveHandler_Db(
                $options['resources']['db'],
                $options['resources']['session']));
        Zend_Session::start();  
    }

    protected function _initAutoload() {
        $autoloader = $this->getApplication()->getAutoloader();
        if (!$autoloader->isFallbackAutoloader()) {
            $autoloader->setFallbackAutoloader(true);
        }
        $autoloader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH . '/',
                    'resourceTypes' => array(
                        'form' => array(
                            'path' => 'forms',
                            'namespace' => 'Form'
                        ), 
                        'services' => array(
                            'path' => 'services',
                            'namespace' => 'Services_'
                        ),
                        'services/admin' => array(
                            'path' => 'services/admin',
                            'namespace' => 'Services_Admin_'
                        ),
                        'services/sosti' => array(
                            'path' => 'services/sosti',
                            'namespace' => 'Services_Sosti_'
                        ),
                        'services/os' => array(
                            'path' => 'services/os',
                            'namespace' => 'Services_Os_'
                        ),
                        'services/sisad' => array(
                            'path' => 'services/sisad',
                            'namespace' => 'Services_Sisad_'
                        ),
                        'services/rh' => array(
                            'path' => 'services/rh',
                            'namespace' => 'Services_Rh_'
                        ) 
                    )
                ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');
        $autoloader->addResourceType('services', 'services/', 'Services');
        return $autoloader;
    }

    protected function _iniLocale() {
        date_default_timezone_set('America/Sao_Paulo');
        //Data no formato 12-12-2010
        $locale = new Zend_Locale('pt_BR');
        Zend_Locale_Format::setOptions(array(
                    'locale' => 'pt_BR',
                    'date_format' => 'dd/MM/YYYY'
                        ));
        $registry = Zend_Registry::getInstance ();
        $registry->set(Zend_Locale, $locale);
    }

    public function _initTranslate() {
        $translate = new Zend_Translate('Array', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'pt_BR.php', 'pt_BR');
        Zend_Registry::set('Zend_Translate', $translate);
        Zend_Validate_Abstract::setDefaultTranslator($translate);
    }

    protected function _initViewHelpers() {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        //$view->addHelperPath('App/View/Helper', 'App_View_Helper');
    }

    protected function _initControllerPlugins() {
        //$this->bootstrap('FirePHP');
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new App_Controller_Plugin_Timeout());
        $frontController->registerPlugin(new App_Controller_Plugin_Db());
        $frontController->registerPlugin(new App_Controller_Plugin_CustomView());
	$frontController->registerPlugin(new Trf1_Sisad_Plugin_Ajuda());
	$frontController->registerPlugin(new Trf1_Sisad_Plugin_Informacao());
        //$frontController->registerPlugin(new App_Controller_Plugin_Auth());
    }

//    protected function _initDoctrine() {
//        $this->getApplication()->getAutoloader()->pushAutoloader(array('Doctrine', 'autoload'));
//        spl_autoload_register(array('Doctrine', 'modelsAutoload'));
//        $loader = Zend_Loader_Autoloader::getInstance();
//        $loader->setFallbackAutoloader(true);
//        $loader->pushAutoloader(array('Doctrine', 'autoload'));
//
//        $manager = Doctrine_Manager::getInstance();
//        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
//        $manager->setAttribute(
//                Doctrine_Core::ATTR_MODEL_LOADING,
//                Doctrine_Core::MODEL_LOADING_CONSERVATIVE
//        );
//        $manager->setAttribute(Doctrine::ATTR_SEQNAME_FORMAT, '%s');
//        //$manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);
//        //$manager->addRecordListener(new App_Doctrine_Record_Listener_ClearCache());
//        /*
//          $cacheDriver = new Doctrine_Cache_Apc();
//          $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
//         */
//        /*
//          $cacheConn = Doctrine_Manager::connection(new PDO('sqlite::memory:'));
//          $cacheDriver = new Doctrine_Cache_Db(array('connection' => $cacheConn));
//
//         */
//
//        //$doctrineConfig = $this->getResource('doctrine');
//
//        $doctrineConfig = $this->getOption('doctrine');
//        //Zend_Debug::dump($doctrineConfig,'doctrine config');
//        //Zend_Debug::dump($this->getOptions() ,'options');
//        foreach ($doctrineConfig['connections'] as $name => $options) {
//            $manager->openConnection($options['dsn'], $name);
//            //$manager->openConnection('oracle://SAD_P:SADPTDQC@172.16.3.216/trf1dsv', 'default');
//        }
//
//        $manager->setCurrentConnection('default');
//        /*
//          $manager->openConnection($doctrineConfig['connections']['default']['dsn'],'default');
//          $manager->openConnection($doctrineConfig['connections']['acl']['dsn'],'acl');
//         */
//        $conn = $manager->getCurrentConnection();
//        $conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
//
//        //$conn->setAttribute(Doctrine::ATTR_QUERY_CACHE, $cacheDriver);
//        //$conn->setCharset('utf8');
//        /*
//          $cacheDriver = new Doctrine_Cache_Apc();
//          $manager->setAttribute(Doctrine::ATTR_RESULT_CACHE, $cacheDriver);
//          $manager->setAttribute(Doctrine::ATTR_RESULT_CACHE_LIFESPAN, 3600);
//         */
//        Doctrine::loadModels($doctrineConfig['models_path']);
//
//        $manager->registerHydrator('HYDRATE_PAIRS', 'App_Doctrine_Hydrator_Pairs');
//
//        return $manager;
//    }

    protected function _initZFDebug() {
        $zfdebugConfig = $this->getOption('zfdebug');
        if ($zfdebugConfig['enabled'] != 1) {
            return;
        }
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');
        /*
          $this->bootstrap('Doctrine');
          $doctrine = $this->getResource('Doctrine');
         */
        $options = array(
            'plugins' => array(
                'Variables',
                'File' => array('base_path' => realpath(APPLICATION_PATH . '/../')),
                'Memory',
                'ZFDebug_Controller_Plugin_Debug_Plugin_Debug' => array(
                    'tab' => 'Debug',
                    'panel' => ''),
                'ZFDebug_Controller_Plugin_Debug_Plugin_Auth',
                'App_ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine',
                'Time',
                'Registry',
                'Exception'
            )
        );

        # Instantiate the database adapter and setup the plugin.
        # Alternatively just add the plugin like above and rely on the autodiscovery feature.
        if ($this->hasPluginResource('db')) {
            $this->bootstrap('db');
            $db = $this->getPluginResource('db')->getDbAdapter();
            $options['plugins']['Database']['adapter'] = $db;
        }

        # Setup the cache plugin
        if ($this->hasPluginResource('cache')) {
            $this->bootstrap('cache');
            $cache = $this->getPluginResource('cache')->getDbAdapter();
            $options['plugins']['Cache']['backend'] = $cache->getBackend();
        }

        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        //$frontController->registerPlugin($debug);
    }
      
    protected function _initNavigation()
    {
        $this->bootstrap('view');
        $this->bootstrap('layout');
        $this->bootstrap('frontController');
        $this->bootstrap('acl');
        $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml','nav');

        $resource = new Zend_Application_Resource_Navigation(array(
              'pages' => $config->toArray(),
        ));
        $resource->setBootstrap($this);

        $aNamespace = new Zend_Session_Namespace('userNs');

        $view = Zend_Layout::getMvcInstance()->getView();
        $acl = Zend_Registry::get('Zend_Acl');

        $view->navigation($resource->getContainer())->setAcl($acl)->setRole($aNamespace->perfil);

        return $resource->init();
    }

    public function _initAcl()
    {
        $this->bootstrap('FrontController');
        $auth = Zend_Auth::getInstance();
        $acl = new App_Acl($auth);
        Zend_Registry::set('Zend_Acl', $acl);
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new App_Controller_Plugin_Acl($auth, $acl));
    }

	/**
	 * Classe destrutora.
	 * Nesse caso, finaliza a conexão com banco de dados, se a mesma estiver aberta.
	 * 
	 * NOTA: Recomendo também utilizad o parâmetro:
	 * 		'persistent' => true
	 * no arquivo application.ini para cada grupo de:
	 * 		resource.db...
	 * 		resource.multidb...
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __destruct() {
		try {
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			if ( $banco->isConnected() ) {
				$banco->closeConnection();
			}
		} catch (Exception $e) {
			// não faz nada!
		}
	}

}