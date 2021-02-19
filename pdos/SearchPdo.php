<?php

/* **************************     HeatherAPI      ************************* */
//No.15
function newFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문 많은 순, 신규매장(7일 이내 등록) category
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN user_address ua on a.addressID = ua.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on r.id = rc.restaurantID
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7
                                AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장 category
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN user_address ua on a.addressID = ua.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on r.id = rc.restaurantID
              WHERE     TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7
                        AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore":
            $query="SELECT     r.id as restaurantID, r.title as restaurantTitle,
                               IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                               IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                               IFNULL(reviewNumber, '없음') as howManyReviews,
                               CONCAT(ROUND((6371 * acos (
                                              cos ( radians( (SELECT    address.x
                                                              FROM      address
                                                              LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                              LEFT JOIN user on user.id = ua.userID
                                                              WHERE     user.id = ?) ) )
                                            * cos ( radians( a.x ) )
                                            * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                              FROM      address
                                                                              LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                              LEFT JOIN user on user.id = ua.userID
                                                                              WHERE     user.id = ?)) )
                                            + sin ( radians( (SELECT    address.x
                                                              FROM      address
                                                              LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                              LEFT JOIN user on user.id = ua.userID
                                                              WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                               )), 1), 'km') as distance,
                               IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                               CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                               EXISTS(SELECT * FROM coupon as c
                                      WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                               (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                               (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                               (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                    FROM      restaurant as r
                    LEFT JOIN address a on r.addressID = a.addressID
                    LEFT JOIN user_address ua on a.addressID = ua.addressID
                    LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                      FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                               FROM review WHERE restaurantID = review.restaurantID
                               GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                    LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                    LEFT JOIN restaurant_category rc on r.id = rc.restaurantID
                    WHERE     TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7
                              AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                              AND EXISTS(SELECT * FROM coupon as c
                                         WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                    GROUP BY  restaurantID
                    HAVING    distance <= 4.0
                    ORDER BY  avgStar DESC
                    LIMIT     0 , 20;";
            break;

        case "newest":
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN user_address ua on a.addressID = ua.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on r.id = rc.restaurantID
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                   LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7
                                AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function oneServingFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isOneServing = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.isOneServing = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isOneServing = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isOneServing = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function koreanFoodFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isKoreanFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.isKoreanFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isKoreanFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isKoreanFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function chickenFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isChicken = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.isChicken = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isChicken = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isChicken = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function flourFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isFlourBasedFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.isFlourBasedFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isFlourBasedFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isFlourBasedFood = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function porkFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isPorkCutlet = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isPorkCutlet = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  distance
                      LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isPorkCutlet = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isPorkCutlet = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function jokbalFilterSearch($userID, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.isJokbalOrBossam = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.isJokbalOrBossam = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.isJokbalOrBossam = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.isJokbalOrBossam = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



function filterSearch($userID, $category, $align, $isCheetah, $deliveryFee, $minimumOrder, $coupon)
{
    $pdo = pdoSqlConnect();
    $query = "";

    switch ($align) {
        case "mostOrdered": //주문많은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN `order` as o on o.restaurantID = r.id
                      WHERE     rc.? = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  COUNT(o.restaurantID) DESC
                      LIMIT     0 , 20;";
            break;

        case "nearest": //가까운 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                         IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                         IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                         IFNULL(reviewNumber, '없음') as howManyReviews,
                         CONCAT(ROUND((6371 * acos (
                                        cos ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) )
                                      * cos ( radians( a.x ) )
                                      * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                        FROM      address
                                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                        LEFT JOIN user on user.id = ua.userID
                                                                        WHERE     user.id = ?)) )
                                      + sin ( radians( (SELECT    address.x
                                                        FROM      address
                                                        LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                        LEFT JOIN user on user.id = ua.userID
                                                        WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                         )), 1), 'km') as distance,
                         IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                         CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                         EXISTS(SELECT * FROM coupon as c
                                WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                         (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
              FROM      restaurant as r
              LEFT JOIN address a on r.addressID = a.addressID
              LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                         FROM review WHERE restaurantID = review.restaurantID
                         GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
              LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
              LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
              WHERE     rc.? = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                        AND EXISTS(SELECT * FROM coupon as c
                                   WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
              GROUP BY  restaurantID
              HAVING    distance <= 4.0
              ORDER BY  distance
              LIMIT     0 , 20;";
            break;

        case "highScore": //별점 높은 순, 신규매장이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      WHERE     rc.? = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  avgStar DESC
                      LIMIT     0 , 20;";
            break;

        case "newest": //신규매장순(매장 등록순) 정렬, 신규매장(7일 이내 등록된 매장)이 아닌 카테고리
            $query = "SELECT     r.id as restaurantID, r.title as restaurantTitle,
                                 IF(TIMESTAMPDIFF(DAY, DATE_FORMAT(r.createdAt, '%Y-%m-%d'), NOW()) < 7, 1, 0) as isNew,
                                 IF(r.isCheetah = 1, '치타배달', 'X') as isCheetah, IFNULL(star, 0) as avgStar,
                                 IFNULL(reviewNumber, '없음') as howManyReviews,
                                 CONCAT(ROUND((6371 * acos (
                                                cos ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) )
                                              * cos ( radians( a.x ) )
                                              * cos ( radians( a.y ) - radians((SELECT    address.y
                                                                                FROM      address
                                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                                LEFT JOIN user on user.id = ua.userID
                                                                                WHERE     user.id = ?)) )
                                              + sin ( radians( (SELECT    address.x
                                                                FROM      address
                                                                LEFT JOIN user_address ua on address.addressID = ua.addressID
                                                                LEFT JOIN user on user.id = ua.userID
                                                                WHERE     user.id = ?) ) ) * sin ( radians( a.x ) )
                                 )), 1), 'km') as distance,
                                 IF(r.deliveryFee = 0, '무료배달', CONCAT('배달비', FORMAT(r.deliveryFee, 0), '원')) as deliveryFee,
                                 CONCAT(r.deliveryTime, '-', (r.deliveryTime)+10, '분') as deliveryTime,
                                 EXISTS(SELECT * FROM coupon as c
                                        WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) as doesCouponExist,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 1 AND restaurantID = r.id) as imageURL1,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 2 AND restaurantID = r.id) as imageURL2,
                                 (SELECT imageURL FROM restaurant_image WHERE imageOrder = 3 AND restaurantID = r.id) as imageURL3
                      FROM      restaurant as r
                      LEFT JOIN address a on r.addressID = a.addressID
                      LEFT JOIN (SELECT review.restaurantID as restaurantID, IFNULL(ROUND(avg(starRating), 1), 0) as star,
                                        FORMAT(COUNT(review.restaurantID), 0) as reviewNumber
                                 FROM review WHERE restaurantID = review.restaurantID
                                 GROUP BY review.restaurantID) as lj3 on r.id = lj3.restaurantID
                      LEFT JOIN restaurant_image as ri on ri.restaurantID = r.id
                      LEFT JOIN restaurant_category rc on rc.restaurantID = r.id
                      LEFT JOIN (SELECT restaurantID, TIMESTAMPDIFF(MINUTE, NOW(), coupon.endAt) as newDay FROM coupon
                                 LEFT JOIN restaurant on coupon.restaurantID = restaurant.id) as c2 on c2.restaurantID = r.id
                      WHERE     rc.? = 1 AND isCheetah = ? AND deliveryFee <= ? AND minimumOrder >= ?
                                AND EXISTS(SELECT * FROM coupon as c
                                           WHERE r.id = c.restaurantID AND TIMESTAMPDIFF(MINUTE, NOW(), c.endAt) > 1) = ?
                      GROUP BY  restaurantID
                      HAVING    distance <= 4.0
                      ORDER BY  newDay DESC
                      LIMIT     0 , 20;";
            break;
    }

    $st = $pdo->prepare($query);
    $st->execute([$userID, $userID, $userID, $category, $isCheetah, $deliveryFee, $minimumOrder, $coupon]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}