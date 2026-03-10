
  ---
  .env — mudanças:
  DB_CONNECTION=pgsql
  DB_PORT=5432
  DB_DATABASE=afiliados
  DB_USERNAME=postgres

  COMO-RODAR.md — instrução de criar banco atualizada para PostgreSQL/psql.

  ---
  As migrations não precisaram de nenhuma alteração porque o Schema Builder do Laravel traduz automaticamente para PostgreSQL:

  ┌───────────────────────┬────────────────────────────┐
  │      Tipo usado       │  Como fica no PostgreSQL   │
  ├───────────────────────┼────────────────────────────┤
  │ enum(...)             │ varchar + CHECK constraint │
  ├───────────────────────┼────────────────────────────┤
  │ mediumText / longText │ text                       │
  ├───────────────────────┼────────────────────────────┤
  │ uuid / foreignUuid    │ uuid nativo                │
  ├───────────────────────┼────────────────────────────┤
  │ decimal               │ numeric                    │
  ├───────────────────────┼────────────────────────────┤
  │ useCurrent()          │ DEFAULT now()              │
  └───────────────────────┴────────────────────────────┘

  Para rodar, crie o banco no PostgreSQL e execute:
  cd api-jwt
  php artisan migrate
  php artisan serve


# Como Rodar e Testar a API

## Pré-requisitos

- PHP 8.2+
- Composer
- PostgreSQL rodando localmente

---

## 1. Configurar o banco de dados

Abra o psql (ou pgAdmin) e crie o banco:

```sql
CREATE DATABASE afiliados;
```

---

## 2. Configurar o .env

Abra o arquivo `api-jwt/.env` e edite as credenciais do banco:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=afiliados
DB_USERNAME=postgres
DB_PASSWORD=sua_senha_aqui
```

---

## 3. Instalar dependências

```bash
cd api-jwt
composer install
```

---

## 4. Rodar as migrations

```bash
php artisan migrate
```

---

## 5. Subir o servidor

```bash
php artisan serve
```

A API ficará disponível em: `http://localhost:8000`

---

## 6. Testar os endpoints

Use o Postman, Insomnia ou qualquer cliente HTTP.

### Registro de usuário

```
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
  "name": "João Silva",
  "cpf": "12345678900",
  "email": "joao@email.com",
  "password": "123456"
}
```

### Login

```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "joao@email.com",
  "password": "123456"
}
```

Resposta:

```json
{
  "access_token": "eyJ...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": { ... }
}
```

### Usar o token nas rotas protegidas

Adicione o header em todas as requisições autenticadas:

```
Authorization: Bearer SEU_TOKEN_AQUI
```

---

## 7. Endpoints disponíveis

### Autenticação (público)
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /api/auth/register | Cadastro de usuário |
| POST | /api/auth/login | Login (retorna JWT) |
| POST | /api/auth/forgot-password | Recuperar senha |

### Autenticação (requer token)
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /api/auth/refresh | Renovar token |

### Usuário (requer token)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /api/users/me | Dados do usuário logado |

### Indicações (requer token)
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /api/referrals | Criar indicação |
| GET | /api/referrals | Listar indicações (`?status=contracted`) |
| GET | /api/referrals/stats | Estatísticas de indicações |

### Cashback (requer token)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /api/cashback/balance | Saldo atual |
| GET | /api/cashback/history | Extrato (`?limit=50`) |

### Saques (requer token)
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /api/withdrawals/request | Solicitar saque |
| GET | /api/withdrawals/history | Histórico (`?status=pending`) |
| GET | /api/withdrawals/stats | Estatísticas de saques |

### Admin (requer token + role admin)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /api/admin/withdrawals | Listar todos os saques |
| GET | /api/admin/withdrawals/stats | Estatísticas gerais |
| POST | /api/admin/withdrawals/:id/approve | Aprovar saque |
| POST | /api/admin/withdrawals/:id/reject | Rejeitar saque |
| POST | /api/admin/withdrawals/:id/mark-paid | Marcar como pago |
| GET | /api/admin/users/:id | Dados do afiliado |

---

## 8. Criar usuário admin (via Tinker)

Para testar as rotas de admin, promova um usuário pelo terminal:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'joao@email.com')->update(['role' => 'admin']);
```

---

## 9. Registrar com código de indicação

Ao registrar, passe o campo `referral_code` com o código de outro usuário:

```
POST /api/auth/register

{
  "name": "Maria",
  "cpf": "98765432100",
  "email": "maria@email.com",
  "password": "123456",
  "referral_code": "ABC123"
}
```

---

## 10. Solicitar saque (exemplo)

```
POST /api/withdrawals/request
Authorization: Bearer SEU_TOKEN

{
  "amount": 50.00,
  "method": "pix",
  "pixKey": "joao@email.com"
}
```
