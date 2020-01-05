<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Constructor de la clase
     *
     */
    public function __construct()
    {
    //$this->middleware('jwt.auth', ['except' => ['login']]);
    }

    /**
     * @param Request $request
     * @return 
     */
    public function sendPaymentToProcessor(Request $request)
    {
        return json_encode([
            'process' => $request->form,
        ]);
    }
}
