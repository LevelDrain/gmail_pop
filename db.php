<?php
function db_connect()
{
    $db_host = 'ホスト名';
    $db_name = 'DB名';
    $db_user = 'ユーザー名';
    $db_pass = 'パスワード';

    try {
        $db = new PDO(
            'mysql:host=' . $db_host .
            ';dbname=' . $db_name .
            ';charset=utf8',
            $db_user,
            $db_pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $db;
    } catch (PDOException $e) {
        exit('DB接続失敗' . $e->getMessage());
    }
}