# Sistema de Coleta e Gerenciamento de Logs via FTP

Este sistema foi desenvolvido em PHP para automatizar o processo de coleta incremental de arquivos de log armazenados em servidores FTP de diferentes clientes. Ele baixa apenas os dados ainda não processados dos logs, armazena localmente em pastas separadas por cliente, e realiza a limpeza de arquivos remotos antigos. Todo o processo é controlado e registrado em arquivos de log do sistema, com execução facilitada via Docker.

---

## Funcionalidades

- **Leitura incremental de logs**: Cada execução baixa apenas novas linhas adicionadas aos arquivos de log desde a última leitura.
- **Suporte a múltiplos clientes**: Configuração de vários clientes, cada um com sua conexão FTP.
- **Armazenamento organizado**: Logs armazenados em subpastas por cliente, com nomes que identificam cliente, data e tipo de log.
- **Limpeza automática**: Arquivos de log remotos com mais de 2 dias são excluídos do FTP automaticamente.
- **Log do sistema**: Todas as ações e erros do processo são registrados em `logs/system/system.log`.
- **Execução via Docker**: Facilidade de instalação e execução independente do ambiente host.

---

## Estrutura de Pastas

```
seu-projeto/
├── config/
│   └── clients.php  # Configuração dos clientes
├── logs/
│   ├── system/      # Log do sistema
│   └── <idcliente>/ # Pastas com logs de cada cliente
├── src/
│   ├── ftp_helper.php
│   ├── logger.php
│   └── main.php
├── Dockerfile
└── docker-compose.yml
```

---

## Configuração

### 1. Cadastro dos Clientes

Edite o arquivo `config/clients.php`:

```php
return [
    [
        'id' => 101,
        'nome' => 'ClienteA',
        'ftp_host' => 'ftp.clientea.com',
        'ftp_user' => 'userA',
        'ftp_pass' => 'senhaA',
        'ftp_dir'  => '\/logs\/'
    ],
    // Adicione mais clientes conforme necessário
];
```

---

## Execução

### 1. Preparação

Certifique-se de ter [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/) instalados.

### 2. Rodando o Sistema

Na raiz do projeto, execute:

```sh
docker-compose up --build
```

- O sistema irá conectar-se a cada FTP dos clientes configurados, baixar apenas os dados novos dos logs diários e registrar o processo em `logs/system/system.log`.
- Os arquivos de log de cada cliente estarão em `logs/<idcliente>/`.

---

## Detalhes Técnicos

### Identificação dos Arquivos de Log

- O sistema processa arquivos de log que sigam o padrão `AAAA-MM-DD_error*.txt` (exemplos: `2025-05-21_error.txt`, `2025-05-21_errorMercadoLivre.txt`).
- Os arquivos locais são nomeados como `[idcliente]_AAAAMMDD_<sufixo>.txt`, preservando o sufixo do nome original do log.

### Leitura Incremental

- O sistema registra para cada arquivo local um arquivo auxiliar `.offset`, que marca até onde o arquivo remoto foi lido na última execução.
- Apenas novas linhas são baixadas a cada execução, evitando duplicidade.

### Limpeza Remota

- Arquivos de log remotos com mais de 2 dias são excluídos do FTP do cliente automaticamente após o processamento.

### Log do Sistema

- Todas as ações, avisos e erros são registrados no arquivo `logs/system/system.log`.
- Os níveis de log utilizados são: `INFO` e `ERROR`.

---

## Personalizações

- **Periodicidade**: Para execução automática em intervalos regulares, utilize o agendador de tarefas do sistema (cron) para rodar `docker-compose up` conforme desejado.
- **Extensões PHP**: O Dockerfile já instala a extensão `ftp` do PHP.
- **Novos formatos de log**: Para suportar novos formatos de nome de arquivo, basta ajustar a expressão regular no código.

---

## Suporte

Em caso de dúvidas ou necessidades de ajuste, consulte o código fonte nos arquivos da pasta `src/` ou abra uma issue.

---