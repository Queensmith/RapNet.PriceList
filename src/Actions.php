<?php

namespace Rapnet\RapnetPriceList;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

/**
 * Class Actions
 * Client for interacting with the RapNet API.
 * @package Rapnet\RapnetPriceList
 */
class Actions
{
    private $config;

    /**
     * Constructor for the Index class.
     *
     * @param string|null $clientId The client ID.
     * @param string|null $clientSecret The client secret.
     */
    public function __construct($clientId = null, $clientSecret = null)
    {
        $this->config = [
            'base_path' => "https://technet.rapnetapis.com",
            'authorization_url' => "https://rapaport-prod.auth0.com",
            'machine_auth_url' => "https://authztoken.api.rapaport.com",
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => null,
            'token_callback' => null,
            'pricelist_url' => "https://technet.rapnetapis.com/pricelist/api",
            'jwt' => null,
            'scope' => 'manageListings priceListWeekly instantInventory',
            'audience' => 'https://pricelist.rapnetapis.com'
        ];
    }

    /**
     * Authorizes the user by redirecting to the authorization URL.
     *
     * @param string $redirectUrl The redirect URL.
     */
    public function authorize($redirectUrl)
    {
        $client = new GuzzleClient(['verify' => false]);
        $url = "{$this->config['authorization_url']}/authorize?response_type=code&client_id={$this->config['client_id']}&redirect_uri={$redirectUrl}&audience={$this->config['audience']}&scope={$this->config['scope']}";

        header("Location: {$url}");
        exit();
    }

    /**
     * Retrieves an authentication token for user authentication.
     *
     * @param string $code The authorization code.
     * @param string $redirectUrl The redirect URL.
     * @return array|null The decoded authentication token data or null on failure.
     * @throws RequestException if the API request fails.
     */
    public function getAuthToken($code, $redirectUrl)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['machine_auth_url']}/api/get";

            $response = $client->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'json' => [
                        'client_id' => $this->config['client_id'],
                        'client_secret' => $this->config['client_secret'],
                        'code' => $code,
                        'redirect_uri' => $redirectUrl
                    ],
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    // Added as an alias to fix the spelling mistake
    public function getAuthTokenMachineToMachinMethod()
    {
        // @deprecated Use getAuthTokenMachineToMachineMethod instead
        return $this->getAuthTokenMachineToMachineMethod();
    }

    /**
     * Retrieves an authentication token for machine-to-machine authentication.
     *
     * @return array|null The decoded authentication token data or null on failure.
     * @throws RequestException if the API request fails.
     */
    public function getAuthTokenMachineToMachineMethod()
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['machine_auth_url']}/api/get";

            $response = $client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'client_id' => $this->config['client_id'],
                        'client_secret' => $this->config['client_secret']
                    ],
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieves the price list for a given shape.
     *
     * @param string $token The authorization token.
     * @param string $shape The diamond shape (e.g., 'Round'). Defaults to 'Round'.
     * @param string $acceptType The desired response format ('application/json', 'application/xml', 'application/dbf').
     * @return array|null The decoded price list data or null on failure (consider throwing exception instead).
     * @throws RequestException if the API request fails.
     */
    public function getPricesList($token, $shape = 'Round', $acceptType = 'application/json')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/list?shape={$shape}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'accept' => $acceptType,
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieves the normalized price list for a given shape.
     *
     * @param string $token The authorization token.
     * @param string $shape The diamond shape (e.g., 'Round'). Defaults to 'Round'.
     * @param bool $csvnormalized Whether to return the price list in CSV format.
     * @return array|null The decoded price list data or null on failure (consider throwing exception instead).
     * @throws RequestException if the API request fails.
     */
    public function getNormalizedPricesList($token, $shape = 'Round', $csvnormalized = true)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/list?shape={$shape}&csvnormalized={$csvnormalized}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'accept' => 'text/csv',
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieves price items for a given shape, size, color, and clarity.
     *
     * @param string $token The authorization token.
     * @param string $shape The diamond shape (e.g., 'Round'). Defaults to 'Round'.
     * @param string $size The size of the diamond.
     * @param string $color The color of the diamond.
     * @param string $clarity The clarity of the diamond.
     * @param string $acceptType The desired response format ('application/json', 'application/xml').
     * @return array|null The decoded price items data or null on failure (consider throwing exception instead).
     * @throws RequestException if the API request fails.
     */
    public function getPriceItems($token, $shape = 'Round', $size, $color, $clarity, $acceptType = 'application/json')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices?shape={$shape}&size={$size}&color={$color}&clarity={$clarity}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => $acceptType,
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieves price changes for a given shape.
     *
     * @param string $token The authorization token.
     * @param string $shape The diamond shape (e.g., 'Round'). Defaults to 'Round'.
     * @return array|null The decoded price changes data or null on failure (consider throwing exception instead).
     * @throws RequestException if the API request fails.
     */
    public function getPricesChanges($token, $shape = 'Round')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/changes?shape={$shape}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }
}
