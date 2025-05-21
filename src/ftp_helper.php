<?php

    require_once __DIR__ . '/logger.php';

    function conectarFtp($cliente) {
        $conn = ftp_connect($cliente['ftp']['host']);
        if (!$conn) {
            log_msg("Falha ao conectar ao servidor FTP: " . $cliente['ftp']['host'], 'ERROR');
            return false;
        }
        if (!ftp_login($conn, $cliente['ftp']['username'], $cliente['ftp']['password'])) {
            log_msg("Falha ao autenticar no servidor FTP: " . $cliente['ftp']['host'], 'ERROR');
            ftp_close($conn);
            return false;
        }
        ftp_pasv($conn, true);
        return $conn;
    }

    function listarArquivosFtp($conn, $diretorio) {
        return ftp_nlist($conn, $diretorio);
    }

    function baixarArquivoFtp($conn, $remoteFile, $localFile) {
        return ftp_get($conn, $localFile, $remoteFile, FTP_ASCII);
    }

    function dataArquivo($conn, $remoteFile) {
        return ftp_mdtm($conn, $remoteFile);
    }

    function excluirArquivoFtp($conn, $remoteFile) {
        return ftp_delete($conn, $remoteFile);
    }