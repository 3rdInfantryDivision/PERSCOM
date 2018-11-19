<?php
/*
+--------------------------------------------------------------------------
|   PERSCOM v1.0
|   =============================================
|   by 3rd Infantry Division (Erickson)
|   Copyright 2014-2015 Third Infantry Division
|   http://www.3rdinf.us
+--------------------------------------------------------------------------
*/

// Check to make sure the user is not accessing the files directly
if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

// Define some variables
define( 'IPS_XML_RPC_DEBUG_ON'  , 0 );
define( 'IPS_XML_RPC_DEBUG_FILE', '' );

// If no in ACP
if (!IN_ACP) {
	
	// Adjust this path as needed
	require_once( "ips_kernel/classXmlRpc.php" );
}

class app_class_perscom
{
	/**
     * Constructor
     *
     * @param    object        ipsRegistry
     * @return    @e void
     */
	public function __construct( ipsRegistry $registry ) {

		// Load classes
		$this->registry   =  ipsRegistry::instance();
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();

		// Load our base language file
		$this->registry->class_localization->loadLanguageFile( array( 'public_lang' ) );
        $this->registry->class_localization->loadLanguageFile( array( 'public_errors' ) );

		// If were not in the ACP
		if ( !IN_ACP ) {

			// Load PERSCOM parent class
			if ( !ipsRegistry::isClassLoaded('perscom') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_perscom.php', 'perscom_model_perscom', 'perscom' );
				$registry->setClass( 'perscom', new $classToLoad( $registry ) );
			}

			// Load PERSOM personnel class
			if ( !ipsRegistry::isClassLoaded('personnel') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_personnel.php', 'perscom_model_personnel', 'personnel' );
				$registry->setClass( 'personnel', new $classToLoad( $registry ) );
			}

			// Load PERSOM status' class
			if ( !ipsRegistry::isClassLoaded('status') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_status.php', 'perscom_model_status', 'status' );
				$registry->setClass( 'status', new $classToLoad( $registry ) );
			}

			// Load PERSCOM ranks class
			if ( !ipsRegistry::isClassLoaded('ranks') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_ranks.php', 'perscom_model_ranks', 'ranks' );
				$registry->setClass( 'ranks', new $classToLoad( $registry ) );
			}

			// Load PERSCOM combat units class
			if ( !ipsRegistry::isClassLoaded('combat_units') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_combat_units.php', 'perscom_model_combat_units', 'combat_units' );
				$registry->setClass( 'combat_units', new $classToLoad( $registry ) );
			}
			
			// Load PERSCOM admin units class
			if ( !ipsRegistry::isClassLoaded('admin_units') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_admin_units.php', 'perscom_model_admin_units', 'admin_units' );
				$registry->setClass( 'admin_units', new $classToLoad( $registry ) );
			}

			// Load PERSCOM awards class
			if ( !ipsRegistry::isClassLoaded('awards') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_awards.php', 'perscom_model_awards', 'awards' );
				$registry->setClass( 'awards', new $classToLoad( $registry ) );
			}

			// Load PERSCOM weapons class
			if ( !ipsRegistry::isClassLoaded('weapons') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_weapons.php', 'perscom_model_weapons', 'weapons' );
				$registry->setClass( 'weapons', new $classToLoad( $registry ) );
			}

			// Load PERSCOM logs class
			if ( !ipsRegistry::isClassLoaded('logs') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_logs.php', 'perscom_model_logs', 'logs' );
				$registry->setClass( 'logs', new $classToLoad( $registry ) );
			}

			// Load PERSCOM requests class
			if ( !ipsRegistry::isClassLoaded('requests') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_requests.php', 'perscom_model_requests', 'requests' );
				$registry->setClass( 'requests', new $classToLoad( $registry ) );
			}

			// Load PERSCOM enlistment applications class
			if ( !ipsRegistry::isClassLoaded('enlistment_applications') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_enlistment_applications.php', 'perscom_model_enlistment_applications', 'enlistment_applications' );
				$registry->setClass( 'enlistment_applications', new $classToLoad( $registry ) );
			}

			// Load PERSCOM statistics class
			if ( !ipsRegistry::isClassLoaded('statistics') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/perscom_model_statistics.php', 'perscom_model_statistics', 'statistics' );
				$registry->setClass( 'statistics', new $classToLoad( $registry ) );
			}

			// Load PERSCOM notifications wrapper class
			if ( !ipsRegistry::isClassLoaded('notifications') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/notifications.php', 'perscom_notifications_wrapper', 'notifications' );
				$registry->setClass( 'notifications', new $classToLoad( $registry ) );
			}

			// Load PERSCOM messenger wrapper class
			if ( !ipsRegistry::isClassLoaded('messenger') ){
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('perscom') . '/sources/classes/messenger.php', 'perscom_messenger_wrapper', 'messenger' );
				$registry->setClass( 'messenger', new $classToLoad( $registry ) );
			}

			// Get current date
			date_default_timezone_set("US/Eastern");
		}
	}

    /**
     * After output initialization
     *
     * @param    object        Registry reference
     * @return    @e void
     */
    public function afterOutputInit( ipsRegistry $registry )
    {
        // Check to make sure we are not in the ACP
        if (!IN_ACP)
        {
            // If PERSCOM not disabled
            if ( !ipsRegistry::$settings[ 'perscom_disable' ] )
            {
                // Get license key info
                $licenseKeyManager = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classPerscom.php', 'classLicenseManagement' );
                $manager = new $licenseKeyManager();

                // If not valid
                if ( !$manager->isValid() )
                {
                    // Switch between our errors
                    switch ( $manager->warning )
                    {
                        // No license key
                        case 1:

                            // Show an error
                            $registry->output->showError( 'Please enter a license key in your settings to use PERSCOM.', 1, FALSE, NULL, 400 );

                            break;

                        // License key expired
                        case 2:

                            // Show an error
                            $registry->output->showError( 'Your license key has expired. Please renew it with Deschutes Design Group LLC to continue using PERSCOM.', 2, FALSE, NULL, 400 );

                            break;

                        // License key inactive
                        case 3:

                            // Show an error
                            $registry->output->showError( 'Your license key is inactive. Please renew it with Deschutes Design Group LLC to continue using PERSCOM.', 3, FALSE, NULL, 400 );

                            break;
                    }

                }
            }

            // PERSCOM is disabled
            else
            {
                // If the member is not in any authorized groups
                if ( !IPSMember::isInGroup( ipsRegistry::member()->fetchMemberData(), array_filter(explode(',', ipsRegistry::$settings['perscom_offline_access_groups'])) , true) ) {

                    // Show an error
                    $registry->output->showError( ipsRegistry::$settings['perscom_offline_message'], 12345678, false, '', 403 );
                }
            }
        }
    }

	/**
     * After output initialization
     *
     * @param    object        Registry reference
     * @return    @e void
     */
    public function afterOutputInit2( ipsRegistry $registry ) {

    	// Check to make sure we are not in the ACP
    	if (!IN_ACP) {

    		// If PERSCOM is disabled
    	    if (ipsRegistry::$settings['perscom_disable']) {

    	    	// If the member is not in any authorized groups
            	if ( !IPSMember::isInGroup( ipsRegistry::member()->fetchMemberData(), array_filter(explode(',', ipsRegistry::$settings['perscom_offline_access_groups'])) , true) ) {

    	    		// Show an error
					$registry->output->showError( ipsRegistry::$settings['perscom_offline_message'], 12345678, false, '', 403 );    
				}		
			}

			// PERSCOM is not disabled
			else {

				// Check if there has been a license key even saved
				if (is_null(ipsRegistry::$settings['perscom_license_key']) || ipsRegistry::$settings['perscom_license_key'] == '') {
					
					// Show an error
					$registry->output->showError( 'No license key has been provided under PERSCOM Settings in the Admin CP.', 12345678, false, '', 401 );
				}

				// A license key has been entered
				else {

					// Check our license key
					$classXmlRpc = new classXmlRpc();
					$response = $classXmlRpc->sendXmlRpc( "http://www.3rdinf.us/interface/licenses.php", "check", array( 
						'key' => ipsRegistry::$settings['perscom_license_key'],
						'usage_id' => isset(ipsRegistry::$settings['perscom_license_key_usage_id']) ? ipsRegistry::$settings['perscom_license_key_usage_id'] : "1" ) );
					
					// If we get an error
					if ($classXmlRpc->errors) {

						// If we get a bad usage ID, try and activate the key
						if ($classXmlRpc->errors[0] == 'BAD_USAGE_ID') {
						
							// Activate the license key
							$activate = $classXmlRpc->sendXmlRpc( "http://www.3rdinf.us/interface/licenses.php", "activate", array( 
								'key' => ipsRegistry::$settings['perscom_license_key'] ) );	

							// If we successfully activated
							if ($activate) {
								
								// Get the usage id
								ipsRegistry::$settings['perscom_license_key_usage_id'] = $classXmlRpc->xmlrpc_params[0]['USAGE_ID'];
							}
						}

						// Some other error
						else {

							// If it is not a bad IP error
							if ($classXmlRpc->errors[0] != 'BAD_IP') {
							
								// Show an error
								$registry->output->showError( 'License Key Error: ' . $classXmlRpc->errors[0] . '. Please make sure your license key is entered properly in the Admin CP PERSCOM Settings page.', 12345678, false, '', 401 );	
							}  
						}
					}

					// We have no errors
					else {

						// Make sure the license key is still active
						if ($classXmlRpc->xmlrpc_params[0]['STATUS'] != 'ACTIVE') {

							// Show an error
							$registry->output->showError( 'License Key Error: ' . $classXmlRpc->xmlrpc_params[0]['STATUS'] . '. Please contact the 3rd Infantry Division if this problem persists.', 12345678, false, '', 401 );
						}
					}
				}
			}	
    	}
    }
}
?>