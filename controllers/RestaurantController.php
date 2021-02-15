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

        /*
         * API No. 3
         * API Name : 필터적용 검색 API
         * 마지막 수정 날짜 : 21.02.10
         */
        case "filterSearch":
            http_response_code(200);

            $category = $_GET['category'];
            $align = $_GET['align'];
            $isCheetah = $_GET['isCheetah'];
            $deliveryFee = $_GET['deliveryFee'];
            $minimumOrder = $_GET['minimumOrder'];
            $coupone = $_GET['coupone'];

            $res->result = getFilterSearch($category, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupone);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "필터적용 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 4
         * API Name : 음식점 기본 정보 조회 API
         * 마지막 수정 날짜 : 21.02.10
         */
        case "basicInfo":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];

//            if(!$restaurantID) {
//                $res->isSuccess = FALSE;
//                $res->code = 2008;
//                $res->message = "restaurantID를 입력하세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            else
            if(!isValidRestaurantID($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "유효하지 않은 restaurantID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            else {
                $res->result = basicInfo($restaurantID);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "음식점 기본정보 조회 성공";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

        /*
         * API No. 5
         * API Name : 음식점 메뉴 조회 API
         * 마지막 수정 날짜 : 21.02.15
         */
        case "getMenu":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];

            if(!isValidRestaurantID($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "유효하지 않은 restaurantID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isMenuRegistered($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2017;
                $res->message = "메뉴가 등록되지 않은 음식점입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getMenu($restaurantID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "음식점 메뉴 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 6
         * API Name : 음식점 즐겨찾기 등록 API
         * 마지막 수정 날짜 : 21.02.16
         */
        case "favorite":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];
            $userID = $vars['userID'];

            $res->result = favorite($restaurantID, $userID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "즐겨찾기 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}