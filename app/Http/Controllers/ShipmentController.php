<?php

namespace App\Http\Controllers;

use Auth;
use View;
use App\Helpers\YsbmTestTaskApiWorker;
use Illuminate\Contracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ShipmentController extends AbstractController
{
    /**
     * @return \Illuminate\Http\RedirectResponse|Contracts\View\View
     */
    public function shipments()
    {
        $response = $this->authSend(function (YsbmTestTaskApiWorker $ysbmApiWorker) {
            return $ysbmApiWorker->send('shipmentList', [], Auth::user()->ysbm_api_token);
        });
        if ($response === 'login') {
            return Redirect::route('logout');
        }
        return View::make('shipments.list')->with(['shipments' => $response['data']['shipments']]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|Contracts\View\View
     */
    public function shipmentCreate(Request $request)
    {
        if ($request->method() !== 'POST') {
            return View::make('shipments.create')->with([
                'id'      => '',
                'name'    => '',
                'message' => '',
            ]);
        }

        $params = [
            'id'      => $request->get('id'),
            'name'    => $request->get('name'),
        ];

        $response = $this->authSend(function (YsbmTestTaskApiWorker $ysbmApiWorker) use ($params) {
            return $ysbmApiWorker->send('shipmentCreate', $params, Auth::user()->ysbm_api_token);
        });

        if ($response === 'login') {
            return Redirect::route('logout');
        }

        if (is_array($response) && isset($response[0]) && $response[0] === 'error') {
            [$error, $message] = $response;
            return View::make('shipments.create')->with(array_merge($params, ['message' => $message]));
        }

        return Redirect::route('shipments');
    }
}
