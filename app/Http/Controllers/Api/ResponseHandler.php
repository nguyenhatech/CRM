<?php

namespace Nh\Http\Controllers\Api;

trait ResponseHandler {

    protected function successResponse($data, $transform = true, $debug = true) {
        if (is_null($data))
            $data = [];

        $response = array_merge([
            'code' => 200,
            'status' => 'success',
        ], $transform ? $this->transform($data) : $data);

        return response()->json($response, $response['code']);
    }

    protected function notFoundResponse() {
        $response = [
            'code' => 404,
            'status' => 'error',
            'data' => 'Resource Not Found',
            'message' => 'Not Found'
        ];
        return response()->json($response, $response['code']);
    }

    public function deleteResponse() {
        $response = [
            'code' => 200,
            'status' => 'success',
            'data' => [],
            'message' => 'Resource Deleted'
        ];
        return response()->json($response, $response['code']);
    }

    public function errorResponse($data, $transform = true) {
        $response = [
            'code' => 422,
            'status' => 'error',
            'data' => $data,
            'message' => 'Unprocessable Entity'
        ];
        return response()->json($response, $response['code']);
    }

    protected function infoResponse($data) {

        $data = [
            'data' => $data
        ];

        $response = array_merge([
            'code' => 200,
            'status' => 'success'
        ], $data);
        return response()->json($response, $response['code']);
    }

    private function _getQueryLog()
    {
        $queries = [];

        if (env('APP_ENV', 'local') == 'production') return $queries;

        $total_query = $total_time = 0;
        foreach (\DB::getQueryLog() as $info) {
            $query = vsprintf(
                // Replace pdo bindings to printf string bindings escaping % char.
                str_replace(['%', '?'], ['%%', "'%s'"], $info['query']),
                // Convert all query attributes to strings.
                $info['bindings']
            );

            $queries['queries'][] = [
                'query' => $query,
                'time' => $info['time'] / 1000 . " ms",
            ];

            $total_query += 1;
            $total_time += $info['time'] / 1000;
        }

        $queries['queries'][] = [
            'total_query' => $total_query,
            'total_time' => $total_time . " ms",
        ];

        return $queries;
    }
}
