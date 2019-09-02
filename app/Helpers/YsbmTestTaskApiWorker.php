<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.09.19
 * Time: 17:24
 */

namespace App\Helpers;

use Log;
use App\Exceptions\YsbmTestTaskApiException;
use App\Exceptions\YsbmTestTaskApiTokenExpiredException;
use App\Exceptions\YsbmTestTaskApiInvalidCredentialsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class YsbmTestTaskApiWorker
{
    private const PARAM_TYPE_INTEGER = 0;
    private const PARAM_TYPE_STRING = 1;

    private static $paramTypeFunctionsCheck = [
        self::PARAM_TYPE_INTEGER => 'is_numeric',
        self::PARAM_TYPE_STRING  => 'is_string',
    ];

    private static $actions = [
        'auth'           => [
            'path'           => '/login',
            'method'         => 'POST',
            'paramsRequired' => [
                'email'    => self::PARAM_TYPE_STRING,
                'password' => self::PARAM_TYPE_STRING,
            ],
            'tokenRequired'  => false,
        ],
        'itemCreate'     => [
            'path'           => '/item',
            'method'         => 'POST',
            'paramsRequired' => [
                'id'         => self::PARAM_TYPE_INTEGER,
                'shipmentId' => self::PARAM_TYPE_INTEGER,
                'name'       => self::PARAM_TYPE_STRING,
                'code'       => self::PARAM_TYPE_STRING,
            ],
            'tokenRequired'  => true,
        ],
        'itemFetch'      => [
            'path'           => '/item/{id}',
            'method'         => 'GET',
            'paramsRequired' => [
                'id' => self::PARAM_TYPE_INTEGER,
            ],
            'tokenRequired'  => true,
        ],
        'itemUpdate'     => [
            'path'           => '/item/{id}',
            'method'         => 'PUT',
            'paramsRequired' => [
                'id'         => self::PARAM_TYPE_INTEGER,
                'shipmentId' => self::PARAM_TYPE_INTEGER,
                'name'       => self::PARAM_TYPE_STRING,
                'code'       => self::PARAM_TYPE_STRING,
            ],
            'tokenRequired'  => true,
        ],
        'itemDelete'     => [
            'path'           => '/item/{id}',
            'method'         => 'DELETE',
            'paramsRequired' => [
                'id' => self::PARAM_TYPE_INTEGER,
            ],
            'tokenRequired'  => true,
        ],
        'shipmentList'   => [
            'path'           => '/shipment',
            'method'         => 'GET',
            'paramsRequired' => [],
            'tokenRequired'  => true,
        ],
        'shipmentCreate' => [
            'path'           => '/shipment',
            'method'         => 'POST',
            'paramsRequired' => [
                'id'   => self::PARAM_TYPE_INTEGER,
                'name' => self::PARAM_TYPE_STRING,
            ],
            'tokenRequired'  => true,
        ],
        'shipmentFetch'  => [
            'path'           => '/shipment/{id}',
            'method'         => 'GET',
            'paramsRequired' => [
                'id' => self::PARAM_TYPE_INTEGER,
            ],
            'tokenRequired'  => true,
        ],
        'shipmentUpdate' => [
            'path'           => '/shipment/{id}',
            'method'         => 'PUT',
            'paramsRequired' => [
                'id'   => self::PARAM_TYPE_INTEGER,
                'name' => self::PARAM_TYPE_STRING,
            ],
            'tokenRequired'  => true,
        ],
        'shipmentDelete' => [
            'path'           => '/shipment/{id}',
            'method'         => 'DELETE',
            'paramsRequired' => [
                'id' => self::PARAM_TYPE_INTEGER,
            ],
            'tokenRequired'  => true,
        ],
        'shipmentSend'   => [
            'path'           => '/shipment/{id}/send',
            'method'         => 'POST',
            'paramsRequired' => [
                'id' => self::PARAM_TYPE_INTEGER,
            ],
            'tokenRequired'  => true,
        ],
    ];

    /**
     * @param string $login
     * @param string $password
     * @return string
     * @throws YsbmTestTaskApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(string $login, string $password)
    {
        $response = $this->send('auth', ['email' => $login, 'password' => $password]);
        return $response['data'][0]['token'];
    }

    /**
     * @param string $action
     * @param array $params
     * @param string|null $token
     * @return array
     * @throws YsbmTestTaskApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $action, array $params, ?string $token = null)
    {
        try {
            $this->check($action, $params, $token);

            [$url, $paramsForBody] = $this->substituteUrl($action, $params);

            $actionDetail = self::$actions[$action];

            /** @var $client Client */
            $client = new Client();
            $options['headers'] = empty($token)
                ? ['Content-Type' => 'application/json']
                : ['Content-Type' => 'application/json', 'Authorization' => "Bearer $token"];

            $options['body'] = json_encode($this->camelCaseToUndelineArrayKeys($paramsForBody));

            $res = $client->request(
                $actionDetail['method'],
                env('YSBM_GRUOP_TEST_TASK_API_URL') . $url,
                $options);
        } catch (\InvalidArgumentException $e) {
            throw new YsbmTestTaskApiException($e->getMessage());
        } catch (ClientException $e) {
            $message = $e->getMessage();
            preg_match('/\{"error":"(.+)"\}/', $message, $matches);
            if (in_array('token_not_provided', $matches)) {
                throw new YsbmTestTaskApiTokenNotProvidedException('YSBM API token not provided');
            }

            preg_match('/\{"error":"(.+)"/', $message, $matches);
            if (in_array('token_expired', $matches)) {
                throw new YsbmTestTaskApiTokenExpiredException('Token for YSBM API server has been expired');
            }

            preg_match('/\{"message":"Invalid Credentials"/', $message, $matches);
            if (is_array($matches) && count($matches) > 0) {
                throw new YsbmTestTaskApiInvalidCredentialsException('YSBM API invalid credentials');
            }
        }

        if ($res->getStatusCode() !== 200 && $res->getStatusCode() !== 204) {
            throw new YsbmTestTaskApiException('YSBM API server return wrong status code');
        }

        $res = json_decode($res->getBody()->getContents(), true);

        Log::info('YSBM API server response - ' . json_encode($res));

        return $res;
    }

    /**
     * @param string $action
     * @param array $params
     * @param string|null $token
     * @throws \InvalidArgumentException
     */
    private function check(string $action, array $params, ?string $token = null)
    {
        if (empty($action) || !is_string($action) || !array_key_exists($action, self::$actions)) {
            throw new \InvalidArgumentException('Bad action for YSBM API');
        }

        if (self::$actions[$action]['tokenRequired'] && empty($token)) {
            throw new \InvalidArgumentException('No token for YSBM API');
        }

        if (!empty(self::$actions[$action]['paramsRequired'])) {
            foreach (self::$actions[$action]['paramsRequired'] as $param => $type) {
                $isParamFounded = false;
                foreach ($params as $apiParam => $value) {
                    if ($apiParam === $param) {
                        $isParamFounded = true;
                        if (!call_user_func(self::$paramTypeFunctionsCheck[$type], $value)) {
                            throw new \InvalidArgumentException('Bad parameter for YSBM API action');
                        }
                        break;
                    }
                }
                if (!$isParamFounded) {
                    throw new \InvalidArgumentException('Required parameter for YSBM API action absent');
                }
            }
        }
    }

    /**
     * @param string $action
     * @param array $params
     * @return array
     */
    private function substituteUrl(string $action, array $params)
    {
        $url = self::$actions[$action]['path'];
        $paramsForBody = $params;
        $url = preg_replace_callback(
            '/\{[^\}]*\}/',
            function ($matches) use ($params, &$paramsForBody) {
                foreach ($matches as $match) {
                    foreach ($params as $param => $value) {
                        if ('{' . $param . '}' === $match) {
                            unset($paramsForBody[$param]);
                            return urldecode($value);
                        }
                    }
                }
            },
            $url
        );
        if ($url === null) {
            throw new \InvalidArgumentException('Error has been found when trying to substitute parameters to'
                . ' YSBM API action URL');
        }
        return [$url, $paramsForBody];
    }

    /**
     * @param $array
     * @return array
     */
    private function camelCaseToUndelineArrayKeys($array)
    {
        $res = [];
        foreach ($array as $key => $value) {
            $newKey = $this->camelCaseToUndeline($key);
            $res[$newKey] = $value;
        }
        return $res;
    }

    /**
     * @param string $input
     * @return string
     */
    private function camelCaseToUndeline(string $input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}