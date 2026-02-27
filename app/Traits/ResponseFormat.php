<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ResponseFormat
{
    public function successResponse($message = '', $data = [], $paginate = null)
    {
        $response = [];
        $response['status'] = 'success';
        $response['message'] = $message;
        $response['error_type'] = '';
        $response['data'] = $data;
        if (! empty($paginate)) {
            $response['total'] = $paginate->total();
            $response['totalPage'] = $paginate->lastPage();
            $response['currentPage'] = $paginate->currentPage();
            $response['perPage'] = $paginate->perPage();
        }

        return response()->json($response, Response::HTTP_OK);
    }

    public function errorResponse($message, $type, $statusCode, $data = [])
    {
        $response = [];
        $response['status'] = 'failed';
        $response['message'] = $message;
        $response['error_type'] = $type;
        $response['data'] = $data;

        return response()->json($response, $statusCode);
    }
}
