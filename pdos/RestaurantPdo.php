<?php

/* **************************     HeatherAPI      ************************* */
function getCategories()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT   id, title, imageURL
              FROM     category
              ORDER BY id;";

    $st = $pdo->prepare($query);
    $st->execute();
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
