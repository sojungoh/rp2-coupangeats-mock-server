<?php

function isRestaurantExist($restaurantID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM restaurant WHERE id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getRestaurantCoupon($restaurantID){
    $pdo = pdoSqlConnect();
    $query = "SELECT `code` AS couponCode, discountPrice FROM coupon
    WHERE now() < endAt AND restaurantID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isCouponCodeValid($couponCode){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM coupon WHERE code = ? AND now() < endAt) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$couponCode]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getRestaurantIDByCode($couponCode){
    $pdo = pdoSqlConnect();
    $query = "SELECT restaurantID FROM coupon WHERE `code` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$couponCode]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['restaurantID'];
}

function isCouponAlreadyExist($restaurantID, $couponCode){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM user_coupon 
    WHERE restaurantID = ? AND `code` = ? AND `status` != 'expired') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $couponCode]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function issueCoupon($userID, $restaurantID, $couponCode){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user_coupon (userID, restaurantID, `code`) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $restaurantID, $couponCode]);

    $st = null;
    $pdo = null;
}

function checkExpiredCoupon(){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_coupon 
    INNER JOIN coupon ON user_coupon.`code` = coupon.`code` 
    SET user_coupon.`status` = 'expired'
    WHERE now() >= coupon.endAt;";

    $st = $pdo->prepare($query);
    $st->execute();

    $st = null;
    $pdo = null;
}

function getUserCoupon($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT user_coupon.`code`, coupon.restaurantID, `name`, discountPrice, 
    ifnull(minOrderPrice, 'no value') AS minOrderPrice, endAt, `status`
    FROM coupon
    INNER JOIN user_coupon ON user_coupon.`code` = coupon.`code`
    WHERE userID = ? AND `status` != 'used';";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getAvailableCoupon($userID, $restaurantID){
    $pdo = pdoSqlConnect();
    $query = "SELECT coupon.`code`, coupon.restaurantID, discountPrice, 
    ifnull(minOrderPrice, 'no value') AS minOrderPrice
    FROM user_coupon
    INNER JOIN coupon ON user_coupon.`code` = coupon.`code`
    WHERE userID = ? AND user_coupon.restaurantID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $restaurantID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}