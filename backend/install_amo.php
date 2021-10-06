<?php
define('DELETE_URL_PATH', 'amo/telegrem_widget');

include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/constants.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/errorlog.php';
setErrorLog($_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH);

include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/webclient.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/amo.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/auth.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/action.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/account.php';
include $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/amoCrm/actions/fields.php';

function setLicense(string $username)
{
    if (LIC_ENABLED) {
        $lic = new \lic\AppLicense(new \dataAccess\license\LicenseDA(), LIC_TRIAL_PERIOD_DAYS);
        try {
            $lic->openTrial($username);
        } catch (\lic\LicenseExistedException $ex) {

        }
    }
}

function checkAuthFile(\amoCrm\actions\auth\AmoCrmAuth $amoAuth)
{
    if ($amoAuth->checkAuthFile()) {
        http_response_code(403);
        exit();
    }
}

function postAction(bool $isMultiAcc)
{
    if (!IS_AUTH_DISABLE || in_array(htmlspecialchars($_POST['domain']), IS_AUTH_DISABLE_EXCLUDE)) {
        $amoAuth = new \amoCrm\actions\auth\AmoCrmAuth(
            new \amoCrm\AmoCrmWebClient(),
            $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . "/users/"
        );

        $authData = \amoCrm\actions\auth\OAuth2Data::createAuthData(
            htmlspecialchars($_POST['domain'])
            ,htmlspecialchars($_POST['integrationId'])
            ,htmlspecialchars($_POST['authCode'])
            ,htmlspecialchars($_POST['secKey'])
        );
        $amoAuth->init($authData);
        $this->afterAuthInit($amoAuth, $isMultiAcc);
    }
}

function getAction(bool $isMultiAcc)
{
    if(isset($_GET['from_widget']) && $_GET['from_widget'] == 1 && (($_GET['client_id'] ?? null) === INTEGRATION_ID) && !empty($_GET['referer']) && !empty($_GET['code'])) {
        $domain = explode('.', htmlspecialchars($_GET['referer']))[0];

        if (!IS_AUTH_DISABLE || in_array($domain, IS_AUTH_DISABLE_EXCLUDE)) {
            $amoAuth = new \amoCrm\actions\auth\AmoCrmAuth(
                new \amoCrm\AmoCrmWebClient(),
                $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . "/users/",
                $isMultiAcc ? "${domain}.dat" : AUTH_FILE_NAME
            );

            $authData = \amoCrm\actions\auth\OAuth2Data::createAuthData(
                $domain
                ,INTEGRATION_ID
                ,htmlspecialchars($_GET['code'])
                ,SECRET_KEY
            );
            $amoAuth->init($authData);
            afterAuthInit($amoAuth, $isMultiAcc);
            return true;
        }
    }
    return false;
}

function afterAuthInit(\amoCrm\actions\auth\AmoCrmAuth $auth, bool $isMultiAcc)
{
    $domain = $auth->getDomain();
    $accountInfo = (new amoCrm\actions\account\AccountActions($auth, new \amoCrm\AmoCrmWebClient()))->getObjects();
    if ($isMultiAcc) {
        rename(__DIR__ . "/users/${domain}.dat", $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . "/users/${domain}_${accountInfo['id']}.dat");
    }

    try {
        createCustomFields($auth);
    } catch (\Exception $ex) {
        if($ex->getMessage() !== 'Error Code = 400') {
            throw $ex;
        }
    }

    if(LIC_ENABLED) {
        setLicense($domain);
    }
}

function createCustomFields(\amoCrm\actions\auth\AmoCrmAuth $auth, array $fields = APP_CUSTOM_FIELDS)
{
    if (sizeof($fields) > 0) {
        $fieldsActions = new \amoCrm\actions\fields\FieldsActions($auth, new \amoCrm\AmoCrmWebClient());
        foreach ($fields as $type => $values) {
            $fieldsForCreate = [];
            foreach ($values as $field) {
                $newField = new \amoCrm\actions\fields\NewField();
                $newField->name = $field['name'];
                $newField->code = $field['code'];
                $newField->is_api_only = $field['isApiOnly'];
                $newField->type = $field['type'];
                $fieldsForCreate[] = $newField;
            }
            $fieldsActions->create([$type], $fieldsForCreate);
        }
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (getAction(IS_MULTI_ACC)) {
            header("HTTP/1.1 200 OK");
            exit('OK');
        }
        break;
    case 'POST':
        postAction(IS_MULTI_ACC);
        header("HTTP/1.1 200 OK");
        exit('OK');
}

require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/templates/amo/install_amo_view.php';
