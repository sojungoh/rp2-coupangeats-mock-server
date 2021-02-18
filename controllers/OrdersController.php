<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "receiveOrder":
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

            if(empty($req->userAddressID) or empty($req->restaurantID) or empty($req->menu) or 
            empty($req->totalPrice) or empty($req->deliveryRequestStatus) or empty($req->paymentID)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userAddressID = $req->userAddressID;
            $restaurantID = $req->restaurantID;
            $totalPrice = $req->totalPrice;
            $deliveryRequestStatus = $req->deliveryRequestStatus;
            $paymentID = $req->paymentID;

            if(!isUserAddressIDExist($userAddressID)){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "존재하지 않는 userAddressID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }
            if(!isRestaurantExist($restaurantID)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "존재하지 않는 restaurantID 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isPaymentValid($paymentID)){
                $res->isSuccess = FALSE;
                $res->code = 2020;
                $res->message = "존재하지 않는 paymentID 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match('/REQUEST_[A-F]/', $deliveryRequestStatus)){
                $res->isSuccess = FALSE;
                $res->code = 2019;
                $res->message = "양식에 맞는 파라미터 값을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            
            if(empty($req->couponCode)){
                $couponCode = null;
            }else{
                $couponCode = $req->couponCode;

                if(!isCouponCodeValid($couponCode)){
                    $res->isSuccess = FALSE;
                    $res->code = 2016;
                    $res->message = "잘못된 쿠폰번호 입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if(!isAvaliableCoupon($restaurantID, $couponCode)){
                    $res->isSuccess = FALSE;
                    $res->code = 2018;
                    $res->message = "사용할 수 없는 쿠폰입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(empty($req->ownerRequest)){
                $ownerRequest = null;
            }else{
                $ownerRequest = $req->ownerRequest;
            }

            if(empty($req->isSpoonNeed)){
                $isSpoonNeed = 1;
            }else{
                $isSpoonNeed = $req->isSpoonNeed;

                if($isSpoonNeed != 0 AND $isSpoonNeed != 1){
                    $res->isSuccess = FALSE;
                    $res->code = 2019;
                    $res->message = "양식에 맞는 파라미터 값을 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(empty($req->deliveryRequest)){
                $deliveryRequest = null;
            }else{
                $deliveryRequest = $req->deliveryRequest;
                }

            for($x = 0; $x < count($req->menu); $x++){
                $menu[$x] = $req->menu[$x];

                if(empty($menu[$x]->menuID) or empty($menu[$x]->menuQuantity) or empty($menu[$x]->price)){
                    $res->isSuccess = FALSE;
                    $res->code = 2011;
                    $res->message = "필수 요청 파라미터를 모두 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $menuID = $menu[$x]->menuID;
                $menuQuantity = $menu[$x]->menuQuantity;
                $price = $menu[$x]->price;

                if(!isMenuIDValid($restaurantID, $menuID)){
                    $res->isSuccess = FALSE;
                    $res->code = 2017;
                    $res->message = "유효하지 않은 menuID 입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                if(!empty($req->menu[$x]->subOptionID)){
                    for($y = 0; $y < count($req->menu[$x]->subOptionID); $y++){
                        
                        $subOptionID = $req->menu[$x]->subOptionID[$y];

                        if(!isSubOptionIDValid($subOptionID, $menuID)){
                            $res->isSuccess = FALSE;
                            $res->code = 2021;
                            $res->message = "유효하지 않은 subOptionID 입니다.";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break 3;
                        }

                    }
                }
                
            }

            $orderID = receiveOrder($userID, $restaurantID, $userAddressID, $couponCode, $paymentID, $totalPrice,
            $ownerRequest, $isSpoonNeed, $deliveryRequestStatus, $deliveryRequest);

            if($couponCode != null){
                changeUsedCouponStatus($userID, $couponCode);
            }

            $menu = array();
            $subOptionID = array();

            for($x = 0; $x < count($req->menu); $x++){
                $menu[$x] = $req->menu[$x];

                $menuID = $menu[$x]->menuID;
                $menuQuantity = $menu[$x]->menuQuantity;
                $price = $menu[$x]->price;

                if(!isset($req->menu[$x]->subOptionID)){
                    $subOptionID = null;
                    putOrderMenu($orderID, $menuID, $subOptionID, $menuQuantity, $price);
                }else{
                    for($y = 0; $y < count($req->menu[$x]->subOptionID); $y++){
                        $subOptionID = $req->menu[$x]->subOptionID[$y];

                        putOrderMenu($orderID, $menuID, $subOptionID, $menuQuantity, $price);
                    }
                }
            }

            $res->result = new StdClass();
            $res->result->restaurantInfo = getOrderRestaurantInfo($orderID);
            $res->result->orderInfo = getOrderInfo($orderID);

            $menuList = array();
            $menuList = getOrderMenuInfo($orderID);
            for($x = 0; $x < count($menuList); $x++){
                $menuID = $menuList[$x]['menuID'];

                $res->result->menuInfo[$x] = $menuList[$x];
                $res->result->menuInfo[$x]['subInfo'] = getSubOptionInfo($orderID, $menuID);
            }
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "매장에서 주문을 확인하고 있습니다.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "cancelOrder":
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

            $orderID = $vars['orderID'];

            $userIDByOrderID = getUserIDByOrderID($orderID);

            if(!isOrderIDExist($orderID)){
                $res->isSuccess = FALSE;
                $res->code = 2022;
                $res->message = "존재하지 않는 orderID 입니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            if($userID != $userIDByOrderID){
                $res->isSuccess = FALSE;
                $res->code = 2023;
                $res->message = "주문취소 권한이 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                break;
            }

            cancelOrder($orderID);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "주문취소 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "getOrderList":
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

            $q = $_GET['q'];

            if($q != 'past' AND $q != 'now'){
                $res->isSuccess = FALSE;
                $res->code = 2019;
                $res->message = "양식에 맞는 파라미터 값을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($q == 'past'){
                $pastOrders = array();
                $pastOrders = getPastOrders($userID);
                
                for($x = 0; $x < count($pastOrders); $x++){

                    $res->orderList[$x] = $pastOrders[$x];
                    $orderID = $pastOrders[$x]['orderID'];

                    $menuList = array();
                    $menuList = getOrderMenuInfo($orderID);

                    for($y = 0; $y < count($menuList); $y++){
                        $menuID = $menuList[$y]['menuID'];

                        $res->orderList[$x]['menuInfo'][$y] = $menuList[$y];
                        $res->orderList[$x]['menuInfo'][$y]['subInfo'] = getSubOptionInfo($orderID, $menuID);
                    }
                }
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "과거 주문 내역 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($q == 'now'){
                $preparingOrders = array();
                $preparingOrders = getPreparingOrders($userID);

                for($x = 0; $x < count($preparingOrders); $x++){

                    $res->orderList[$x] = $preparingOrders[$x];
                    $orderID = $preparingOrders[$x]['orderID'];

                    $menuList = array();
                    $menuList = getOrderMenuInfo($orderID);

                    for($y = 0; $y < count($menuList); $y++){
                        $menuID = $menuList[$y]['menuID'];

                        $res->orderList[$x]['menuInfo'][$y] = $menuList[$y];
                        $res->orderList[$x]['menuInfo'][$y]['subInfo'] = getSubOptionInfo($orderID, $menuID);
                    }
                }
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "준비중 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}