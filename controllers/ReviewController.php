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
         * API Name : 도움이 돼요/안돼요 등록 API : 리뷰 도움됨 누르면 n명에게 도움이 되었습니다 표시되고, 그 상태(1)에서 안 됨 누르면 저 글자 없어져야 한다.
         * 마지막 수정 날짜 : 21.02.19
         */
        case "isHelpfulReview":
            http_response_code(200);

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];

            $reviewID = $vars['reviewID'];
            $userID = getDataByJWToken($jwt, JWT_SECRET_KEY)->userID;
            $isHelpful = $req->isHelpful;
            $wasHelpful = wasHelpfulReview($reviewID, $userID);

            if(!isValidJWT($jwt, JWT_SECRET_KEY)){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "유효하지 않은 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if(!isUserIDExist($userID)){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "존재하지 않는 사용자의 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if(!isValidReviewID($reviewID)) {
                $res->isSuccess = FALSE;
                $res->code = 2027;
                $res->message = "유효하지 않은 reviewID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($isHelpful == 1) {

                if($wasHelpful == 1) { //도움 됨을 또 누른 경우 = 도움 됨/안됨 둘 다 아님 = 9로 변경.
                    noneHelpful($reviewID, $userID);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "도움 해제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else { //도움 된다(1)로 바꾸거나 등록하고 싶다.
                    isHelpful($reviewID, $userID, $isHelpful);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "도움 됨 등록/변경 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            elseif($isHelpful == 0) {

                if($wasHelpful == 0) { //도움 안됨을 또 누른 경우 = 도움 됨/안됨 둘 다 아님 = 9로 변경.
                    noneHelpful($reviewID, $userID);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "도움 해제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else { //도움 안된다(0)고 등록하거나 바꾸고 싶다.
                    isNotHelpful($reviewID, $userID, $isHelpful);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "도움 안됨 등록/변경 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            else {
                $res->isSuccess = FALSE;
                $res->code = 2026;
                $res->message = "isHelpful 값을 0 또는 1로 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

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