# Multi-Tenant SaaS Expense Management API

A comprehensive Laravel-based API for managing expenses in a multi-tenant SaaS environment with advanced features including authentication, role-based access control, background job processing, and audit logging.

## Features Implemented

### Task 1: Multi-Tenant Database Structure ✅
- **Companies Table**: Store company information with name and email
- **Users Table**: Modified with company_id (foreign key) and role (enum: Admin, Manager, Employee)
- **Expenses Table**: With company_id and user_id foreign keys, title, amount, and category
- **Relationships**: Proper relationships set up between all models
- **Indexes**: Performance indexes on company_id and user_id

### Task 2: API Authentication & RBAC ✅
- **Laravel Sanctum**: Token-based authentication implemented
- **Role-Based Access Control**:
  - Admin: Full access to manage users and expenses
  - Manager: Manage expenses (cannot delete users)
  - Employee: View and create expenses
- **Data Isolation**: Users can only access data from their company

### Task 3: API Endpoints ✅

#### Authentication
- `POST /api/register` - Register new company and admin user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires auth)

#### Expense Management
- `GET /api/expenses` - List expenses (paginated, searchable, filtered by company)
- `POST /api/expenses` - Create new expense
- `PUT /api/expenses/{id}` - Update expense (Managers & Admins only)
- `DELETE /api/expenses/{id}` - Delete expense (Admins only)

#### User Management
- `GET /api/users` - List users (Admins only)
- `POST /api/users` - Add user (Admins only)
- `PUT /api/users/{id}` - Update user role (Admins only)

#### Audit Logs
- `GET /api/audit-logs` - List audit logs (Admins & Managers only)
- `GET /api/audit-logs/{id}` - View specific audit log

### Task 4: Optimization & Performance ✅
- **Eager Loading**: Using `with()` to prevent N+1 queries
- **Database Indexes**: On company_id and user_id
- **Redis Caching**: Frequently accessed queries cached for 1 hour
- **Query Optimization**: Selective field loading and pagination

### Task 5: Background Job Processing ✅
- **Laravel Queues**: Configured with Redis driver
- **Weekly Report Job**: `SendWeeklyExpenseReport` runs every Monday at 9 AM
- **Scheduler**: Configured in `app/Console/Kernel.php`
- **Email Reports**: Sends expense summaries to all company admins

### Task 6: Audit Logs ✅
- **Audit Logs Table**: Tracks all changes with user_id, company_id, action, and changes
- **Change Tracking**: Old and new values stored as JSON
- **Comprehensive Logging**: Logs create, update, and delete actions
- **Queryable**: Can view audit logs through API endpoints

## Technology Stack

- **Framework**: Laravel 11
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Authentication**: Laravel Sanctum
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **PHP Version**: 8.2

## Project Structure

```
backendtestapril25/
├── app/
│   ├── Console/
│   │   └── Kernel.php (Scheduler configuration)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php
│   │   │   └── Api/
│   │   │       ├── ExpenseController.php
│   │   │       ├── UserController.php
│   │   │       └── AuditLogController.php
│   │   └── Middleware/
│   │       ├── EnsureUserInCompany.php
│   │       └── CheckRole.php
│   ├── Jobs/
│   │   └── SendWeeklyExpenseReport.php
│   ├── Models/
│   │   ├── Company.php
│   │   ├── User.php
│   │   ├── Expense.php
│   │   └── AuditLog.php
│   └── Traits/
│       └── BelongsToCompany.php
├── config/
│   ├── app.php
│   ├── database.php
│   ├── cache.php
│   ├── queue.php
│   └── redis.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_companies_table.php
│       ├── 2024_01_01_000002_create_users_table.php
│       ├── 2024_01_01_000003_create_expenses_table.php
│       ├── 2024_01_01_000004_create_audit_logs_table.php
│       └── 2024_01_01_000005_create_personal_access_tokens_table.php
├── routes/
│   └── api.php
├── Dockerfile
├── docker-compose.yml
├── nginx.conf
├── supervisord.conf
├── .env.example
├── .env
└── composer.json
```

## Getting Started with Docker

### Prerequisites
- Docker Engine installed
- Docker Compose installed
- No conflicts with ports 8000 (Nginx), 3306 (MySQL), 6379 (Redis)

### Installation & Setup

1. Navigate to the project directory:
```bash
cd backendtestapril25
```

2. Build and start the Docker containers:
```bash
docker-compose up -d
```

