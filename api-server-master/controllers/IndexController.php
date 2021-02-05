<?php
require dirname(__FILE__) . '/function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
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
        /*
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 5
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUserDetail":
            http_response_code(200);

            $res->result = getUserDetail($vars["userIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
      
        case "createUser":
            http_response_code(1000);

            $email = $req->emailAddress;
            $pwdHash = password_hash($req->password, PASSWORD_DEFAULT);
            $name = $req->name;
            $phoneNumber = $req->phoneNumber;
            $aggrement = Array();
            $aggrement = $req->aggrement;

            for($x = 0; $x < count($aggrement); $x++){
                if($aggrement[$x] != TRUE){
                    $res->isSuccess = FALSE;
                    $res->code = 2000;
                    $res->message = "필수 항목에 모두 동의해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(isEmailExist($email)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "이미 가입된 이메일 주소입니다. 다른 이메일을 입력하여 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isPhoneNumberExist($phoneNumber)){
                $emailByPhone = getEmailByPhoneNumber($phoneNumber);

                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "{$emailByPhone} 아이디(이메일)로 가입된 휴대폰 번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = createUser($name, $phoneNumber, $email, $pwdHash);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
