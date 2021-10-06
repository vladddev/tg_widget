<?php
namespace dataAccess;

function saveNewChat(string $amoDomainName, string $chatId, string $chatName, string $messengerName)
{
    if(!getChats($amoDomainName, $messengerName, $chatId)[0] ?? null) {
        $dbConn = getDbConnect();

        $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
        $chatName =  mysqli_real_escape_string($dbConn, $chatName);
        $chatId = mysqli_real_escape_string($dbConn, $chatId);
        $messengerName = mysqli_real_escape_string($dbConn, $messengerName);
        executeQuery("insert into chats values ('${amoDomainName}', '${chatId}', '${chatName}', '${messengerName}')", $dbConn);
    }
}

function getChats(string $amoDomainName, string $messengerName, string $chatId = null)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    $messengerName = mysqli_real_escape_string($dbConn, $messengerName);
    $chatId = $chatId ? mysqli_real_escape_string($dbConn, $chatId) : null;
    $dbQuery = "
        select * from chats 
        where amo_domain = '${amoDomainName}' and messenger = '${messengerName}'"
        . ($chatId ? " and chat_id = ${chatId}" : '');

    return executeQuery($dbQuery, $dbConn) ?? null;
}

function deleteChats(string $amoDomainName, string $messengerName)
{
    $dbConn = getDbConnect();
    $amoDomainName = mysqli_real_escape_string($dbConn, $amoDomainName);
    $messengerName = mysqli_real_escape_string($dbConn, $messengerName);
    $dbQuery = "delete from chats where amo_domain = '${amoDomainName}' and messenger = '${messengerName}'";

    executeQuery($dbQuery, $dbConn);
}
