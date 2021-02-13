<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "getRestaurantCoupon":
            http_response_code(200);

            $restaurantID = $_GET['restaurant-id'];

            if(!isRestaurantExist($restaurantID)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "존재하지 않는 restaurantID 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(getRestaurantCoupon($restaurantID) == null){
                $res->isSuccess = FALSE;
                $res->code = 3004;
                $res->message = "음식점에서 발급한 쿠폰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->couponInfo = getRestaurantCoupon($restaurantID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "음식점 쿠폰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "issueCoupon":
            http_response_code(200);
            
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];

            if(!isValidJWT($jwt, JWT_SECRET_KEY)){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "유효하지 않은 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $userID = getDataByJWToken($jwt, JWT_SECRET_KEY)->userID;

            if(!isUserIDExist($userID)){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "존재하지 않는 사용자의 JWT 토큰입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if(empty($req->couponCode)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $couponCode = $req->couponCode;

            if(!isCouponCodeValid($couponCode)){
                $res->isSuccess = FALSE;
                $res->code = 2016;
                $res->message = "잘못된 쿠폰번호 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            
            $restaurantID = getRestaurantIDByCode($couponCode);
            checkExpiredCoupon();

            if(isCouponAlreadyExist($restaurantID, $couponCode)){
                $res->isSuccess = FALSE;
                $res->code = 3005;
                $res->message = "이미 발급 받으신 쿠폰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            issueCoupon($userID, $restaurantID, $couponCode);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "쿠폰 받기 완료";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
            
    
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}