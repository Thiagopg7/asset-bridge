# asset-bridge

Aplicação Laravel para gestão de ativos, construída com Inertia + React e
servida num ambiente Docker de desenvolvimento.

## Stack

| Camada    | Tecnologia |
|-----------|------------|
| Backend   | PHP 8.5 · Laravel 13 · Fortify · Wayfinder · Tinker |
| Frontend  | Inertia.js 3 · React 19 · TypeScript · Tailwind CSS 4 · Vite |
| Banco     | MySQL 8.4 |
| Testes    | Pest 4 · PHPUnit 12 |
| Infra dev | Docker (PHP-FPM, Nginx, MySQL, Vite) |

## Pré-requisitos

- Docker e Docker Compose
- Entrada no `/etc/hosts` apontando o domínio local para o loopback:

  ```
  127.0.0.1 ponte-de-ativos.local
  ```

> A porta **80** precisa estar livre no host (o Nginx do projeto a utiliza).
> Se você roda Herd/Valet/Apache, pare-os antes de subir o ambiente.

## Como rodar (Docker)

```bash
# 1. Variáveis de ambiente
cp .env.example .env

# 2. Override de portas específico da sua máquina (ajuste se 3306 estiver ocupada)
cp docker-compose.override.yml.example docker-compose.override.yml

# 3. Sobe os containers (a primeira vez faz o build das imagens)
docker compose up -d --build

# 4. Dependências PHP e chave da aplicação
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 5. Migrations
docker compose exec app php artisan migrate
```

O container `node` roda `npm install && npm run dev` automaticamente, então o
Vite já sobe junto — não é preciso rodar manualmente.

### Acessos

| Serviço            | URL |
|--------------------|-----|
| Aplicação          | http://ponte-de-ativos.local |
| Vite (dev server)  | http://localhost:5173 |
| MySQL (do host)    | `127.0.0.1:3307` (porta definida no override) |

## Serviços do Compose

| Serviço | Descrição |
|---------|-----------|
| `app`   | PHP-FPM 8.5 (com Node e Composer). Acessado pelo Nginx via rede interna. |
| `web`   | Nginx — serve `public/` na porta 80 e encaminha PHP para `app:9000`. |
| `db`    | MySQL 8.4. Dados persistidos no volume `mysqldata`. |
| `node`  | Vite dev server na porta 5173 (HMR). |

## Comandos úteis

```bash
# Logs / status
docker compose ps
docker compose logs -f app

# Artisan / Composer / NPM dentro dos containers
docker compose exec app php artisan <comando>
docker compose exec app composer <comando>
docker compose exec node npm <comando>

# Testes
docker compose exec app php artisan test
docker compose exec app php artisan test --compact --filter=NomeDoTeste

# Formatação / lint
docker compose exec app vendor/bin/pint
docker compose exec node npm run lint

# Build de produção dos assets
docker compose exec node npm run build

# Encerrar o ambiente
docker compose down            # mantém os dados do banco
docker compose down -v         # remove também o volume do MySQL
```

## Convenções

- Commits seguem **Conventional Commits** em PT-BR (`tipo(escopo): descrição`).
- Identificadores no código em **inglês**; apenas a interface e mensagens ao
  usuário em **PT-BR**.
- Nunca versionar `.env`, chaves ou segredos. O `docker-compose.override.yml`
  (config local de cada máquina) é ignorado pelo Git.
