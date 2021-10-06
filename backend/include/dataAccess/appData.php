<?php
namespace dataAccess;

function getTelegramChatId(string $amoDomainName)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    return executeQuery("select telegramm_chat_id as ChatId from amo_telegram where amo_domain='${amoDomainName}'", $dbConn)[0]['ChatId'] ?? null;
}

function saveTelegramChatId(string $amoDomainName, string $telegramChatId)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    $telegramChatId = mysqli_real_escape_string($dbConn, $telegramChatId);

    if (getTelegramChatId($amoDomainName)) {
        executeQuery("UPDATE amo_telegram SET telegramm_chat_id ='${telegramChatId}' WHERE amo_domain='${amoDomainName}'", $dbConn);
    } else {
        executeQuery("INSERT INTO amo_telegram (amo_domain, telegramm_chat_id) VALUES ('${amoDomainName}', '${telegramChatId}')", $dbConn);
    }
}

function getDataInfo(string $amoDomainName)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    return executeQuery("select * from amo_info where domain='${amoDomainName}'", $dbConn)[0] ?? null;
}

function saveAmoDataInfo(string $amoDomainName, array $info)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    $amoName = mysqli_real_escape_string($dbConn, $info['name']);
    $usersCount = $info['usersCount'];
    $licFrom = $info['licFrom'] ?? 'NULL';
    $licTo = $info['licTo'] ?? 'NULL';
    $tariff = mysqli_real_escape_string($dbConn, $info['tariff']);
    $timeZone = mysqli_real_escape_string($dbConn, $info['timeZone']);
    $version = $info['version'];
    $managerName = mysqli_real_escape_string($dbConn, $info['managerName']);
    $managerPhone = mysqli_real_escape_string($dbConn, $info['managerPhone']);

    if (getDataInfo($amoDomainName)) {
        executeQuery("
            UPDATE amo_info 
            SET 
                domain = '${amoDomainName}'
                , name = '${amoName}'
                , usersCount = ${usersCount}
                , licFrom = ${licFrom}
                , licTo = ${licTo}
                , tariff = '${tariff}'
                , timeZone = '${timeZone}'
                , version = ${version}
                , managerName = '${managerName}'
                , managerPhone = '${managerPhone}'
                , created = current_timestamp()
            WHERE domain='${amoDomainName}'", $dbConn);
    } else {
        executeQuery("
            INSERT INTO amo_info 
            VALUES 
                   (
                    '${amoDomainName}'
                    ,'${amoName}'
                    ,${usersCount}
                    ,${licFrom}
                    ,${licTo}
                    ,'${tariff}'
                    ,'${timeZone}'
                    ,${version}
                    ,'${managerName}'
                    ,'${managerPhone}'
                    ,current_timestamp()
                    )
            ", $dbConn);
    }
}