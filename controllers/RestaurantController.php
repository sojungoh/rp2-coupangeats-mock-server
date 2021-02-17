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
                $res->code = 3005;
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
         * API Name : 음식점 즐겨찾기 등록 및 해제 API
         * 마지막 수정 날짜 : 21.02.17
         */
        case "favorite":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];
            $userID = $vars['userID'];

            if(!isRegisteredFavorite($restaurantID, $userID)) { //이 유저가 이 음식점에 등록한 적이 있나? 있으면 1 없으면 0
                                                                //처음 등록이야! isRegisteredFavorite 값이 0인 경우!
                favorite($restaurantID, $userID);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "즐겨찾기 등록 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            else { //등록한 적 있네 있어.
                if(isAlreadyFavorite($restaurantID, $userID)) { //등록한 적 있는데, 그게 지금 즐겨찾기 상태(1)인 경우, 해제해줘야 해.
                    deleteFavorite($restaurantID, $userID);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "즐겨찾기 해제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else { //등록한 적 있는데, 그게 지금 해제 상태(0)인 경우, 다시 등록해줘야 해.
                    reFavorite($restaurantID, $userID);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "즐겨찾기 등록 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

        /*
         * API No. 7
         * API Name : 메뉴 상세조회 API
         * 마지막 수정 날짜 : 21.02.17
         */
        case "menuDetail":
            http_response_code(200);

            $menuID = $vars['menuID'];

            if(!isValidMenuID($menuID)) {
                $res->isSuccess = TRUE;
                $res->code = 2017;
                $res->message = "유효하지 않은 menuID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = menuDetail($menuID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "메뉴 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8
         * API Name : 메뉴 옵션 조회 API
         * 마지막 수정 날짜 : 21.02.17
         */
        case "menuOptions":
            http_response_code(200);

            $menuID = $vars['menuID'];

            if(!isValidMenuID($menuID)) {
                $res->isSuccess = FALSE;
                $res->code = 2017;
                $res->message = "유효하지 않은 menuID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isNotExistOptions($menuID)) {
                $res->isSuccess = FALSE;
                $res->code = 3006;
                $res->message = "옵션이 없는 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = menuOptions($menuID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "메뉴 옵션 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 9
         * API Name : 매장/원산지정보 조회 API
         * 마지막 수정 날짜 : 21.02.18
         */
        case "restaurantDetail":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];

            if(!isValidRestaurantID($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "유효하지 않은 restaurantID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = restaurantDetail($restaurantID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "매장/원산지조회 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        /*
         * API No. 10
         * API Name : 리뷰 기본정보 조회 API
         * 마지막 수정 날짜 : 21.02.18
         */
        case "reviewInfo":
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];

            if(!isValidRestaurantID($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "유효하지 않은 restaurantID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isExistReview($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 3007;
                $res->message = "리뷰가 등록되지 않은 음식점입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = reviewInfo($restaurantID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "리뷰 기본정보 조회 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        /*
         * API No. 11
         * API Name : 리뷰 필터적용 조회 API
         * 마지막 수정 날짜 : 21.02.17
         */
        case "reviewFilter": //?isPhotoReview=&align=
            http_response_code(200);

            $restaurantID = $vars['restaurantID'];
            $isPhotoReview = $_GET['isPhotoReview'];
            $align = $_GET['align'];

            if(!isValidRestaurantID($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "유효하지 않은 restaurantID 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(($isPhotoReview != 0 && $isPhotoReview != 1) || $isPhotoReview == null) {
                $res->isSuccess = FALSE;
                $res->code = 2024;
                $res->message = "isPhotoReview key값을 0 또는 1로 조회하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(($align != 'latest' && $align != 'helpful' && $align != 'highRate' && $align != 'lowRate') || $align == null) {
                $res->isSuccess = FALSE;
                $res->code = 2025;
                $res->message = "align key값을 'latest', 'helpful', 'highRate', 'lowRate' 중 하나로 조회하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isExistReview($restaurantID)) {
                $res->isSuccess = FALSE;
                $res->code = 3007;
                $res->message = "리뷰가 등록되지 않은 음식점입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = reviewFilter($restaurantID, $isPhotoReview, $align);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "리뷰 '".$align."' 필터 적용 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}