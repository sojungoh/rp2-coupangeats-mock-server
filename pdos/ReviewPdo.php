<?php

/* **************************     HeatherAPI      ************************* */
//No.12
function isHelpfulReview($reviewID, $userID, $isHelpful)
{
    $pdo = pdoSqlConnect();
    $query = "";

    $st = $pdo->prepare($query);
    $st->execute([$reviewID, $userID, $isHelpful]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidReviewID($reviewID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT review.id FROM review WHERE id =? ) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

//No.13
function whatIsTheMenu($orderID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT    cart.orderID, menu.menuName AS menu, IFNULL(so.subName, '선택안함') AS chosenOption
              FROM      review
              LEFT JOIN cart ON review.orderID = cart.orderID
              LEFT JOIN menu ON cart.menuID = menu.menuID
              LEFT JOIN sub_option AS so ON cart.subOptionID = so.subID
              WHERE     cart.orderID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidOrderID($orderID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM `order` WHERE id=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}