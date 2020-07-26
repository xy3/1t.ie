<?php


namespace OneT;


/**
 *
 * Strict HTTP/HTTPS/FTP URL validator
 *
 * This class is designed to provide stricter URL validation than
 * built-in PHP functions do on their own.
 *
 **/

class StrictUrlValidator
{
	private static $errorMessage = '';


	/**
	 *
	 * Validate a URL
	 *
	 * Analyze URL and check the hostname and HTTP response code.
	 *
	 * @param string	$url				The URL to be validated
	 * @param boolean	$validateHost		If set to true, existence of the hostname will be tested
	 * @param boolean	$validateAddress	If set to true, return code of the URL will be tested
	 *
	 * @return boolean Returns true if the URL appears to be valid, false otherwise.
	 *
	 **/

	public static function validate( $url, $validateHost = false, $validateAddress = false )
	{
		$filteredUrl = filter_var( $url, FILTER_VALIDATE_URL );

		if( $filteredUrl === false )
		{
			self::setError( 'URL did not pass filter_var()' );
			return false;
		}

		$parsedUrl = parse_url( $filteredUrl );

		if( $parsedUrl === false )
		{
			self::setError( 'URL did not pass parse_url()' );
			return false;
		}

		if( isset( $parsedUrl[ 'scheme' ] ) === false )
		{
			self::setError( 'URL does not contain a scheme' );
			return false;
		}


		if( isset( $parsedUrl[ 'host' ] ) === false )
		{
			self::setError( 'URL does not contain a host' );
			return false;
		}

		/* You might want to have this one too, although
		 * then http://www.google.com will fail, because
		 * the function expects http://www.google.com/
		 *
		 * if( isset( $parsedUrl[ 'path' ] ) === false )
		 * {
		 * 		return 'URL does not contain a path';
		 * }
		 */


		if( $parsedUrl[ 'scheme' ] === $parsedUrl[ 'host' ] )
		{
			/*
			 * This is actually technically incorrect, but have deemed
			 * unlikely that anyone would have a locally hosted domain
			 * named 'http' or 'https'...
			 */

			self::setError( 'URL scheme matches URL host' );
			return false;
		}


		if( $validateHost === true )
		{
			if( self::validateHostname( $parsedUrl[ 'host' ] ) === false )
			{
				return false;
			}
		}


		if( $validateAddress === true )
		{
			if( self::validateAddress( $filteredUrl ) === false )
			{
				return false;
			}
		}


		return true;
	}


	/**
	 *
	 * Check that hostname exists
	 *
	 * @param string	$host	Hostname to be tested
	 *
	 * @return bool true if hostname can be solved, false otherwise
	 *
	 **/

	protected static function validateHostname( $host )
	{
		// Using gethostbynamel on purpose
		if( gethostbynamel( $host ) === false )
		{
			self::setError( 'Could not solve hostname' );
			return false;
		}

		self::setError( '' );
		return true;
	}


	/**
	 *
	 * Query URL and check that the server does not return an
	 * error code.
	 *
	 * @param string	$url	URL to be checked
	 *
	 * @return bool true if the URL appears to be OK, false otherwise
	 *
	 */

	protected static function validateAddress( $url )
	{
		$curl = curl_init();

		if( $curl === false )
		{
			// initialization failed
			self::setError( 'Could not initialize cURL' );
			return false;
		}

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );


		if( curl_exec( $curl ) === false )
		{
			// fetch failed
			self::setError( 'Failed fetching the page with cURL' );
			return false;
		}


		$status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );


		if( ( intval( $status ) >= 400 ) || ( $status === false ) )
		{
			self::setError( 'Server responded with an error code: ' . intval( $status ) );
			return false;
		}

		return true;
	}


	private static function setError( $errorMessage )
	{
		self::$errorMessage = $errorMessage;
	}


	public static function getError()
	{
		return self::$errorMessage;
	}
}


