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
         * API No. 12
         * API Name : 도움이 돼요/안돼요 등록 API (n명에게 도움이 되었습니다 도 바뀌어야 한다)
         * 마지막 수정 날짜 : 21.02.18
         */
        case "isHelpfulReview":
            http_response_code(200);

            $reviewID = $vars['reviewID'];
            $userID = $vars['userID'];
            $isHelpful = $vars['isHelpful'];
            $printMessage = "";

            if($isHelpful == 1) $printMessage = '돼요';
            elseif($isHelpful == 0) $printMessage = '안돼요';
            else {
                $res->isSuccess = FALSE;
                $res->code = 2026;
                $res->message = "isHelpful 값을 0 또는 1로 조회하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidReviewID($reviewID)) {
                $res->isSuccess = FALSE;
                $res->code = 2027;
                $res->message = "유효하지 않은 reviewID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = isHelpfulReview($reviewID, $userID, $isHelpful);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "도움이 ".$printMessage." 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 13
         * API Name : 주문한 메뉴 및 옵션 조회 API
         * 마지막 수정 날짜 : 21.02.18
         */
        case "whatIsTheMenu":
            http_response_code(200);

            $orderID = $vars['orderID'];

            if(!isValidOrderID($orderID)) {
                $res->isSuccess = FALSE;
                $res->code = 2028;
                $res->message = "유효하지 않은 orderID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = whatIsTheMenu($orderID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "주문한 메뉴 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 14
         * API Name : 리뷰 등록 API
         * 마지막 수정 날짜 : 21.02.18
         */
        case "registerReview":
            http_response_code(200);

            $orderID = $vars['orderID'];

            if(!isValidOrderID($orderID)) {
                $res->isSuccess = FALSE;
                $res->code = 2028;
                $res->message = "유효하지 않은 orderID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = registerReview($orderID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "주문한 메뉴 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}