<?php

/**
 * return a succesful response
 * @param string $message
 * @param boolean $status
 * @param string $response_name
 * @param string $response_value
 * @param string $data
 * @return Illuminate\Http\Response
 */
function successResponse(String $message, $status, $data = null, $code = 200, String $response_name = null, $response_value = null,)
{
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data ?? null
    ];
    if ($response_name != null && $response_value != null) {
        $response[$response_name] = $response_value;
    }
    return response()->json($response, $code);
}

/**
 * return a failed response
 * @param \Throwable $ex
 * @param boolean $status
 * @param string $message
 * @return Illuminate\Http\Response
 */
function failedResponse(\Throwable $ex, $status, $message = "Unexpected exception occured. Check logs for more details and try again.")
{
    $response = [
        'status' => $status,
        'message' => $message,
        'error' => $ex->errorInfo[2] ?? $ex->getMessage()
    ];
    return response()->json($response, 500);
}

/**
 * return other response
 * @param int $code
 * @param boolean $status
 * @param string $message
 */
function otherError($code, $status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message
    ], $code);
}
