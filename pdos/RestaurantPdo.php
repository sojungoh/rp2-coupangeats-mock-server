<?php

/* **************************     HeatherAPI      ************************* */
function getCategories()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT   id, title, imageURL
              FROM     category
              ORDER BY id;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getFilters()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT   id as 'order', filterTitle,
                       GROUP_CONCAT(subFilterTitle ORDER BY filter.subFilterID SEPARATOR ', ') as filters
              FROM     filter
              GROUP BY id;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//No.3
function getFilterSearch($category, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupone): array
{
    $pdo = pdoSqlConnect();
    $query = "";

    $st = $pdo->prepare($query);
    $st->execute([$category, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupone]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//No.4
function basicInfo($restaurantID): array
{
    $pdo = pdoSqlConnect();
    $query = "SELECT restaurant.id as id, restaurant.title, FORMAT(count(review.restaurantID), 0) as 'reviews',
                     IFNULL(ROUND(avg(review.starRating), 1), 0) as 'star',
                     IF(FORMAT(restaurant.deliveryFee, 0) = 0, '무료배달', FORMAT(restaurant.deliveryFee, 0))as deliveryFee,
                     FORMAT(restaurant.minimumOrder, 0) as minimumOrder, restaurant.isCheetah,
                     (SELECT imageURL
                      FROM restaurant_image
                      WHERE imageOrder = 1 AND restaurantID = restaurant.id) imageURL1,
                     (SELECT imageURL
                      FROM restaurant_image
                      WHERE imageOrder = 2 AND restaurantID = restaurant.id) imageURL2,
                     (SELECT imageURL
                      FROM restaurant_image
                      WHERE imageOrder = 3 AND restaurantID = restaurant.id) imageURL3
              FROM   restaurant
              LEFT JOIN review on review.restaurantID = restaurant.id
              WHERE restaurant.id =?
              GROUP BY review.restaurantID;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidRestaurantID($restaurantID): int
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT restaurant.id FROM restaurant WHERE id =? ) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

//No.5
function getMenu($restaurantID): array
{
    $pdo = pdoSqlConnect();
    $query = "SELECT     r.id as restaurantID, r.title as restaurant,
                         mc.category as menuCategory,
                         m.menuID as menuID, m.menuName as name,
                         CONCAT(FORMAT(menuPrice, 0), '원') as price,
                         IFNULL(m.menuIntroduction, '메뉴소개 없음') as menuIntro,
                         CASE (SELECT EXISTS(SELECT COUNT(imageURL)
                                             FROM   menu_image mi
                                             WHERE  mi.menuID = m.menuID
                                             GROUP BY mi.menuID
                                             LIMIT 1))
                         WHEN 0 THEN '이미지 없음'
                         ELSE
                                 (CASE (SELECT COUNT(imageURL)
                                        FROM   menu_image mi
                                        WHERE  mi.menuID = m.menuID
                                        GROUP BY mi.menuID
                                        LIMIT 1)
                                 WHEN 2 THEN (SELECT mi2.imageURL
                                              FROM   menu_image mi2
                                              WHERE  mi2.imageOrder = 1
                                              AND    mi2.menuID = m.menuID
                                              LIMIT 1)
                                 ELSE (SELECT mi3.imageURL
                                       FROM menu_image mi3
                                       WHERE mi3.menuID = m.menuID
                                       LIMIT 1)
                                 END)
                         END as imageURL
             FROM       menu_category mc
             LEFT JOIN menu m on m.menuID = mc.menuID
             LEFT JOIN restaurant r on m.restaurantID = r.id
             WHERE      r.id =?
             ORDER BY   mc.categoryOrder, m.menuID;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isMenuRegistered($restaurantID): int
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT     restaurantID 
                            FROM       menu 
                            INNER JOIN restaurant r on menu.restaurantID = r.id 
                            WHERE r.id =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

//No.6
function favorite($restaurantID, $userID)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO favorite(restaurantID, userID) VALUES(?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);

    $st = null;
    $pdo = null;
}

function deleteFavorite($restaurantID, $userID)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE favorite SET isFavorite = 0 WHERE restaurantID = ? AND userID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);

    $st = null;
    $pdo = null;
}

function reFavorite($restaurantID, $userID)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE favorite SET isFavorite = 1 WHERE restaurantID = ? AND userID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);

    $st = null;
    $pdo = null;
}

