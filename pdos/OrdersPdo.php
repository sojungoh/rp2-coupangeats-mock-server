<?php

function receiveOrder($userID, $restaurantID, $userAddressID, $couponCode, $paymentID, 
$totalPrice, $ownerRequest, $isSpoonNeed, $deliveryRequestStatus, $deliveryRequest){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `order` (userID, restaurantID, userAddressID, couponCode, 
    paymentID, price, ownerRequest, isSpoonNeed, deliveryRequestStatus, deliveryRequest) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $restaurantID, $userAddressID, $couponCode, $paymentID, 
    $totalPrice, $ownerRequest, $isSpoonNeed, $deliveryRequestStatus, $deliveryRequest]);

    $orderID = $pdo->lastInsertId();

    $st = null;
    $pdo = null;

    return $orderID;
}

function putOrderMenu($orderID, $menuID, $subOptionID, $menuQuantity, $price){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO cart (orderID, menuID, subOptionID, menuQuantity, price)
    VALUES (?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$orderID, $menuID, $subOptionID, $menuQuantity, $price]);

    $st = null;
    $pdo = null;
}

function getOrderRestaurantInfo($orderID){
    $pdo = pdoSqlConnect();
    $query = "SELECT restaurant.id AS restaurantID, restaurant.title, address.x, address.y
    FROM `order`
    INNER JOIN restaurant ON restaurant.id = `order`.restaurantID
    INNER JOIN address ON address.addressID = restaurant.addressID
    WHERE `order`.id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getOrderInfo($orderID){
    $pdo = pdoSqlConnect();
    $query = "SELECT `order`.id AS orderID, address.address, ifnull(address.buildingName, 'no value') AS buildingName,
    ifnull(user_address.detail, 'no value') AS detailAddress,
    `order`.price AS totalPrice, 
    payment.id AS paymentID, 
    payment.paymentName, method AS paymentMethod, `number` AS paymentNumber
    FROM `order`
    INNER JOIN user_address ON user_address.id = `order`.userAddressID
    INNER JOIN address ON address.addressID = user_address.addressID
    INNER JOIN payment ON `order`.paymentID = payment.id
    WHERE `order`.id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getOrderMenuInfo($orderID){
    $pdo = pdoSqlConnect();
    $query = "SELECT cart.menuID, menu.menuName, cart.menuQuantity, cart.subOptionID, sub_option.subName
    FROM cart
    INNER JOIN menu ON menu.menuID = cart.menuID
    LEFT JOIN sub_option ON sub_option.subID = cart.subOptionID
    WHERE cart.orderID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isAvaliableCoupon($restaurantID, $couponCode){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM user_coupon 
    WHERE restaurantID = ? AND `code` = ? AND `status` = 'applied') 
    AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $couponCode]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function changeUsedCouponStatus($userID, $couponCode){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_coupon
    INNER JOIN coupon ON user_coupon.`code` = coupon.`code`
    SET user_coupon.`status` = CASE WHEN recyclable = 0 THEN 'used' ELSE 'applied' END
    WHERE userID = ? AND user_coupon.`code` = ?; ";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $couponCode]);

    $st = null;
    $pdo = null;
}

function isPaymentValid($paymentID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM payment WHERE id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$paymentID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isMenuIDValid($restaurantID, $menuID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM menu WHERE restaurantID = ? AND menuID = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $menuID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isSubOptionIDValid($subOptionID, $menuID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM sub_option
    INNER JOIN option_title ON sub_option.optionTitleID = option_title.optionTitleID
    INNER JOIN option_order ON option_title.optionTitleID = option_order.optionTitleID
    INNER JOIN menu ON menu.menuOptionID = option_order.optionID
    WHERE sub_option.subID = ? AND menu.menuID = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$subOptionID, $menuID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getUserIDByOrderID($orderID){
    $pdo = pdoSqlConnect();
    $query = "SELECT userID FROM `order`
    WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userID'];
}

function isOrderIDExist($orderID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM `order` WHERE id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function cancelOrder($orderID){
    $pdo = pdoSqlConnect();
    $query = "UPDATE `order` SET `status` = 0 WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);

    $st = null;
    $pdo = null;
}

function getPastOrder($userID){
    
}