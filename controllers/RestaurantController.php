<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /* ************************       HeatherAPI      ************************ */
        /*
         * API No. 1
         * API Name : 검색화면 카테고리 조회 API
         * 마지막 수정 날짜 : 21.02.08
         */
        case "categories":
            http_response_code(200);
            $res->result = getCategories();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카테고리 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : 검색필터 항목조회 API
         * 마지막 수정 날짜 : 21.02.08
         */
        case "filters":
            http_response_code(200);
            $res->result = getFilters();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "검색필터 항목조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}