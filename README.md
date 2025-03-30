# Laravel API com Docker

API desenvolvida em Laravel para gerenciamento de servidores, integrada com PostgreSQL e Min.IO (S3 Compatível).  
**Ambiente totalmente containerizado com Docker.**

---

## 🚀 Como Executar o Projeto

### **Pré-requisitos**
- Docker ([Instalação](https://docs.docker.com/get-docker/))
- Docker Compose ([Instalação](https://docs.docker.com/compose/install/))

---

### **Passos Rápidos**

1. **Clone o repositório**:
   ```bash
   git clone [URL_DO_REPOSITÓRIO]
   cd laravel-api
   ```

2. **Suba os serviços**:
   ```bash
   # Construa e inicie os containers
   docker compose up -d --build
   ```

3. **Instale dependências e configure o JWT**:
   ```bash
   # Instalar pacotes essenciais (JWT e Min.IO)
   docker compose exec app composer require tymon/jwt-auth league/flysystem-aws-s3-v3

   # Publicar configurações do JWT
   docker compose exec app php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

   # Gerar a chave JWT
   docker compose exec app php artisan jwt:secret

   # Execute as migrations
   docker compose exec app php artisan migrate
   ```

4. **Acesse os serviços**:
   | Serviço      | URL                      | Credenciais               |
   |--------------|--------------------------|---------------------------|
   | **Laravel**  | `http://localhost:8000`  | -                         |
   | **Min.IO**   | `http://localhost:9001`  | `minioadmin`/`minioadmin` |
   | **PostgreSQL**| `postgres-db:5432`      | `laravel`/`secret`        |

---

### **Comandos Úteis**

| Descrição                          | Comando                              |
|------------------------------------|--------------------------------------|
| **Parar containers**               | `docker compose down`                |
| **Reconstruir containers**         | `docker compose up -d --build`       |
| **Ver logs do Laravel**            | `docker compose logs app`            |
| **Executar comandos no container** | `docker compose exec app [COMANDO]`  |
| **Listar containers ativos**       | `docker compose ps`                  |

---

### **Configuração do Ambiente**
O arquivo `.env` já está pré-configurado com:
```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=servidores_db
DB_USERNAME=laravel
DB_PASSWORD=secret

MINIO_ENDPOINT=http://minio:9000
MINIO_KEY=minioadmin
MINIO_SECRET=minioadmin
MINIO_BUCKET=servidores
```

---

### ⚠️ **Importante!**
- **Bucket no Min.IO**: Após subir o container, acesse `http://localhost:9001`, faça login e crie manualmente o bucket `servidores`.
- **Permissões**: Se houver erros de permissão, execute:
  ```bash
  docker compose exec app chmod -R 775 storage
  ```
