<?php

declare(strict_types=1);

namespace Mwreg\Sraquotes\Domain\Model;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Random Quotes" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Marc Wampfler <mwreg@outlook.com>, 2108
 */


/**
 * Quote
 */
class Quote extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var array Extension configurations
     */
    private $extConfig;

    /**
     * @var string Staempfli AG quote database API token
     */
    private $apiToken = null;

    /**
     * @var int $amount The total amount of available quotes
     */
    public $amount = null;

    /**
     * @var array $quotes Stores the gotten quotes. This array holds the quotes to pass to the frontend
     */
    public $quotes = array();

    /**
     * This variable is to optimize the performance
     * @var int $maximumNumberOfSingleRequests The maximum numbers of single requests before we get all quotes
     */
    private $maximumNumberOfSingleRequests = 40;


    /**
     * Get extension configuration and check API token
     */
    function __construct ( )
    {
        // Set the extension configuration
        $this->setExtensionConfiguration();

        // Check if API token is set in the extension configuration
        if( $this->isApiTokenSet() )
        {
            // Set API token
            $this->setApiToken();

            // Set the available amount of quotes
            $this->setAmountOfQuotes();
        }
    }


    /**
     * Sets the extension configuration
     */
    private function setExtensionConfiguration()
    {
        $this->extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sraquotes');
    }

    /**
     * Get extension configuration
     *
     * @return array
     */
    public function getExtensionConfiguration()
    {
        return $this->extConfig;
    }

    /**
     * Checks if the Staempfli quote database API token is set in the extension configuration
     * @return bool
     */
    private function isApiTokenSet()
    {
        return array_key_exists( 'quoteApiToken' , $this->extConfig) && !empty($this->extConfig['quoteApiToken']) ;
    }

    /**
     * Set the api token
     */
    private function setApiToken()
    {
        $this->apiToken = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sraquotes' , 'quoteApiToken');
    }

    /**
     * Get the api token
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return (!empty($this->apiToken)) ? $this->apiToken : false;
    }

    /**
     * Executes the request to the API
     *
     * @param string $endpoint The API endpoint
     * @param string $method The request method. Default = GET
     * @return string JSON string containing the request results
     */
    private function doApiRequest( string $endpoint , string $method = 'GET')
    {
        // Root ULR of the quotes API
        $url = 'https://remoteassessment.staempflidev.com/api' . $endpoint;

        $requestFactory = new RequestFactory;

        $additionalOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getApiToken()
            ]
        ];

        // Get a PSR-7-compliant response object
        $response = $requestFactory->request( $url, $method, $additionalOptions );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Returned status code is ' . $response->getStatusCode() .'. Check the API Token in the extension settings.'
            );
        }

        if ($response->getHeaderLine('Content-Type') !== 'application/json') {
            throw new \RuntimeException(
                'The request did not return JSON data'
            );
        }

        $response = $response->getBody()->getContents();

        // Return the request result as is
        return $response;
    }


    /**
     * OLD manually raw PHP way
     * Executes the request to the API
     *
     * @param string $endpoint The API endpoint
     * @param string $method The request method. Default = GET
     * @return string JSON string containing the request results
     */
    private function doApiRequestOLD( string $endpoint , string $method = 'GET')
    {
        // Root ULR of the quotes API
        $url = 'https://remoteassessment.staempflidev.com/api' . $endpoint;

        // Prepare request header:
        $header = [
            'Content-Type: application/json',
            "Authorization: Bearer aGO0Bb8Yk5xBSJ3U9GvStX7ClwXuLg3iVqrH4EW3"
        ];

        // Initialize the request
        $ch = curl_init( $url );

        // Prepare the Request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $test = curl_getinfo($ch);

        // Execute the Request
        $result = curl_exec($ch);

        // Close the request
        curl_close($ch);

        // Return the request result as is
        return $result;
    }


    /**
     * Gets the total amount of quotes available.
     * Endpoint: GET /api/quotes/count
     */
    private function setAmountOfQuotes()
    {
        $amount = json_decode($this->doApiRequest( '/quotes/count' ));
        $this->amount = ( is_object($amount) && property_exists($amount,'count')) ? $amount->count : null;
    }

    /**
     * Returns the available amount of quotes.
     */
    public function getAmountOfQuotes()
    {
        return $this->amount;
    }

    /**
     * Store all quotes into $this->quotes
     * Endpoint: GET /api/quotes/{id}
     */
    public function setAllQuotes()
    {
        $quote = json_decode( $this->doApiRequest( '/quotes' ) );
        $this->quotes = ( is_array($quote) && count($quote) > 0 ) ? $quote : array();
    }


    /**
     * Get random single quote, based of a random generated quote id between 0 and the maximum of available quotes.
     * It could be, that the quote with the generated id was deleted.
     * Because we don't know if a quote with the generated id exists, we try several times.
     * If we have a lot of deleted quotes, we have the problem, that we don't reach all...That's life...
     *
     * Endpoint: /api/quotes/{id}
     *
     * @return boolean
     */
    public function addRandomQuote()
    {
        // We use a for loop to prevent infinite loops in case we don't get a quote back after a few tries.
        for ( $i = 0 ; $i < 9 ; $i++)
        {
            // Randomly generated quote id
            $endpoint = "/quotes/" . random_int( 1 , $this->amount );

            // Get a quote
            $quote = json_decode( $this->doApiRequest( $endpoint ) );

            // If we receive a quote, we store it and leave the loop
            if( is_object($quote) && property_exists( $quote , 'id' ) )
            {
                // Store the quote in the array, using the quote ID as array key, so that no duplicates are stored.
                $this->quotes[$quote->id] = $quote;

                // Exit for loop
                return true;
            }
        }

        // We return false if we don't get a quote
        return false;
    }


    /**
     * Sets random quotes and store them in the object instance.
     * By default, only one quote will be fetched.
     *
     * @param int $quantity Number of random quotes. Default: 1
     * @return boolean
     */
    public function setRandomQuotesByRequests( int $quantity )
    {
        // Check if we have a theoretical valid quote id
        if( $quantity <= $this->amount )
        {
            // Add a random quote to $this->quotes until quantity is reached.
            // We use a counter to prevent infinite loops
            $loopCount = 0;
            do
            {
                $this->addRandomQuote();
                $loopCount += 1;
            }while( count( $this->quotes ) < $quantity || $loopCount == $this->maximumNumberOfSingleRequests);

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Sets random quotes and store them in the object instance.
     * By default, only one quote will be fetched.
     * There are two ways to get the Quotes:
     *  1) One request to get all quotes, then select randomly the requested quantity of quotes.
     *  2) Multiple requests which a randomly generated quote id for each request.
     * This is done from a performance point of view: if we have a small number of requested quotes,
     * the performance is faster with several single requests than if we request all citations at once.
     *
     * @param int $quantity Number of quotes to get
     */
    public function setRandomQuotes( int $quantity = 1)
    {
        if( $quantity <= $this->maximumNumberOfSingleRequests )
        {
            $this->setRandomQuotesByRequests( $quantity );
        }
        else
        {
            //TODO get all quotes and select randomly from result array
            $this->setAllQuotes();
        }
    }



    /**
     * Returns the quotes.
     * It could be, that we have 0 quotes in $this->quotes.
     * This will be handled in the view.
     */
    public function getQuotes()
    {
        return $this->quotes;
    }
}
