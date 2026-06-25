# WeatherEvent Pro


## Stack

| Camada     | Tecnologia                    |
|------------|-------------------------------|
| Backend    | Laravel 10 · PHP 8.3-FPM      |
| Frontend   | Vue 3 · TypeScript · Vite     |
| Estilo     | Tailwind CSS 4                |
| Estado     | Pinia · Vue Router 5          |
| Banco      | MySQL 8.4                     |
| Cache      | Redis 7                       |
| Servidor   | Nginx (proxy reverso)         |

---

## Pré-requisitos

- [Docker](https://www.docker.com/) com Docker Compose

---

## Configuração inicial

**1. Copie os arquivos de ambiente:**

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp docker/mysql.env.example docker/mysql.env
```

**2. Ajuste as variáveis se necessário** (as defaults já funcionam para desenvolvimento local).

---

## Subindo o projeto

```bash
docker compose up -d
```

Na primeira execução, o backend automaticamente:
- Instala as dependências PHP via Composer
- Aguarda o MySQL ficar disponível
- Roda as migrations e seeders
- Otimiza o cache do Laravel

---

## Acessos

| Serviço    | URL                        |
|------------|----------------------------|
| Frontend   | http://localhost:5173      |
| API/Backend| http://localhost:8000      |
| MySQL      | localhost:3306             |
| Redis      | localhost:6379             |

---

## Comandos úteis

```bash
# Ver logs de todos os serviços
docker compose logs -f

# Ver logs de um serviço específico
docker compose logs -f app

# Rodar artisan dentro do container
docker compose exec app php artisan <comando>

# Acessar o container do backend
docker compose exec app sh

# Parar todos os containers
docker compose down

# Parar e remover volumes (apaga o banco)
docker compose down -v
```

---

## Estrutura do projeto

```
WeatherEvent-Pro/
├── backend/          # API Laravel (PHP-FPM)
├── frontend/         # SPA Vue 3 + Vite
├── docker/
│   ├── nginx/        # Configuração do Nginx
│   └── mysql.env     # Variáveis do MySQL
└── docker-compose.yaml
```
