<?php
namespace controllers;
use amoCrm\actions\auth\AmoCrmAuth;
use lic\License;

require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/dataAccess/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/dataAccess/appData.php';
require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/dataAccess/telegramData.php';

require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/messengers/messenger.php';
require $_SERVER['DOCUMENT_ROOT'] . '/' . DELETE_URL_PATH . '/include/messengers/telegram/telegram_mess.php';

abstract class Controller
{
    protected $lic;

    public function __construct($lic)
    {
        $this->lic=$lic;
    }

    public final function execute($params)
    {
        if (!$this->getIsCheckAppKey() || ($this->getIsCheckAppKey() && (htmlspecialchars($_GET['key']) ?? '') === APP_KEY)) {
            $this -> processBefore($params);

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this -> processGet($params);
                    break;
                case 'POST':
                    $this -> processPost($params);
                    break;
                case 'DELETE':
                    $this -> processDelete($params);
            }
        } else {
            http_response_code(403);
        }
    }

    protected abstract function processGet($params);
    protected abstract function processBefore($params);
    protected abstract function processPost($params);
    protected abstract function processDelete($params);
    protected function getIsCheckAppKey(): bool
    {
        return IS_CHECK_APP_KEY;
    }

    protected function getIsCheckCORS(): bool
    {
        return true;
    }
}

abstract class AppController extends Controller
{
    private $authDir;

    protected $auth;
    protected $allowLic = LIC_ENABLED;

    public function __construct(string $authDir, $lic)
    {
        parent::__construct($lic);
        $this->authDir = $authDir;
    }

    final protected function processBefore($params)
    {
        if(isset($_SERVER['HTTP_ORIGIN'])) {
            if (IS_DEBUG || (!IS_DEBUG && $this->checkHttpOrigin()))
            {
                header("Access-Control-Allow-Origin:${_SERVER['HTTP_ORIGIN']}");
            } else {
                if (!IS_DEBUG) {
                    exit();
                }
            }
        }

        if (!$this->getIsAuthDisable() || (IS_MULTI_ACC ? in_array(htmlspecialchars($params[0]), IS_AUTH_DISABLE_EXCLUDE) : true)) {
            $this->setAuth($params);
        }

        if(LIC_ENABLED) {
            $this->checkLic();
        }
    }

    protected function getIsAuthDisable(): bool
    {
        return IS_AUTH_DISABLE;
    }

    private function checkHttpOrigin(): bool
    {
        return !empty($_SERVER['HTTP_ORIGIN']) && mb_ereg_match(HTTP_ORIGIN_TEMPLATE, $_SERVER['HTTP_ORIGIN']) !== false;
    }

    protected function setAuth(array $params, $fileName = null)
    {
        $amoAuth = new AmoCrmAuth(
            new \amoCrm\AmoCrmWebClient()
            ,$this->authDir
            ,$fileName ? $fileName : (IS_MULTI_ACC ? (htmlspecialchars($params[0]) . '_' . htmlspecialchars($params[1]) . '.dat') : AUTH_FILE_NAME));
        if (!$amoAuth->checkAuthFile()) {
            http_response_code(403);
            exit();
        }
        $amoAuth->init();

        $this->auth = $amoAuth;
    }

    private function checkLic()
    {
        if ($this->allowLic && !$this->lic->check($this->auth->getDomain(), $this->auth)) {
            http_response_code(403);
            exit();
        }
    }
}
