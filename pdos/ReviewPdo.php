<?php

/* **************************     HeatherAPI      ************************* */
//No.12
function noneHelpful($reviewID, $userID) //9로 변경
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE review_helpful
              SET    isHelpful = 9
              WHERE  reviewID = ? AND userID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewID, $userID]);

    $st = null;
    $pdo = null;
}

function isHelpful($reviewID, $userID, $isHelpful) //1로 등록 또는 변경
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO review_helpful(userID, reviewID, isHelpful) VALUES(?,?,?)
              ON DUPLICATE KEY UPDATE reviewID = ?, userID = ?, isHelpful = 1;";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $reviewID, $isHelpful, $reviewID, $userID]);

    $st = null;
    $pdo = null;
}

function isNotHelpful($reviewID, $userID, $isHelpful) //0으로 등록 또는 변경
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO review_helpful(userID, reviewID, isHelpful) VALUES(?,?,?)
              ON DUPLICATE KEY UPDATE reviewID = ?, userID = ?, isHelpful = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$userID, $reviewID, $isHelpful, $reviewID, $userID]);

    $st = null;
    $pdo = null;
}

function wasHelpfulReview($reviewID, $userID) //이전에 1로 등록했었으면 1, 0으로 등록했었으면 0, 9로 아무것도 하지 않고 싶었으면 9, 없으면 없음 리턴
{
    $pdo = pdoSqlConnect();
    $query = "SELECT IFNULL(MAX(isHelpful), 4) as wasHelpful
              FROM   review_helpful
              WHERE  reviewID = ? AND userID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewID, $userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['wasHelpful']);
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