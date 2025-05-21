<?php

    require_once __DIR__ . '/../config/clients.php';
    require_once __DIR__ . '/logger.php';
    require_once __DIR__ . '/ftp_helper.php';

    function get_offset_file($localFile) {
        return $localFile . '.offset';
    }

    function get_last_offset($localFile) {
        $offsetFile = get_offset_file($localFile);
        if (file_exists($offsetFile)) {
            return (int)file_get_contents($offsetFile);
        }
        return 0;
    }

    function set_last_offset($localFile, $offset) {
        $offsetFile = get_offset_file($localFile);
        file_put_contents($offsetFile, $offset);
    }

    $clientes = require __DIR__ . '/../config/clients.php';

    foreach ($clientes as $cliente) {
        log_msg("Processando cliente: {$cliente['nome']} (ID: {$cliente['id']})", "INFO");
        $conn = conectarFtp($cliente);
        if (!$conn) continue;

        $arquivos = listarArquivosFtp($conn, $cliente['ftp']['dir']);
        if (!$arquivos) {
            log_msg("Nenhum arquivo encontrado em {$cliente['ftp_dir']}", "INFO");
            ftp_close($conn);
            continue;
        }

        //Pasta local cliente
        $pastaLocal = __DIR__ . '/../logs/' . $cliente['id'] . '/';
        if (!is_dir($pastaLocal)) {
            mkdir($pastaLocal, 0777, true);
        }

        foreach ($arquivos as $arquivo) {
            // Filtra arquivos de log: AAAA-MM-DD_error*.txt
            if (!preg_match('/(\d{4})-(\d{2})-(\d{2})_error.*\.txt$/', $arquivo, $matches)) continue;

            $dataFormatada = $matches[1] . $matches[2] . $matches[3]; //AAAAMMDD
            $sufixo = substr($arquivo, 10); // _error*.txt
            $nomeLocal = $pastaLocal . $cliente['id'] . '_' . $dataFormatada . $sufixo;

            $remoteSize = ftp_size($conn, $arquivo);
            if ($remoteSize == -1) continue;

            $lastOffset = get_last_offset($nomeLocal);

            if ($remoteSize > $lastOffset) {
                // Baixar apenas o trecho novo do arquivo
                $fp = fopen($nomeLocal . '.tmp', 'w+');
                // Pula para o offset desejado no remoto
                if (ftp_get($conn, $fp, $arquivo, FTP_ASCII, $lastOffset)) {
                    $newData = file_get_contents($nomeLocal . '.tmp');
                    if ($newData !== false && strLen($newData) > 0) {
                        file_put_contents($nomeLocal, $newData, FILE_APPEND);
                        set_last_offset($nomeLocal, $remoteSize);
                        log_msg("Adicionado trecho novo de $arquivo ao $nomeLocal (offset $lastOffset → $remoteSize)", "INFO");
                    } else {
                        log_msg("Nenhum dado novo em $arquivo (offset $lastOffset)", "INFO");
                    }
                } else {
                    log_msg("Erro ao baixar trecho novo de $arquivo (offset $lastOffset)", "ERROR");
                }
                fclose($fp);
                unlink($nomeLocal . '.tmp');
            } else {
                log_msg("Nenhum dado novo para $arquivo (offset $lastOffset, size $remoteSize)", "INFO");
            }

            // Excluir arquivo remoto se tem mais de 2 dias
            $dataRemota = dataArquivo($conn, $arquivo);
            if ($dataRemota != -1 && $dataRemota < strtotime('-2 days')) {
                if (excluirArquivoFtp($conn, $arquivo)) {
                    log_msg("Arquivo remoto $arquivo excluído com sucesso", "INFO");
                } else {
                    log_msg("Erro ao excluir arquivo remoto $arquivo", "ERROR");
                }
            }
        }
        ftp_close($conn);
    }

    log_msg("Processamento concluído", "INFO");