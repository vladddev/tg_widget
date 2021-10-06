<?php
//Замените значения констант в соответствии с Вашей интеграцией
define('IS_DEBUG', false);
define('IS_AUTH_DISABLE', false);
define('IS_AUTH_DISABLE_EXCLUDE', []);
define('IS_MULTI_ACC', true);

define('APP_KEY', '1234567890987654321');
define('IS_CHECK_APP_KEY', true);

define('MAX_ONE_QUERY_LIMIT', 250);

define('AUTH_FILE_NAME', 'auth.dat'); //Заменить имя файла на любое другое. В нем будет храниться ключ подключения к amoCrm
define('AUTH_IV', '1234567890987654'); // Заменить на любую последоватьльность из 16 цифр !!!!

define('ROOT_URL', 'https://hooks.tgwidget.ru');
define('INTEGRATION_REDIRECT_LINK', ROOT_URL . '/amo/telegrem_widget/install_amo.php'); // Заменить адрес Вашего сервера
define('INTEGRATION_ID', 'XXXX-XX-XXXX-XXXX-XXX-XX-XXXX');
define('SECRET_KEY', '1234567890987654321');

define('INTEGRATION_NAME', 'SendTHandlers');
define('ERROR_FILENAME', 'errors.err');

define('LIC_ENABLED', false);
define('HTTP_ORIGIN_TEMPLATE', '^https:\/\/[a-zA-Z0-9-]+.amocrm.(ru|com)[\/]*$');

define('APP_CUSTOM_FIELDS', []);

define('DB_HOST', 'localhost');
define('DB_NAME', 'name');
define('DB_LOGIN', 'user');
define('DB_PASSWORD', 'password');


define('ROOT_DOMAIN', 'devtgwidget');
define('ROOT_ACCOUNT_ID', 28889386);

define('LICENSE_PIPELINE_ID', 3837646);
define('LICENSE_BASE_STAGE_ID', 36921079);
define('TARIFF_NAME_FIELD_ID', 508357);
define('PROGRAM_FIELD_ID', 508359);
define('PROGRAM_AMO_FIELD_CODE', 'AMO');
define('LIC_END_FIELD_ID', 508363);
define('USER_COUNT_FIELD_ID', 508365);
define('MONTH_COUNT_FIELD_ID', 508367);
define('CONTACT_AMO_DOMAIN_FIELD_ID', 508431);
define('NEW_LEAD_TAG_NAME', 'виджет телеграм');

define('PRICE_CALCULATE_MIN_MONTH_COUNT', 6);
define('TARIFF_LIST', [
    'расширенный' => ['calculateBlock' => true, 'userCountBlock' => 1, 'monthCountBlock' => 1, 'price' => 999, 'discount' => [['blockCount' => 9, 'monthCountDiscount' => 1], ['blockCount' => 12, 'monthCountDiscount' => 2], ['blockCount' => 24, 'monthCountDiscount' => 6]]]
    ,'базовый' => ['calculateBlock' => true, 'userCountBlock' => 1, 'monthCountBlock' => 1, 'price' => 499, 'discount' => [['blockCount' => 9, 'monthCountDiscount' => 1], ['blockCount' => 12, 'monthCountDiscount' => 2], ['blockCount' => 24, 'monthCountDiscount' => 6]]]
    ,'расширенный (архивный)' => ['calculateBlock' => true, 'userCountBlock' => 1, 'monthCountBlock' => 1, 'price' => 799, 'discount' => [['blockCount' => 9, 'monthCountDiscount' => 1], ['monthCount' => 12, 'monthCountDiscount' => 2], ['monthCount' => 24, 'monthCountDiscount' => 6]]]
    ,'профессиональный' => ['calculateBlock' => true, 'userCountBlock' => 1, 'monthCountBlock' => 1, 'price' => 1499, 'discount' => [['blockCount' => 9, 'monthCountDiscount' => 1], ['monthCount' => 12, 'monthCountDiscount' => 2], ['monthCount' => 24, 'monthCountDiscount' => 6]]]
    ,'микро-бизнес' => ['calculateBlock' => false, 'userCountBlock' => 2, 'monthCountBlock' => 12, 'price' => 4999, 'discount' => [['blockCount' => 2, 'monthCountDiscount' => 2]]]
    ,'старт-ап' => ['calculateBlock' => false, 'userCountBlock' => 5, 'monthCountBlock' => 12, 'price' => 14999, 'discount' => [['blockCount' => 2, 'monthCountDiscount' => 2]]]
]);