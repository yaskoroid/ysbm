<?php

namespace App\Http\Controllers;

use App\Exceptions\YsbmTestTaskApiTokenExpiredException;
use App\Exceptions\YsbmTestTaskApiException;
use App\Helpers\YsbmTestTaskApiWorker;

class AbstractController extends Controller
{
    /**
     * @param callable $actionSend
     * @return string
     */
    protected function authSend(callable $actionSend)
    {
        try {
            $ysbmApiWorker = new YsbmTestTaskApiWorker();
            return $actionSend($ysbmApiWorker);
        } catch (YsbmTestTaskApiTokenExpiredException $e) {
            return 'login';
        } catch (YsbmTestTaskApiException $e) {
            return ['error', $e->getMessage()];
        }
    }
}
