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
                     ROUND(avg(review.starRating), 1) as 'star',
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
              INNER JOIN review on review.restaurantID = restaurant.id
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
                                 WHEN 1 THEN (SELECT mi2.imageURL
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
             INNER JOIN menu m on m.menuID = mc.menuID
             INNER JOIN restaurant r on m.restaurantID = r.id
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
    $query = "";
    $st = $pdo->prepare($query);
    $st->execute([$restaurantID, $userID]);

    $st = null;
    $pdo = null;

}
