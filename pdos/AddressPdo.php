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
    $query = "SELECT id AS userAddressID, address.x, address.y, `type`, ifnull('no value', detail) AS detailAddress, ifnull('no value', nickname) AS nickname, `status`
    FROM user_address
    INNER JOIN address ON user_address.addressID = address.addressID
    WHERE userID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
