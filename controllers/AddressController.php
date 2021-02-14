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

            if(empty($req->address) OR empty($req->x) OR empty($req->y)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            
            $address = $req->address;
            $x = $req->x;
            $y = $req->y;

            if(!preg_match('/^[+-]?\d*(\.?\d*)$/', $x) OR !preg_match('/^[+-]?\d*(\.?\d*)$/', $y)){
                $res->isSuccess = FALSE;
                $res->code = 2012;
                $res->message = "올바르지 않은 x좌표(경도값) 혹은 y좌표(위도값)이에요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(empty($req->type)){
                $type = 'else';
            }else{
                $type = $req->type;
            }
            if($type != 'home' AND $type != 'company' AND $type != 'else'){
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "올바른 type값을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(empty($req->buildingName)){
                $buildingName = null;
            }else{
                $buildingName = $req->buildingName;
            }
            if(empty($req->detailAddress)){
                $detail = null;
            }else{
                $detail = $req->detailAddress;
            }
            if(empty($req->nickname)){
                $nickname = null;
            }else{
                $nickname = $req->nickname;
            }
            if($type == 'home'){
                if(isHomeExist($userID)){
                    changeTypeToElse(getHomeUserAddressID($userID));
                }
            }
            if($type == 'company'){
                if(isCompanyExist($userID)){
                    changeTypeToElse(getCompanyUserAddressID($userID));
                }
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
            addAddress($address, $buildingName, $x, $y);
            $addressID = getAddressID($x, $y);
            
            addUserAddress($userID, $addressID, $detail, $type, $nickname);
            $res->result = new stdClass();
            $res->result->addressList = getUserAddressList($userID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달 주소 추가 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "getUserAddressList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

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
            $res->result->addressList = getUserAddressList($userID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달주소 관리 조회 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "getUserAddress":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userAddressID = $vars['userAddressID'];

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
            if(!isUserAddressIDExist($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "존재하지 않는 userAddressID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if($userID != getUserIDByUserAddressID($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2015;
                $res->message = "배달지 조회 권한이 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(empty(getUserAddress($userAddressID))){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "배달지 상세 정보를 불러올 수 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            $res->addressDetail = getUserAddress($userAddressID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달지 상세 정보 조회 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "setDeliveryAddress":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
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
            if(empty($req->userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $userAddressID = $req->userAddressID;
            
            if(!isUserAddressIDExist($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "존재하지 않는 userAddressID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if($userID != getUserIDByUserAddressID($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2015;
                $res->message = "배달지 설정 권한이 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            refreshDeliveryAddress($userID);
            setDeliveryAddress($userAddressID);
            $res->addressDetail = getUserAddress($userAddressID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달지 주소 설정 변경 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "editUserAddress":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userAddressID = $vars['userAddressID'];
            
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
            if(empty($req->address) OR empty($req->x) OR empty($req->y) OR empty($req->type)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $address = $req->address;
            $x = $req->x;
            $y = $req->y;
            $type = $req->type;

            if(!preg_match('/^[+-]?\d*(\.?\d*)$/', $x) OR !preg_match('/^[+-]?\d*(\.?\d*)$/', $y)){
                $res->isSuccess = FALSE;
                $res->code = 2012;
                $res->message = "올바르지 않은 x좌표(경도값) 혹은 y좌표(위도값)이에요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if($type != 'home' AND $type != 'company' AND $type != 'else'){
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "올바른 type값을 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(!isUserAddressIDExist($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "존재하지 않는 userAddressID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if($userID != getUserIDByUserAddressID($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2015;
                $res->message = "배달지 수정 권한이 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if(empty($req->buildingName)){
                $buildingName = null;
            }else{
                $buildingName = $req->buildingName;
            }
            if(empty($req->detailAddress)){
                $detail = null;
            }else{
                $detail = $req->detailAddress;
            }
            if(empty($req->nickname)){
                $nickname = null;
            }else{
                $nickname = $req->nickname;
            }
            if($type == 'home'){
                if(isHomeExist($userID)){
                    changeTypeToElse(getHomeUserAddressID($userID));
                }
            }
            if($type == 'company'){
                if(isCompanyExist($userID)){
                    changeTypeToElse(getCompanyUserAddressID($userID));
                }
            }

            if(isAddressExist($x, $y)){
                $addressID = getAddressID($x, $y);

                editUserAddress($addressID, $detail, $type, $nickname, $userAddressID);
                $res->result = new stdClass();
                $res->result->addressList = getUserAddressList($userID);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "배달지 상세 정보 수정 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            addAddress($address, $buildingName, $x, $y);
            $addressID = getAddressID($x, $y);
            
            editUserAddress($addressID, $detail, $type, $nickname, $userAddressID);
            $res->result = new stdClass();
            $res->result->addressList = getUserAddressList($userID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달지 상세 정보 수정 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "deleteUserAddress":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userAddressID = $vars['userAddressID'];
            
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
            if(!isUserAddressIDExist($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "존재하지 않는 userAddressID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if($userID != getUserIDByUserAddressID($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2015;
                $res->message = "배달지 삭제 권한이 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            deleteUserAddress($userAddressID);

            $res->result = new stdClass();
            $res->result->addressList = getUserAddressList($userID); 
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배달지 상세 정보 삭제 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "checkUserAddressType":
            http_response_code(200);
            
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $type = $_GET['type'];
            
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
            if($type != 'home' AND $type != 'company'){
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "올바른 type(쿼리 스트링)을 입력해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(isHomeExist($userID) AND $type == 'home'){
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "기존 등록된 '집' 주소가 있습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(isCompanyExist($userID) AND $type == 'company'){
                $res->isSuccess = FALSE;
                $res->code = 3003;
                $res->message = "기존 등록된 '회사' 주소가 있습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "기존 등록된 {$type} 주소가 없습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "checkUserAddressStatus":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
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

            if(!isDeliveryAddressExist($userID)){
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "설정된 배달지 주소가 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "설정된 배달지 주소가 있습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;
            
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}