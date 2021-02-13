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
    WHERE restaurantID = ? AND `code` = ? AND `status` = 'applied') AS exist;";

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
    $query = "UPDATE user_coupon A 
    INNER JOIN coupon B 
    SET A.`status` = 'expired'
    WHERE B.endAt <= now();";

    $st = $pdo->prepare($query);
    $st->execute();

    $st = null;
    $pdo = null;
}