<?php
require dirname(__FILE__) . '/function.php';
require './vendor/autoload.php';

use Twilio\Rest\Client;

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: application/json; charset=UTF-8');
$req = json_decode(file_get_contents("php://input"));

session_start();

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
      
        case "getUserDetail":
            http_response_code(200);

            $userID = $vars["userID"];

            if(!isUserIDExist($userID)){
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message ="userID를 가져올 수 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $res->result = getUserDetail($userID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "회원 정보를 불러 왔습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;
        
        case "createUser":
            http_response_code(200);
    
            $email = $req->emailAddress;
            $pwdHash = password_hash($req->password, PASSWORD_DEFAULT);
            $name = $req->name;
            $phoneNumber = $req->phoneNumber;
            $aggrement = $req->aggrement;
    
            if($aggrement != TRUE){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "필수 항목에 모두 동의해주세요.";
                echo json_encode($res);
                break;
            }
    
            if(isEmailExist($email)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "이미 가입된 이메일 주소입니다. 다른 이메일을 입력하여 주세요.";
                echo json_encode($res);
                break;
            }

            createUser($name, $phoneNumber, $email, $pwdHash);
    
            $userID = getUserID($email);
    
            if($userID == null){
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "userID를 가져올 수 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
    
            $res->result = new stdClass();
            $res->result->userID = $userID;
            $res->result->jwt = getJWT($userID, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;    
    
    
        case "checkEmail":
            http_response_code(200);
            $email = $req->emailAddress;
                
            if(isEmailExist($email)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "이미 가입된 이메일 주소입니다. 다른 이메일을 입력하여 주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
                
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "사용가능한 이메일입니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;
    
        case "checkPhoneNumber":
            http_response_code(200);
            $phoneNumber = $req->phoneNumber;

            if (!preg_match('/^(010|011|016|017|018|019)[^0][0-9]{4}[0-9]{1,4}/', $phoneNumber)) {
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "유효하지 않은 휴대폰 번호입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
                
            if(isPhoneNumberExist($phoneNumber)){
                $emailByPhone = getEmailByPhoneNumber($phoneNumber);
    
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "{$emailByPhone} 아이디(이메일)로 가입된 휴대폰 번호입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "사용가능한 휴대폰 번호입니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;    

        case "userAuth":
            http_response_code(200);
            $phoneNumber = $req->phoneNumber;
            $_SESSION['randomCode'] = mt_rand(100000, 999999);

            if (!preg_match('/^(010|011|016|017|018|019)[^0][0-9]{4}[0-9]{1,4}/', $phoneNumber)) {
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "유효하지 않은 휴대폰 번호입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $account_sid = 'AC66628d749aa5fb14322bc113125745e6';
            $auth_token = 'd2d167b535ec0fd35e2599238f40f29a';
            $twilio_number = "+15108227026";

            $client = new Client($account_sid, $auth_token);
        
            $client->messages->create(// Where to send a text message (your cell phone?)
                    "+82{$phoneNumber}",
                    array(
                        'from' => $twilio_number,
                        'body' => "{$_SESSION['randomCode']}"
                    )
            );
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "입력하신 번호로 인증번호가 발송되었습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "verifyCode":
            http_response_code(200);
            $smsCode = $req->smsCode;

            if($_SESSION['randomCode'] == null){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "인증번호가 발급되지 않았습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if($smsCode != $_SESSION['randomCode']){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "인증번호가 일치하지 않습니다. 확인 후 다시 이용하여 주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "핸드폰 인증에 성공하였습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            unset($_SESSION['randomCode']);
            break;
        
        case "userLogin":
            http_response_code(200);

            $email = $req->emailAddress;
            $password = $req->password;

            if(!isEmailExist($email)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "회원정보 또는 인증정보가 일치하지 않습니다. 다시 시도해주세요.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if(!checkPassword($email, $password)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "입력하신 아이디 또는 비밀번호가 일치하지 않습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            $userID = getUserID($email);
            $res->result = new stdClass();
            $res->result->userID = $userID;
            $res->result->jwt = getJWT($userID, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
