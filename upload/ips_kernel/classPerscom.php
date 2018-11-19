<?php

class classLicenseManagement
{
    /**
     * @brief License key warnings
     */
    const LICENSE_KEY_WARNING_NONE = '1';
    const LICENSE_KEY_WARNING_EXPIRED = '2';
    const LICENSE_KEY_WARNING_INACTIVE = '3';
    const LICENSE_KEY_WARNING_USAGE_ID = '4';
    const LICENSE_KEY_WARNING_IDENTIFIER = '5';

    /*
     * @brief License key API url
     */
    protected static $url = 'https://www.deschutesdesigngroup.com/applications/nexus/interface/licenses/?';

    /**
     * @brief	The license key
     */
    public $licenseKey = NULL;

    /**
     * @brief	The license key expiration date
     */
    public $expirationDate = NULL;

    /**
     * @brief	Data Store
     */
    public $data = NULL;

    /**
     * @brief	The license warning message
     */
    public $warning = NULL;

    /**
     * Constructor
     *
     * @return	void
     */
    public function __construct()
    {
        // Get license key info
        $data = $this->getLicenseKeyData( TRUE );

        // If we get data back
        if ( isset( $data ) AND $data != NULL )
        {
            // Set data
            $this->data = $data;
            $this->expirationDate = $data['expires'];
            $this->licenseKey = $data['key'];
        }
    }

    /*
     * @brief Checks the current license key in cache and fetches it if its old or non-existent
     * @param   $forceRefresh   Forces check to query the licensing server instead of pulling the cached value
     * @return  $data           The license key data
     */
    protected function getLicenseKeyData( $forceRefresh = FALSE )
    {
        // Set our variables for our cached values
        $cached = NULL;
        $setFetched = FALSE;

        // Get our license dadata
        if ( ipsRegistry::cache()->getCache( 'perscomLicenseData' ) )
        {
            // Set the cached value
            $cached =  ipsRegistry::cache()->getCache( 'perscomLicenseData' );

            // If the latest fetch has been greater than 21 days or we are forcing a refresh
            if ( $cached['fetched'] > ( time() - 1814400 ) and !$forceRefresh )
            {
                // If the license key has not expired, or we dont have the expiration data value in the cached results
                if ( !$cached['data']['expires'] OR $cached['data']['expires'] > time() )
                {
                    // Return the cached license key data
                    return $cached['data'];
                }

                // If the license key is expired and we've refetched the data, making sure its up to data
                else if ( $cached['data']['expires'] AND $cached['data']['expires'] < time() AND isset( $cached['refetched'] ) )
                {
                    // Return the refetched cached license key data
                    return $cached['data'];
                }

                // The license key is expired, refetch the data to make sure we have to the latest
                else
                {
                    // Were refetching the license data
                    $setFetched = TRUE;
                }
            }
        }

        // Fetch the license key data
        require_once IPS_KERNEL_PATH . 'classFileManagement.php';
        $api = new classFileManagement();
        $api->timeout = 5;
        $response = $api->getFileContents( static::$url . 'info&key=' . ipsRegistry::$settings['perscom_license_key'] . '&usage_id=' . ipsRegistry::$settings['perscom_usage_id'] . '&identifier=' . ipsRegistry::$settings['board_url'] );

        // Decode the json
        $response = json_decode( $response, TRUE );

        // If we got content back
        if( !empty( $response ) ) {

            // Create the data we are going to store
            $licenseData = array( 'fetched' => time(), 'data' => $response );

            // If were refetching
            if ( $setFetched )
            {
                // Make sure to set the flag
                $licenseData['refetched'] = 1;
            }

            // Store the data
            ipsRegistry::cache()->setCache( 'perscomLicenseData', $licenseData, array( 'array' => 1 ) );
        }

        // Return the response
        return $response;
    }

