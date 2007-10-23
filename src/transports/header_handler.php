<?php
/**
 * File containing the ezcWebdavHeaderHandler class.
 *
 * @package Webdav
 * @version //autogen//
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * An instance of this class manages header parsing and handling.
 *
 * An object of this class takes care about headers in the {@link
 * ezcWebdavTransport} to parse incoming headers and serialize outgoing
 * headers.  Like for the {@link ezcWebdavPropertyHandler}, the instance of
 * this class that is used in the current transport layer must be accessable
 * for plugins.
 *
 * @package Webdav
 * @version //autogen//
 */
class ezcWebdavHeaderHandler
{
    /**
     * Map of regular header names to $_SERVER keys.
     *
     * @var array(string=>string)
     */
    protected $headerMap = array(
        'Content-Length' => 'HTTP_CONTENT_LENGTH',
        'Content-Type'   => 'CONTENT_TYPE',
        'Depth'          => 'HTTP_DEPTH',
        'Destination'    => 'HTTP_DESTINATION',
        'Lock-Token'     => 'HTTP_LOCK_TOKEN',
        'Overwrite'      => 'HTTP_OVERWRITE',
        'Timeout'        => 'HTTP_TIMEOUT',
        'Server'         => 'SERVER_SOFTWARE',
    );

    /**
     * Pathfactory to process incoming headers.
     * 
     * @var ezcWebdavPathFactory
     */
    protected $pathFactory;

    /**
     * Returns an array with the given headers.
     *
     * Checks for the availability of headers in $headerNamess, given as an
     * array of header names, and parses them according to their format. 
     *
     * The returned array can be used with {@link ezcWebdavRequest->setHeaders()}.
     * 
     * @param array(string) $headerNames 
     * @return array(string=>mixed)
     *
     * @throws ezcWebdavUnknownHeaderException
     *         if a header requested in $headerNames is not known in {@link
     *         $headerNames}.
     */
    public function parseHeaders( array $headerNames )
    {
        $resultHeaders = array();
        foreach ( $headerNames as $headerName )
        {
            if ( ( $value = $this->parseHeader( $headerName ) ) !== null )
            {
                $resultHeaders[$headerName] = $value;
            }
        }
        return $resultHeaders;
    }

    /**
     * Parses a single header.
     *
     * Retrieves a $headerName and returns the processed value for it, if it
     * does exist. If the requested header is unknown, a {@link
     * ezcWebdavUnknownHeaderException} is thrown. If the requested header is
     * not present in {@link $_SERVER} null is returned.
     * 
     * @param string $headerName 
     * @return mixed
     */
    public function parseHeader( $headerName )
    {
        if ( isset( $this->headerMap[$headerName] ) === false )
        {
            throw new ezcWebdavUnknownHeaderException( $headerName );
        }
        return ( isset( $_SERVER[$this->headerMap[$headerName]] )
            ? $this->processHeader( $headerName, $_SERVER[$this->headerMap[$headerName]] )
            : null
        );
    }

    /**
     * Processes a single header value.
     *
     * Takes the $headerName and $value of a header and parses the value accordingly,
     * if necessary. Returns the parsed or unmanipuled result.
     * 
     * @param string $headerName 
     * @param string $value 
     * @return mixed
     */
    protected function processHeader( $headerName, $value )
    {
        switch ( $headerName )
        {
            case 'Depth':
                switch ( trim( $value ) )
                {
                    case '0':
                        $value = ezcWebdavRequest::DEPTH_ZERO;
                        break;
                    case '1':
                        $value = ezcWebdavRequest::DEPTH_ONE;
                        break;
                    case 'infinity':
                        $value = ezcWebdavRequest::DEPTH_INFINITY;
                        break;
                    // No default. Header stays as is, if not matched
                }
                break;
            case 'Destination':
                $value = ezcWebdavServer::getInstance()->pathFactory->parseUriToPath( $value );
                break;
            default:
                // @TODO Add plugin hook
        }
        return $value;
    }
}

?>