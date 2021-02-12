<?php

function isAddressExist($x, $y){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM address WHERE x = ? AND y = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$x, $y]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function addAddress($x, $y){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO address (x, y) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$x, $y]);

    $st = null;
    $pdo = null;
}

function getAddressID($x, $y){
    $pdo = pdoSqlConnect();
    $query = "SELECT addressID FROM address WHERE x = ? AND y = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$x, $y]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['addressID'];
}

function isHomeExist($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM user_address WHERE userID = ? AND `type` = 'home') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isCompanyExist($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM user_address WHERE userID = ? AND `type` = 'company') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getHomeUserAddressID($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id FROM user_address WHERE userID = ? AND `type` = 'home';";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['id'];
}

function getCompanyUserAddressID($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id FROM user_address WHERE userID = ? AND `type` = 'company';";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['id'];
}

function addUserAddress($userID, $addressID, $detail, $type, $nickname){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user_address (userID, addressID, detail, `type`, nickname) VALUES (?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $addressID, $detail, $type, $nickname]);

    $st = null;
    $pdo = null;
}

function getUserAddressList($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id AS userAddressID, address.x, address.y, `type`, ifnull(detail, 'no value') AS detailAddress, ifnull(nickname, 'no value') AS nickname, `status`
    FROM user_address
    INNER JOIN address ON user_address.addressID = address.addressID
    WHERE userID = ?
    ORDER BY (
          CASE `type`
          WHEN 'home' THEN 1
          WHEN 'company' THEN 2
          ELSE 3 END ), updatedAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isUserAddressIDExist($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM user_address WHERE id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getUserIDByUserAddressID($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "SELECT userID FROM user_address WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userID'];
}

function getUserAddress($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id AS userAddressID, address.x, address.y, `type`, ifnull(detail, 'no value') AS detailAddress, ifnull(nickname, 'no value') AS nickname, `status`
    FROM user_address
    INNER JOIN address ON user_address.addressID = address.addressID
    WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function refreshDeliveryAddress($userID){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_address
    SET `status` = 0
    WHERE userID = ? AND `status` = 1;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);

    $st = null;
    $pdo = null;
}

function setDeliveryAddress($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_address
    SET `status` = 1
    WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);

    $st = null;
    $pdo = null;
}

function editUserAddress($addressID, $detail, $type, $nickname, $userAddressID){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_address
    SET addressID = ?, detail = ?, `type` = ?, nickname = ?
    WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$addressID, $detail, $type, $nickname, $userAddressID]);

    $st = null;
    $pdo = null;
}

function deleteUserAddress($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM user_address WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);

    $st = null;
    $pdo = null;
}

function changeTypeToElse($userAddressID){
    $pdo = pdoSqlConnect();
    $query = "UPDATE user_address
    SET `type` = 'else'
    WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userAddressID]);

    $st = null;
    $pdo = null;
}