    /**
     * Checks the license key, activating if need be
     *
     * @param    string    The license key
     * @return    void
     * @throws    \DomainException
     */
    public static function checkLicenseKey()
    {
        // Get our class that will handle the requests
        require_once IPS_KERNEL_PATH . 'classFileManagement.php';
        $api = new classFileManagement();
        $api->timeout = 5;

        // Perform an info request
        $info = $api->getFileContents( static::$url . 'info&key=' . ipsRegistry::$settings['perscom_license_key'] . '&identifier=' . ipsRegistry::$settings['board_url'] );

        // Decode the json
        $infoResponse = json_decode( $info, TRUE );

        // If we have an error
        if ( isset( $infoResponse['errorMessage'] ) )
        {
            // Throw an error code
            static::getLicenseKeyError( $api->http_status_code, $infoResponse['errorMessage'] );
        }

        // No error
        else
        {
            // If our usage id is in the usage data and its activated
            if ( isset( $infoResponse['usage_data'] ) AND isset( $infoResponse['usage_data'][ipsRegistry::$settings['perscom_usage_id']] ) AND isset( $infoResponse['usage_data'][ipsRegistry::$settings['perscom_usage_id']]['activated'] ) )
            {
                // Check the license key
                $check = $api->getFileContents( static::$url . 'check&key=' . ipsRegistry::$settings['perscom_license_key'] . '&identifier=' . ipsRegistry::$settings['board_url'] . '&usage_id=' . ipsRegistry::$settings['perscom_usage_id'] );

                // Decode the JSON
                $checkResponse = json_decode( $check, TRUE );

                // If we have an error
                if ( isset( $checkResponse['errorMessage'] ) )
                {
                    // Throw an error code
                    static::getLicenseKeyError( $api->http_status_code, $checkResponse['errorMessage'] );
                }
            }

            // Our usage id isnt in the usage data, so try and activate it
            else
            {
                // Activate the license key
                $activate = $api->getFileContents( static::$url . 'activate&key=' . ipsRegistry::$settings['perscom_license_key'] . '&identifier=' . ipsRegistry::$settings['board_url'] );

                // Decode the JSON
                $activateResponse = json_decode( $activate, TRUE );

                // If we have an error
                if ( isset( $activateResponse['errorMessage'] ) )
                {
                    // Throw an error code
                    static::getLicenseKeyError( $api->http_status_code, $activateResponse['errorMessage'] );
                }

                // If we have a good response
                elseif ( $api->http_status_code == 200 )
                {
                    // Save the usage id
                    IPSLib::updateSettings( array( 'perscom_usage_id' => $activateResponse['usage_id'] ) );
                }
            }
        }
    }

    /**
     * Checks to see if the license key is valid
     *
     * @return  bool  Whether or not the license key is valid
     */
    public function isValid()
    {
        // If there is no license key set and the user can manage the license key
        if ( !$this->licenseKey )
        {
            // Set warning
            $this->warning = static::LICENSE_KEY_WARNING_NONE;

            // There is no license key on file
            return FALSE;
        }

        // If the license key is set
        else
        {
            // If the license key is expired, or not active and they have permissions to manage the license key
            if ( ( ( isset( $this->data['expires'] ) and $this->data['expires'] < time() ) ) )
            {
                // Set warning
                $this->warning = static::LICENSE_KEY_WARNING_EXPIRED;

                // Return expired
                return FALSE;
            }
            elseif ( isset( $this->data['errorCode'] ) and $this->data['errorCode'] === 'INACTIVE' )
            {
                // Set warning
                $this->warning = static::LICENSE_KEY_WARNING_INACTIVE;

                // Return inactive
                return FALSE;
            }
            elseif ( isset( $this->data['errorCode'] ) and $this->data['errorCode'] === 'EXPIRED' )
            {
                // Set warning
                $this->warning = static::LICENSE_KEY_WARNING_EXPIRED;

                // Return inactive
                return FALSE;
            }
        }

        // Set no warning
        $this->warning = NULL;

        // The license key is fine
        return TRUE;
    }

    /*
     * @brief   Returns the error for correct license response
     * @param   $httpResponseCode   The HTTP status code that was returned in the response
     * @param   $code               The error code
     * @return  error               The DomainException
     */
    protected static function getLicenseKeyError( $httpResponseCode, $code )
    {
        // Switch between the http response codes
        switch ( $httpResponseCode )
        {
            // Good response
            case 200:

                // Switch between the codes
                switch ( $code )
                {
                    // Inactive
                    case 103:

                        // License key is inactive
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_inactive', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Expired
                    case 104:

                        // License key is expired
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_expired', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // All others
                    default:

                        // General error
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_general', $code, TRUE, NULL, $httpResponseCode );

                        break;
                }

                break;

            // Bad response
            case 400:

                // Switch between the codes
                switch ( $code )
                {
                    // Inactive or bad id
                    case 101:

                        // License key is inactive or bad id
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_identifier_key', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // License key not active
                    case 102:

                        // License key is inactive
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_inactive', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Purchase cancelled
                    case 103:

                        // Purchase is cancelled
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_cancelled', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Purchase not active
                    case 104:

                        // License key is expired
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_expired', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Max uses
                    case 201:

                        // Too many uses
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_max', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Bad usage id
                    case 303:

                        // License key has been used too much
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_usage_id', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // Bad ip
                    case 304:

                        // License key has been used too much
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_ip', $code, TRUE, NULL, $httpResponseCode );

                        break;

                    // All others
                    default:

                        // General error
                        ipsRegistry::getClass('output')->showError( 'perscom_license_error_general', $code, TRUE, NULL, $httpResponseCode );

                        break;
                }

                break;

            // All other responses
            default:

                // General error
                ipsRegistry::getClass('output')->showError( 'perscom_license_error_general', $code, TRUE, NULL, $httpResponseCode );

                break;
        }
    }
}