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
         * API No. 15
         * API Name : 필터적용 검색 API
         * 마지막 수정 날짜 : 21.02.19
         */
        case "filterSearch":
            http_response_code(200);

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userID = getDataByJWToken($jwt, JWT_SECRET_KEY)->userID;
            $category = $_GET['category'];
            $align = $_GET['align']; //현재 추천순 적용 불가
            $isCheetah = $_GET['isCheetah'];
            $deliveryFee = $_GET['deliveryFee'];
            $minimumOrder = $_GET['minimumOrder'];
            $coupon = $_GET['coupon'];

            if (!isValidJWT($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "유효하지 않은 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if (!isUserIDExist($userID)) {
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "존재하지 않는 사용자의 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if($category == null || $align == null || $isCheetah == null || $deliveryFee == null
               || $minimumOrder == null || $coupon == null) {
                $res->isSuccess = FALSE;
                $res->code = 2029;
                $res->message = "필수 파라미터를 모두 채워주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($align == 'recommend') {
                $res->isSuccess = FALSE;
                $res->code = 2030;
                $res->message = "현재 추천순 필터는 적용되지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            elseif($category == 'new') { //category가 new인 경우
                $result = newFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isOneServing') {
                $result = oneServingFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isKoreanFood') {
                $result = koreanFoodFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isChicken') {
                $result = chickenFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isFlourBasedFood') {
                $result = flourFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isPorkCutlet') {
                $result = porkFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            elseif($category == 'isJokbalOrBossam') {
                $result = jokbalFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);

                if($result == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 3008;
                    $res->message = "조건에 맞는 매장이 없습니다. 검색 조건 변경 후 다시 시도해 보세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "필터적용 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            else { //그 이외에는 현재 지원되지 않음
                //$res->result = filterSearch($userID, $category, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon);
                $res->isSuccess = TRUE;
                $res->code = 3009;
                $res->message = "현재 해당 카테고리는 지원되지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
