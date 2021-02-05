<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "13.124.163.139";
        $DB_NAME = "coupang_eatsdb";
        $DB_USER = "rp2remote";
        $DB_PW = "1234#";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}