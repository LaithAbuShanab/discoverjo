<?php

namespace App\Helpers;

class ApiResponse
{
    static function sendResponse($code = 200, $msg = null, $data = null)
    {
        $response = [
            'status'    => $code,
            'msg'       => $msg,
            'data'      => $data,
        ];

        return response()->json($response, $code);
    }

    static function sendResponseError($code = 401, $msg = null)
    {
        $response = [
            'status'    => $code,
            'msg'       => $msg,
        ];

        return response()->json($response, $code);
    }
}
