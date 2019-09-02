<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use Redirect;
use Validator;
use View;
use App\Exceptions\YsbmTestTaskApiException;
use App\Exceptions\YsbmTestTaskApiInvalidCredentialsException;
use App\Exceptions\YsbmTestTaskApiTokenExpiredException;
use App\Helpers\YsbmTestTaskApiWorker;
use App\User;
use Illuminate\Contracts;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|Contracts\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loginAction(Request $request)
    {
        Auth::logout();

        $errors = new MessageBag();
        if ($old = $request->old('errors')) {
            $errors = $old;
        }

        $data = [
            'errors' => $errors,
            'email'  => $request->get('email'),
        ];

        if ($request->server('REQUEST_METHOD') !== 'POST') {
            return View::make('auth.login', $data);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password'  => 'required',
        ]);

        if ($validator->fails()) {
            $data['errors'] = new MessageBag(['pass' => [trans('general.log_in.invalid_login_password')]]);
            return Redirect::route('login')->withInput($data);
        }

        $credentials = [
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];

        $ysbmApiWorker = new YsbmTestTaskApiWorker();
        try {
            if (!Auth::attempt($credentials)
                || $ysbmApiWorker->send('shipmentList', [],  Auth::user()->ysbm_api_token) === null) {
                $error = $this->loginToYsbmApi($ysbmApiWorker, $credentials);
                if ($error) {
                    $data['errors'] = new MessageBag(['pass' => [$error]]);
                    return Redirect::route('login')->withInput($data);
                }
            }
        } catch (YsbmTestTaskApiTokenExpiredException $e) {
            $error = $this->loginToYsbmApi($ysbmApiWorker, $credentials);
            if ($error) {
                $data['errors'] = new MessageBag(['pass' => [$error]]);
                return Redirect::route('login')->withInput($data);
            }
        } catch (YsbmTestTaskApiException $e) {
            $data['errors'] = new MessageBag(['pass' => $e->getMessage()]);
            return Redirect::route('login')->withInput($data);
        }

        return Redirect::route('index');
    }

    /**
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        Auth::logout();
        return Redirect::route('index');
    }

    /**
     * @param YsbmTestTaskApiWorker $ysbmApiWorker
     * @param array $credentials
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function loginToYsbmApi(YsbmTestTaskApiWorker $ysbmApiWorker, array $credentials)
    {
        try {
            $token = $ysbmApiWorker->login($credentials['email'], $credentials['password']);
            $this->updateTokenOrCreateUser($credentials, $token);
        } catch (YsbmTestTaskApiInvalidCredentialsException $e) {
            return trans('general.log_in.invalid_login_password');
        } catch (YsbmTestTaskApiException $e) {
            return $e->getMessage();
        }
        return null;
    }

    /**
     * @param array $credentials
     * @param string $token
     */
    private function updateTokenOrCreateUser(array $credentials, string $token)
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
        } else {
            $user = new User();
            $user->name = $credentials['email'];
            $user->email = $credentials['email'];
            $user->password = Hash::make($credentials['password']);;
        }
        $user->ysbm_api_token = $token;
        $user->save();
    }
}
