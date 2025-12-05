<h1 align="center">Hello 👋, I'm Ramon Mendes - Software Developer</h1>

<h3 align="center">A back-end developer passionate about technology</h3>

- 🔭 I am currently working on [Back-end project development](https://github.com/RamonSouzaDev)

- 🌱 I'm currently learning **Software Architecture and Engineering**

- 📫 How to reach me **dwmom@hotmail.com**

<h3 align="left">Let's network:</h3>

<p align="left">
<a href="https://linkedin.com/in/ramon-mendes-b44456164/" target="blank"><img align="center" src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/linked-in-alt.svg" alt="LinkedIn" height="30" width="40" /></a>
</p>

<h3 align="left">Languages and Tools:</h3>

<p align="left"> 
<a href="https://www.php.net" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="php" width="40" height="40"/> </a>
<a href="https://laravel.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-original.svg" alt="laravel" width="40" height="40"/> </a>
<a href="https://www.mysql.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original-wordmark.svg" alt="mysql" width="40" height="40"/> </a>
<a href="https://www.docker.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/docker/docker-original-wordmark.svg" alt="docker" width="40" height="40"/> </a>
<a href="https://redis.io" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/redis/redis-original-wordmark.svg" alt="redis" width="40" height="40"/> </a>
<a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" alt="javascript" width="40" height="40"/> </a>
<a href="https://www.linux.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/linux/linux-original.svg" alt="linux" width="40" height="40"/> </a>
</p>

---

<h1 align="center">📦 Orders Management System</h1>

<p align="center">
  <strong>Complete REST API for order management with Laravel 10 + PHP 8.2 + MySQL 8</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/MySQL-8+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/Redis-Cache-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis">
</p>

---

## 📋 Features

- ✅ Complete CRUD for orders
- ✅ Automatic calculation of totals (subtotal - discount + tax)
- ✅ Status workflow with controlled transitions
- ✅ Soft delete for safe deletion
- ✅ Responsive frontend for API interaction
- ✅ Unit tests with PHPUnit
- ✅ Docker containerization
- ✅ Redis caching
- ✅ Audit logs

---

## 🔄 Status Workflow

```
draft → pending → paid (final)
              ↘ cancelled (final)
```

**Business Rules:**
- `quantity` must be ≥ 1
- `unit_price` must be > 0
- Order cannot be created directly as `paid`
- `paid` and `cancelled` are final states

---

## 🚀 Installing Project

<p align="left">
<a href="https://www.docker.com/" target="_blank" rel="noreferrer"> 
<img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/docker/docker-original-wordmark.svg" alt="docker" width="40" height="40"/> 
</a>
<strong>Running using Docker</strong>
</p>

**1. Clone the repository:**

```bash
git clone git@github.com:RamonSouzaDev/orders-api.git
```

**2. Enter the project folder:**

```bash
cd orders-api
```

**3. Run the setup script (Git Bash):**

```bash
bash setup.sh
```

This script will automatically:
- Create the `.env` file
- Build Docker containers
- Install Composer dependencies
- Generate application key
- Run database migrations

---

## 🌐 URL

After setup, access:

| Service | URL |
|---------|-----|
| **Frontend** | http://localhost:8000 |
| **API** | http://localhost:8000/api |
| **Health Check** | http://localhost:8000/api/health |

---

## 🧪 Unit Tests 💡

Enter the app container and run the tests:

```bash
docker-compose exec app php artisan test
```

**Tests implemented:**
- Order total calculation
- Item validation
- Status workflow transitions

---

## 📡 Routes

### Orders

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | /api/orders | List all orders (with filters) |
| `GET` | /api/orders?status=pending | Filter by status |
| `GET` | /api/orders?customer_name=John | Filter by customer name |
| `GET` | /api/orders/cursor | List with keyset pagination |
| `GET` | /api/orders/{id} | View a specific order |
| `POST` | /api/orders | Create a new order |
| `PUT` | /api/orders/{id}/status | Update order status |
| `DELETE` | /api/orders/{id} | Delete order (soft delete) |

### Health

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | /api/health | API health check |

---

## 📝 API Examples

### Create Order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "discount": 10.00,
    "tax": 5.50,
    "items": [
      {
        "product_name": "Dell Notebook",
        "quantity": 1,
        "unit_price": 3500.00
      },
      {
        "product_name": "Wireless Mouse",
        "quantity": 2,
        "unit_price": 89.90
      }
    ]
  }'
```

### List Orders

```bash
curl http://localhost:8000/api/orders
```

### Update Status

```bash
curl -X PUT http://localhost:8000/api/orders/{uuid}/status \
  -H "Content-Type: application/json" \
  -d '{"status": "pending"}'
```

### Delete Order

```bash
curl -X DELETE http://localhost:8000/api/orders/{uuid}
```

---

## 🏗️ Architecture

```
app/
├── DTOs/                    # Data Transfer Objects
├── Enums/                   # OrderStatus enum with workflow
├── Exceptions/              # Custom exceptions
├── Http/
│   ├── Controllers/Api/     # REST Controllers
│   ├── Requests/            # Form Requests validation
│   └── Resources/           # API Resources
├── Models/                  # Eloquent Models
├── Repositories/            # Repository Pattern
└── Services/                # Business Logic Layer
```

---

## ⭐ Differentials Implemented

| Feature | Description |
|---------|-------------|
| **Docker** | Complete containerized environment |
| **Unit Tests** | PHPUnit tests for business rules |
| **Repository Pattern** | Data access abstraction |
| **DTOs** | Data Transfer Objects with validation |
| **Service Layer** | Centralized business logic |
| **Redis Cache** | Query caching |
| **Audit Logs** | Change tracking |
| **Keyset Pagination** | Efficient pagination |

---

## 🛠️ Useful Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Run tests
docker-compose exec app php artisan test

# Access container
docker-compose exec app bash

# Run migrations
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear
```

---

## 📄 License

MIT License - Feel free to use this project.

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/RamonSouzaDev">Ramon Mendes</a>
</p>