function isRegisteredFavorite($restaurantID, $userID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT *
                            FROM favorite
                            WHERE restaurantID = ?
                            AND userID = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isAlreadyFavorite($restaurantID, $userID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT *
                             FROM favorite
                             WHERE restaurantID = ?
                             AND userID = ?
                             AND isFavorite = 1) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

//No.7
function menuDetail($menuID): array
{
    $pdo = pdoSqlConnect();
    $query = "SELECT m.restaurantID, r.title, m.menuID, menuName,
                     IFNULL(menuIntroduction, '메뉴소개 없음') AS menuIntro,
                     CONCAT(FORMAT(menuPrice, 0), '원') AS menuPrice,
                     CASE (SELECT EXISTS(SELECT COUNT(mi2.imageURL)
                                         FROM menu_image mi2
                                         WHERE mi2.menuID = m.menuID
                                         GROUP BY mi2.menuID
                                         LIMIT 1))
                     WHEN 0 THEN '1번 이미지 없음'
                     ELSE
                          (CASE (SELECT COUNT(imageURL)
                                 FROM   menu_image mi
                                 WHERE  mi.menuID = m.menuID
                                 GROUP BY mi.menuID
                                 LIMIT 1)
                          WHEN 1 THEN (SELECT imageURL
                                       FROM menu_image
                                       WHERE m.menuID = menu_image.menuID)
                          WHEN 2 THEN (SELECT mi2.imageURL
                                       FROM   menu_image mi2
                                       WHERE  mi2.imageOrder = 1
                                       AND    mi2.menuID = m.menuID
                                       LIMIT 1)
                          END)
                     END AS imageURL1,
                     CASE (SELECT EXISTS(SELECT COUNT(mi2.imageURL)
                                         FROM menu_image mi2
                                         WHERE mi2.menuID = m.menuID
                                         GROUP BY mi2.menuID
                                         LIMIT 1))
                     WHEN 0 THEN '2번 이미지 없음'
                     ELSE
                         (CASE (SELECT COUNT(imageURL)
                                FROM   menu_image mi
                                WHERE  mi.menuID = m.menuID
                                GROUP BY mi.menuID
                                LIMIT 1)
                         WHEN 1 THEN '2번 이미지 없음'
                         WHEN 2 THEN (SELECT mi2.imageURL
                                      FROM   menu_image mi2
                                      WHERE  mi2.imageOrder = 2
                                      AND    mi2.menuID = m.menuID
                                      LIMIT 1)
                         END)
                     END AS imageURL2,
                     CASE (SELECT ISNULL(m.menuOptionId))
                     WHEN 1 THEN '옵션 없음'
                     ELSE '옵션 있음'
                     END as menuOption
             FROM      menu m
             LEFT JOIN menu_image mi on m.menuID = mi.menuID
             INNER JOIN restaurant r on m.restaurantID = r.id
             WHERE     m.menuID = ?
             GROUP BY  m.menuID;";

    $st = $pdo->prepare($query);
    $st->execute([$menuID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidMenuID($menuID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT *
                             FROM menu
                             WHERE menuID = ?
                             AND menuStatus = 1) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$menuID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

//No.8
function menuOptions($menuID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT     r.id as restaurantID, r.title as restaurant,
                         menuID, menuName, optionOrder, optionTitle, isEssential, multipleChoice, subOrder, subName,
                         CONCAT(FORMAT(subPrice, 0), '원') as subPrice
              FROM       menu as m
              LEFT JOIN  option_order oo on m.menuOptionID = oo.optionID
              INNER JOIN option_title ot on oo.optionTitleID = ot.optionTitleID
              INNER JOIN sub_option so on ot.optionTitleID = so.optionTitleID
              INNER JOIN restaurant r on r.id = m.restaurantID  
              WHERE      m.menuID = ?
              ORDER BY   optionOrder, subOrder;";

    $st = $pdo->prepare($query);
    $st->execute([$menuID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isNotExistOptions($menuID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT ISNULL(menu.menuOptionID) as isNotExistOptions FROM menu WHERE menuID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['isNotExistOptions']);
}

//No.9
function restaurantDetail($restaurantID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT r.id as restaurantID, r.title AS restaurant, r.phoneNumber,
                     CONCAT_WS(' ', a.address, (CASE ISNULL(a.buildingName)
                                                WHEN 1 THEN ''
                                                ELSE a.buildingName
                                                END),
                                    r.addressDetail) AS address,
                     CONCAT_WS(',', CONCAT('(', a.x), CONCAT(a.y, ')')) AS point,
                                    r.ownerName, r.ownerNumber, r.name, r.introduction,
                     CASE ISNULL(oh.breakAt)
                     WHEN 1 THEN CONCAT_WS(': ', CONCAT_WS(' ~ ', oh.day, oh.until),
                                 CONCAT_WS(' ~ ', DATE_FORMAT(oh.openAt, '%H:%i'), DATE_FORMAT(oh.closeAt, '%H:%i')))
                     ELSE CONCAT_WS(', ', (CONCAT_WS(': ', CONCAT_WS(' ~ ', oh.day, oh.until),
                          CONCAT_WS(' ~ ', DATE_FORMAT(oh.openAt, '%H:%i'), DATE_FORMAT(oh.breakAt, '%H:%i')))),
                          CONCAT_WS(' ~ ', DATE_FORMAT(oh.breakEndedAt, '%H:%i'), DATE_FORMAT(oh.closeAt, '%H:%i')))
                     END AS openingHour,
                     CASE (ISNULL(r.notice))
                     WHEN 1 THEN '공지사항 없음'
                     ELSE r.notice
                     END AS notice,
                     CASE (ISNULL(r.originInfo))
                     WHEN 1 THEN '원산지정보 없음'
                     ELSE r.originInfo
                     END AS originInfo,
                     CASE (ISNULL(r.allergenInfo))
                     WHEN 1 THEN '알레르기정보 없음'
                     ELSE r.allergenInfo
                     END AS allergenInfo
             FROM restaurant AS r
             INNER JOIN address a on r.addressID = a.addressID
             LEFT JOIN opening_hour oh ON r.id = oh.restaurantID
             WHERE r.id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//No.10
function reviewInfo($restaurantID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT    r2.id AS restaurantID, r2.title, avgReview.avgstar, avgReview.reviewNumber, r.id AS reviewID, r.userID,
                        CONCAT(LEFT(user.name, 1), '**') AS reviewer, r.contents, r.starRating AS star
              FROM      restaurant AS r2
              LEFT JOIN review AS r ON r2.id = r.restaurantID
              LEFT JOIN (SELECT restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) AS avgstar,
                                CONCAT(FORMAT(COUNT(id), 0),'개') AS reviewNumber
                         FROM   review
                         WHERE restaurantID = ?
                         GROUP BY restaurantID) avgReview ON r2.id = avgReview.restaurantID
              LEFT JOIN user ON user.id = r.userID
              WHERE     r.restaurantID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isExistReview($restaurantID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM review WHERE review.restaurantID = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$restaurantID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
