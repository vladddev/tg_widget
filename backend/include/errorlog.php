<?php

function setErrorLog(string $rootDir)
{
    ini_set('date.timezone', 'Europe/Moscow');
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 'on');
    ini_set('error_log', $rootDir . '/' . ERROR_FILENAME);
}

