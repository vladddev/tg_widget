<?php
namespace dataAccess;

function getDbConnect()
{
    $dbConnect = mysqli_connect(DB_HOST,DB_LOGIN,DB_PASSWORD,DB_NAME );
    $dbConnect->set_charset('utf8');
    return $dbConnect;
}

function executeQuery($dbQuery, $dbConnect, $autoClose = true): array
{
    $dbQueryResult = mysqli_query($dbConnect, $dbQuery);
    if ($dbQueryResult === false) {
        throw new \Exception("Error on DbQuery ${dbQuery}");
    }
    if ($autoClose) {
        mysqli_close($dbConnect);
    } else {
        return [];
    }
    return gettype($dbQueryResult) !== 'boolean' && $dbQueryResult ? mysqli_fetch_all($dbQueryResult, MYSQLI_ASSOC) : [];
}