3. Wait for all services to be healthy (about 30-60 seconds)

4. Verify the setup:
```bash
docker-compose ps
```

All services should show "Up" status.

### Running Migrations Manually (if needed)

```bash
docker-compose exec app php artisan migrate
```

### Clear Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

## API Usage

### Base URL
```
http://localhost:8000/api
```

### 1. Register a New Company & Admin User
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Acme Corp",
    "company_email": "acme@example.com",
    "name": "John Doe",
    "email": "john@acme.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@acme.com",
    "company_id": 1,
    "role": "Admin",
    "created_at": "2024-06-03T10:00:00Z"
  },
  "token": "bearer_token_here"
}
```

### 2. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@acme.com",
    "password": "password123"
  }'
```

### 3. Create Expense (Authenticated)
```bash
curl -X POST http://localhost:8000/api/expenses \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Office Supplies",
    "amount": 150.50,
    "category": "Supplies"
  }'
```

### 4. List Expenses
```bash
curl -X GET "http://localhost:8000/api/expenses?page=1&category=Supplies" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. Create User (Admin Only)
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@acme.com",
    "password": "password123",
    "role": "Manager"
  }'
```

### 6. Update Expense (Manager/Admin)
```bash
curl -X PUT http://localhost:8000/api/expenses/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Updated Supplies",
    "amount": 200.00
  }'
```

### 7. Delete Expense (Admin Only)
```bash
curl -X DELETE http://localhost:8000/api/expenses/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 8. View Audit Logs
```bash
curl -X GET http://localhost:8000/api/audit-logs \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Container Management

### View Container Logs
```bash
# All containers
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f redis
```

### Access Container Shell
```bash
docker-compose exec app bash
```

### Stop Containers
```bash
docker-compose down
```

### Stop and Remove Volumes
```bash
docker-compose down -v
```

## Database Access

### MySQL Connection
```bash
docker-compose exec db mysql -u expense_user -p expense_db
```

Password: `expense_password`

### Redis Connection
```bash
docker-compose exec redis redis-cli
```

## Job Queue

### View Queued Jobs
```bash
docker-compose exec app php artisan queue:failed
```

### Retry Failed Jobs
```bash
docker-compose exec app php artisan queue:retry all
```

## Scheduled Tasks

The scheduler runs every minute and executes:
- Weekly expense report job (Mondays 9 AM)

To manually trigger:
```bash
docker-compose exec app php artisan schedule:run
```

## Common Issues & Solutions

### Database Connection Error
Ensure the database is ready:
```bash
docker-compose exec db mysql -h localhost -u root -proot_password -e "SELECT 1"
```

### Redis Connection Error
Check Redis is running:
```bash
docker-compose exec redis redis-cli ping
```

### Permission Errors
Fix permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

## Performance Notes

- **Eager Loading**: All queries use `with()` to avoid N+1 issues
- **Caching**: Expense lists cached for 1 hour in Redis
- **Pagination**: Default 15 items per page
- **Indexes**: All foreign keys and frequently queried fields indexed

## Security Features

- **Token-Based Authentication**: Laravel Sanctum tokens
- **Multi-Tenant Isolation**: Users restricted to their company data
- **Role-Based Access**: Three-tier role system with endpoint restrictions
- **Password Hashing**: Bcrypt hashing for all passwords
- **CSRF Protection**: Disabled for API routes (using Sanctum tokens)

## Testing

Run tests (if PHPUnit configured):
```bash
docker-compose exec app php artisan test
```

## Environment Variables

Key variables in `.env`:
- `APP_KEY`: Application encryption key
- `DB_CONNECTION`: Database driver (mysql)
- `DB_HOST`: Database host (db)
- `QUEUE_CONNECTION`: Queue driver (redis)
- `CACHE_DRIVER`: Cache driver (redis)
- `REDIS_HOST`: Redis host (redis)

## Next Steps

1. **Extend Models**: Add more fields as needed
2. **Add Pagination**: Currently set to 15 items per page
3. **Email Notifications**: Configure mail driver for audit notifications
4. **API Documentation**: Add Swagger/OpenAPI documentation
5. **Advanced Caching**: Implement cache invalidation strategies
6. **Rate Limiting**: Add throttle middleware for production

## Support

For issues or questions, refer to the Laravel documentation:
- https://laravel.com/docs/11
- https://laravel.com/docs/11/sanctum
- https://laravel.com/docs/11/queues

## License

MIT License - See LICENSE file for details
