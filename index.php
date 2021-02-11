<?php

require './pdos/DatabasePdo.php';
require './pdos/UsersPdo.php';
require './pdos/JWTPdo.php';
require './pdos/RestaurantPdo.php';
require './pdos/AddressPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;
use \Firebase\JWT\JWT;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'UTF-8');

//에러출력하게 하는 코드
error_reporting(E_ALL); 
ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* Controller 는 UsersController, RestaurantController 등 나눠서 진행할게요! */
    
    /* ******************   Users   ****************** */

    $r->addRoute('GET', '/', ['UsersController', 'index']);
    $r->addRoute('POST', '/users', ['UsersController', 'createUser']);
    $r->addRoute('GET', '/users/{userID}', ['UsersController', 'getUserDetail']);
    $r->addRoute('POST', '/users/email', ['UsersController', 'checkEmail']);
    $r->addRoute('POST', '/users/phone', ['UsersController', 'checkPhoneNumber']);
    $r->addRoute('POST', '/users/auth', ['UsersController', 'userAuth']);
    $r->addRoute('POST', '/users/auth/code', ['UsersController', 'verifyCode']);
    $r->addRoute('POST', '/users/login', ['UsersController', 'userLogin']);

    /* ******************   Restaurant   ****************** */

    $r->addRoute('GET', '/categories', ['RestaurantController', 'categories']); //검색화면 카테고리 조회
    $r->addRoute('GET', '/filters', ['RestaurantController', 'filters']); //필터 항목 조회

    /* ******************   Address   ****************** */
    $r->addRoute('POST', '/address', ['AddressController', 'addUserAddress']);

    /* ******************   JWT   ****************** */
    
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'UsersController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UsersController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            case 'RestaurantController':
                $handler = $routeInfo[1][1]; 
                $vars = $routeInfo[2];
                require './controllers/RestaurantController.php';
                break;
            case 'AddressController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AddressController.php';
                break;
            /*case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}