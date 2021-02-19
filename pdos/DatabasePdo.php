<?php

//Soi 테스트용 DB
// function pdoSqlConnect()
// {
//    try {
//        $DB_HOST = "13.124.163.139";
//        $DB_NAME = "coupang_eatsdb";
//        $DB_USER = "rp2remote";
//        $DB_PW = "1234#";
//        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
//        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//        return $pdo;
//    } catch (\Exception $e) {
//        echo $e->getMessage();
//    }
// }

//Heather 테스트용 DB
// function pdoSqlConnect()
// {
//     try {
//         $DB_HOST = "heatherdb.c7br4zg8tlch.us-east-2.rds.amazonaws.com";
//         $DB_NAME = "CoupangEats";
//         $DB_USER = "heatheradmin";
//         $DB_PW = "heather-server";
//         $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
//         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         return $pdo;
//     } catch (\Exception $e) {
//         echo $e->getMessage();
//     }
// }

//RDS 개발용 DB
 function pdoSqlConnect()
 {
     try {
         $DB_HOST = "coupang-eatsdb.cr4fbdipsnjz.ap-northeast-2.rds.amazonaws.com";
         $DB_NAME = "coupangeatsdb";
         $DB_USER = "soi";
         $DB_PW = "i4abella3514";
         $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         return $pdo;
     } catch (\Exception $e) {
         echo $e->getMessage();
     }
 }