<?php

function getUserDetail($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id AS userID, `name`, phoneNumber FROM `user` WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isUserIDExist($userID){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from `user` where id = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isEmailExist($email){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM `user` WHERE email = ?) AS emailExist;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['emailExist'];
}

function isPhoneNumberExist($phoneNumber){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM `user` WHERE phoneNumber = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$phoneNumber]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getEmailByPhoneNumber($phoneNumber){
    $pdo = pdoSqlConnect();
    $query = "SELECT email FROM `user` WHERE phoneNumber = ? ORDER BY createdAt DESC limit 1;";

    $st = $pdo->prepare($query);
    $st->execute([$phoneNumber]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['email'];
}

function createUser($name, $phoneNumber, $email, $pwdHash){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `user` (`name`, phoneNumber, email, `password`) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$name, $phoneNumber, $email, $pwdHash]);

    $st = null;
    $pdo = null;
}

function getUserID($email){
    $pdo = pdoSqlConnect();
    $query = "SELECT id FROM `user` WHERE email = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['id'];
}

function checkPassword($email, $password){
    $pdo = pdoSqlConnect();
    $query = "SELECT `password` AS hash FROM `user` WHERE email = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return password_verify($password, $res[0]['hash']);
}

