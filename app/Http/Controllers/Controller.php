<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //

    public function responseSuccess(array $data)
    {
        return response()->json([
            'code' => 'S01',
            'data' => $data
        ], 200);
    }

    public function responseFail($message, String $code, int $httpCode)
    {
        return response()->json([
            'code' => $code,
            'message' => $message
        ], $httpCode);
    }
}
