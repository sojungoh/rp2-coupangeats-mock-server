<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "registerPayment":
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
            if($_GET['method'] != 'account' AND $_GET['method'] != 'card'){
                $res->isSuccess = FALSE;
                $res->code = 2019;
                $res->message = "양식에 맞는 파라미터 값을 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(empty($req->paymentName) OR empty($req->number)){
                $res->isSuccess = FALSE;
                $res->code = 2019;
                $res->message = "양식에 맞는 파라미터 값을 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $method = $_GET['method'];
            $paymentName = $req->paymentName;
            $number = $req->number;

            registerPayment($userID, $paymentName, $number, $method);
            $res->paymentList = getPaymentList($userID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "결제수단 추가 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "getPaymentList":
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

            $res->result = new stdClass();
            $res->result->paymentList = getPaymentList($userID);
            $res->code = 1000;
            $res->message = "결제 관리 조회 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;
            
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}