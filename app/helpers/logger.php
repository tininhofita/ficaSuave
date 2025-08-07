<?php

function logEvento($mensagem, $tipo = 'INFO')
{
    $logDir = BASE_PATH . '/app/helpers/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $arquivo = $logDir . 'inicio.log';
    $data = date('Y-m-d H:i:s');
    $msgFinal = "[{$data}] [{$tipo}] {$mensagem}\n";

    file_put_contents($arquivo, $msgFinal, FILE_APPEND);
}

function logDespesas($mensagem, $tipo = 'INFO')
{
    $logDir = BASE_PATH . '/app/helpers/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $arquivo = $logDir . 'despesas.log';
    $data = date('Y-m-d H:i:s');
    $msgFinal = "[{$data}] [{$tipo}] {$mensagem}\n";

    file_put_contents($arquivo, $msgFinal, FILE_APPEND);
}
