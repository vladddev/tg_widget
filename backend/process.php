<?php
define('DELETE_URL_PATH', 'amo/telegrem_widget');

include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/constants.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/errorlog.php';
setErrorLog($_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH);

include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/routes.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/webclient.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/amo.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/auth.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/action.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/controllers/controller.php';

$currentUrl = str_replace((DELETE_URL_PATH . '/'), '', $_SERVER['REQUEST_URI']);

$params = getParamsArray($currentUrl);
$params['body'] = file_get_contents('php://input');

$route = remoteLastSplash(parse_url($currentUrl , PHP_URL_PATH));

$controllerData = array_filter(
    getRouteMapping(),
    function ($value) use ($route) {
        static $flag = false;
        if (!$flag && preg_match($value['match'], $route) > 0) {
            $flag = true;
            return true;
        }
        return false;
    }
);

if (empty($controllerData)) {
    file_put_contents('deny.log', $route);
    http_response_code(403);
} else {
    $controllerData = array_shift($controllerData);
    $controllerFullFilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . $controllerData['path'];
    require $controllerFullFilePath;

    $controllerClass = '\controllers' . $controllerData['name'];
    if(LIC_ENABLED) {
        $lic = new \lic\AppLicense(new \dataAccess\license\LicenseDA(), LIC_TRIAL_PERIOD_DAYS);
    }

    $controller = new $controllerClass(__DIR__ . "/users/", $lic ?? null);
    $controller->execute($params);
}
