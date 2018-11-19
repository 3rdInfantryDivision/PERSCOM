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

class admin_perscom_settings_settings extends ipsCommand
{
	/**
	 * Shortcut for url
	 *
	 * @access	private
	 * @var		string			URL shortcut
	 */
	private $form_code;
	
	/**
	 * Shortcut for url (javascript)
	 *
	 * @access	private
	 * @var		string			JS URL shortcut
	 */
	private $form_code_js;
	
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
	    //-----------------------------------------
		// Handle the do stuff
		//-----------------------------------------
		switch ($this->request['do'])
		{
			case 'enlistment_application':
				$this->request['conf_title_keyword'] = 'perscom_enlistment_application';
			break;

			case 'database':
				$this->request['conf_title_keyword'] = 'perscom_database_settings';
			break;

			case 'setting_update':
                $this->validateLicenseKey();
            break;

			default:
				$this->request['conf_title_keyword'] = 'perscom_general_settings';
			break;
		}

        //-----------------------------------------
		// Set up stuff
		//-----------------------------------------
		$this->form_code	= 'module=settings&amp;section=settings';
		$this->form_code_js	= 'module=settings&section=settings';

		//-------------------------------
		// Grab, init and load settings
		//-------------------------------
		$classToLoad	= IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ).'/modules_admin/settings/settings.php', 'admin_core_settings_settings', 'core' );
		$settings		= new $classToLoad( $this->registry );
		$settings->makeRegistryShortcuts( $this->registry );

		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		
		$settings->html			= $this->registry->output->loadTemplate( 'cp_skin_settings', 'core' );
		$settings->form_code	= $settings->html->form_code    = 'module=settings&amp;section=settings';
		$settings->form_code_js	= $settings->html->form_code_js = 'module=settings&section=settings';

        $settings->return_after_save         = $this->settings['base_url'] . $this->form_code . '&do=setting_update';
        $settings->_viewSettings();

		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------

		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}

    /**
     * Handles the calednar new/edit form
     *
     * @param	string	$type	Either new or edit
     * @return	@e void
     */
    public function validateLicenseKey()
    {
        // Get license key info
        $licenseKeyManager = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classPerscom.php', 'classLicenseManagement' );
        $manager = new $licenseKeyManager();

        // Check license key
        $manager::checkLicenseKey();

        // Redirect
        $this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . 'app=perscom&module=settings&section=settings' );
    }
}