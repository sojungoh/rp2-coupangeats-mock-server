<?php

function registerPayment($userID, $paymentName, $number, $method){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO payment (userID, paymentName, `number`, method)
    VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $paymentName, $number, $method]);

    $st = null;
    $pdo = null;
}

function getPaymentList($userID){
    $pdo = pdoSqlConnect();
    $query = "SELECT id AS paymentID, paymentName, `number`, method FROM payment
    WHERE userID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userID]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}