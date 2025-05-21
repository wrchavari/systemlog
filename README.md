# Sistema de Coleta e Gerenciamento de Logs via FTP

Este sistema em PHP automatiza a coleta incremental de arquivos de log armazenados em servidores FTP de diferentes clientes. Ele baixa apenas os dados ainda não processados desde a última execução, gerenciando múltiplos clientes e organizando os logs localmente por cliente e data.

---

## Funcionalidades

- **Leitura incremental de logs**: Apenas novas linhas dos arquivos são baixadas a cada execução.
- **Suporte a múltiplos clientes**: Fácil configuração de novos clientes e conexões.
- **Armazenamento organizado**: Logs salvos em subpastas por cliente, com nomes que identificam o cliente, data e tipo de log.
- **Limpeza automática**: Arquivos de log remotos com mais de 2 dias podem ser excluídos do FTP após o processamento (verifique se a opção está ativa no código).
- **Log do sistema**: Todas as ações e erros registrados em `logs/system/system.log`.
- **Execução via Docker**: Instalação e execução facilitadas.

---

## Pré-requisitos

- [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/)
- Acesso FTP válido para os clientes que terão logs coletados

---

## Estrutura de Pastas

```
seu-projeto/
├── config/
│   └── clients.php          # Configuração dos clientes
├── logs/
│   ├── system/              # Log do sistema
│   └── <idcliente>/         # Logs de cada cliente
├── src/
│   ├── ftp_helper.php
│   ├── logger.php
│   └── main.php
├── Dockerfile
└── docker-compose.yml
```

---

## Configuração

### Cadastro dos Clientes

Edite o arquivo `config/clients.php`:

```php
return [
    [
        'id' => 101,
        'name' => 'ClienteA',
        'ftp' => [
            'host' => 'ftp.clientea.com',
            'username' => 'userA',
            'password' => 'senhaA',
            'dir'  => '/logs/'
        ],
    ],
    // Adicione mais clientes conforme necessário
];
```

- `id`: Identificador único do cliente (número)
- `name`: Nome do cliente (apenas para identificação nos logs)
- `ftp.host`: Endereço do servidor FTP
- `ftp.username` e `ftp.password`: Credenciais do FTP
- `ftp.dir`: Caminho remoto dos arquivos de log

---

## Execução

### Usando Docker

Na raiz do projeto, execute:

```sh
docker-compose up --build
```

- O sistema conectará a cada FTP dos clientes configurados, baixando apenas os dados novos dos logs diários, e registrando o processo em `logs/system/system.log`.
- Os arquivos de log de cada cliente estarão em `logs/<idcliente>/`.

### Execução Manual (opcional)

Se desejar executar sem Docker, certifique-se de ter PHP 8.2+ com a extensão FTP instalada e execute:

```sh
php src/main.php
```

---

## Detalhes Técnicos

### Padrão dos Arquivos de Log

- O sistema processa arquivos que sigam o padrão `AAAA-MM-DD_error*.txt` (exemplos: `2025-05-21_error.txt`, `2025-05-21_errorMercadoLivre.txt`).
- Os arquivos locais são nomeados como `[idcliente]_AAAAMMDD_<sufixo>.txt`, preservando o sufixo do nome original do log.

#### Exemplo de arquivo local gerado

```
logs/101/101_20250521_errorMercadoLivre.txt
```

### Leitura Incremental

- Para cada arquivo local, o sistema cria um arquivo `.offset` auxiliar, marcando até onde o arquivo remoto foi lido na última execução.
- Apenas novas linhas são baixadas a cada execução, evitando duplicidade.

### Limpeza Remota

- (Opcional) Arquivos remotos com mais de 2 dias podem ser excluídos do FTP após o processamento.
- Para ativar/desativar este recurso, ajuste no código-fonte em `src/main.php`.

### Log do Sistema

- Todas as ações, avisos e erros são registrados no arquivo `logs/system/system.log`.
- Os níveis de log utilizados são: `INFO` e `ERROR`.

---

## Personalizações

- **Periodicidade**: Para execução automática em intervalos regulares, utilize o cron (ou equivalente) para rodar o sistema.
- **Novos formatos de log**: Para suportar novos formatos de nome de arquivo, ajuste a expressão regular no código.
- **Integração com ELK**: Exemplos de configuração para Filebeat estão disponíveis em `docker/filebeat.yml`.

---

## Suporte

Em caso de dúvidas ou necessidades de ajuste, confira o código-fonte nos arquivos da pasta `src/` ou abra uma issue.

---

## Como contribuir

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b minha-feature`)
3. Commit suas alterações (`git commit -am 'Minha nova feature'`)
4. Faça push para o branch (`git push origin minha-feature`)
5. Abra um Pull Request

---
