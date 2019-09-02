<?php

namespace App\Http\Controllers;

use Auth;
use View;
use App\Helpers\YsbmTestTaskApiWorker;
use Illuminate\Contracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ItemController extends AbstractController
{
    /**
     * @param Request $request
     * @param int $shipmentId
     * @return \Illuminate\Http\RedirectResponse|Contracts\View\View
     */
    public function itemCreate(Request $request, int $shipmentId)
    {
        if ($request->method() !== 'POST') {
            return View::make('items.create')->with([
                'id'         => '',
                'shipmentId' => $shipmentId,
                'name'       => '',
                'code'       => '',
                'message'    => '',
            ]);
        }

        $params = [
            'id'         => $request->get('id'),
            'shipmentId' => $shipmentId,
            'name'       => $request->get('name'),
            'code'       => $request->get('code'),
        ];

        $response = $this->authSend(function (YsbmTestTaskApiWorker $ysbmApiWorker) use ($params) {
            return $ysbmApiWorker->send('itemCreate', $params, Auth::user()->ysbm_api_token);
        });

        if ($response === 'login') {
            return Redirect::route('logout');
        }

        if (is_array($response) && isset($response[0]) && $response[0] === 'error') {
            [$error, $message] = $response;
            return View::make('items.create')->with(array_merge($params, ['message' => $message]));
        }

        return Redirect::route('shipments');
    }

    /**
     * @param Request $request
     * @param int $itemId
     * @return \Illuminate\Http\RedirectResponse|Contracts\View\View
     */
    public function itemDelete(Request $request, int $itemId)
    {
        $response = $this->authSend(function (YsbmTestTaskApiWorker $ysbmApiWorker) use ($itemId) {
            return $ysbmApiWorker->send('itemDelete', ['id' => $itemId], Auth::user()->ysbm_api_token);
        });

        if ($response === 'login') {
            return Redirect::route('logout');
        }

        if (is_array($response) && isset($response[0]) && $response[0] === 'error') {
            [$error, $message] = $response;
            return View::make('items.delete')->with(['message' => $message]);
        }

        return Redirect::route('shipments');
    }
}
