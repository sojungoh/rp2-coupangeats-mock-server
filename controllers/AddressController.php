<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "addUserAddress":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
            if(!isValidJWT($jwt, JWT_SECRET_KEY)){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "유효하지 않은 JWT 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userID = getDataByJWToken($jwt, JWT_SECRET_KEY)->userID;

            if(!isUserIDExist($userID)){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "존재하지 않는 사용자의 JWT 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $x = $req->x;
            $y = $req->y;
            $type = $req->type;
            $detail = $req->detailAddress;
            $nickname = $req->nickname;

            if($x == null OR $y == null){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "x좌표(경도값)와 y좌표(위도값)는 필수 입력값이에요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match('/^[+-]?\d*(\.?\d*)$/', $x) OR !preg_match('/^[+-]?\d*(\.?\d*)$/', $y)){
                $res->isSuccess = FALSE;
                $res->code = 2012;
                $res->message = "올바르지 않은 x좌표(경도값) 혹은 y좌표(위도값)이에요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($type != 'home' AND $type != 'company' AND $type != 'else'){
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "올바른 type값을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isAddressExist($x, $y)){
                $addressID = getAddressID($x, $y);

                addUserAddress($userID, $addressID, $detail, $type, $nickname);
                $res->result = new stdClass();
                $res->result->addressList = getUserAddressList($userID);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "배달 주소 추가 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            addAddress($x, $y);
            $addressID = getAddressID($x, $y);
            
            addUserAddress($userID, $addressID, $detail, $type, $nickname);
            $res->result = new stdClass();
            $res->result->addressList = getUserAddressList($userID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달 주소 추가 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}