# Loyalty System

A robust loyalty points management system built with Symfony 6, MySQL, and Docker. The system manages member accounts, track transactions, award loyalty points, and process gift redemptions.

## Features

- **Member Management**: Create and manage member accounts with email verification
- **Wallet System**: Each member has a wallet to track their loyalty points balance
- **Transaction Processing**: Record monetary transactions and automatically award loyalty points
- **Points Management**: Calculate and track loyalty points based on transaction amounts
- **Gift Redemption**: Enable members to redeem accumulated points for gifts
- **Wallet Consistency**: Built-in validation and consistency checks for wallet integrity
- **Database Migrations**: Automated schema management with Doctrine Migrations

## Tech Stack

- **Framework**: Symfony 6.4
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0
- **ORM**: Doctrine ORM 3.6
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx 1.27

## Project Structure

```
├── bin/                              # CLI commands
├── config/                           # Configuration files
│   ├── packages/                     # Service configurations
│   ├── routes/                       # Route definitions
│   ├── bundles.php                   # Bundle configuration
│   ├── services.yaml                 # Service definitions
│   └── routes.yaml                   # Main routes
├── docker/                           # Docker configurations
│   ├── nginx/                        # Nginx configuration
│   └── php/                          # PHP-FPM Dockerfile
├── migrations/                       # Database migrations
├── src/                              # Application source code
│   ├── Command/                      # CLI commands
│   ├── Controller/                   # API controllers
│   ├── Entity/                       # Doctrine entities
│   ├── Repository/                   # Database repositories
│   ├── Service/                      # Business logic services
│   └── Kernel.php                    # Symfony Kernel
├── public/                           # Public web root
├── var/                              # Cache and logs
├── docker-compose.yml                # Docker compose configuration
├── composer.json                     # PHP dependencies
└── SETUP.md                          # Detailed setup instructions
```

## Core Entities

### Member
Represents a loyalty system member with:
- Unique email address
- Full name
- Creation timestamp
- Associated wallet and transactions

### Wallet
Tracks member loyalty points:
- Current point balance
- Associated member (one-to-one relationship)
- Collection of points
- Last update timestamp

### Point
Individual loyalty point records:
- Associated wallet
- Point value
- Creation timestamp

### Transaction
Records monetary transactions:
- Member reference
- Transaction amount
- Points awarded
- Transaction status and timestamp

### Redemption
Records point redemption events:
- Member reference
- Associated gift
- Points spent
- Redemption timestamp

### Gift
Available items for redemption:
- Gift name and description
- Required points to redeem
- Availability status

## Core Services

### TransactionService
Handles transaction creation and point calculation:
- Validates transaction amounts
- Calculates loyalty points based on amount
- Updates wallet balance in atomic transactions
- Ensures data consistency

### RedemptionService
Manages point redemption process:
- Validates sufficient points
- Updates wallet balance
- Records redemption records
- Handles gift availability

### WalletConsistencyService
Validates and maintains wallet integrity:
- Consistency checks for wallet balances
- Point reconciliation
- Data validation

## Getting Started

### Prerequisites

- Docker and Docker Compose installed on your system
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd loyalty-system
   ```

2. **Start Docker services**
   ```bash
   docker compose up -d --build
   ```

   This starts:
   - PHP-FPM service on port 8090
   - MySQL database on port 3308
   - phpMyAdmin on port 8081
   - Nginx web server

3. **Verify database connection**
   ```bash
   docker compose exec php php bin/console doctrine:query:sql "SELECT 1"
   ```

4. **Run migrations**
   ```bash
   docker compose exec php php bin/console doctrine:migrations:migrate -n
   ```

### Environment Configuration

The project uses `.env` file for configuration. Key variables:

```dotenv
DATABASE_URL="mysql://loyalty_user:loyalty_pass@mysql:3306/loyalty_db?serverVersion=8.0&charset=utf8mb4"
```

These are automatically configured in the Docker setup.

## Running the Application

### Access the Application

- **Web Application**: http://localhost:8090
- **phpMyAdmin**: http://localhost:8081
  - Server: `mysql`
  - User: `root`
  - Password: `root`

### CLI Commands

Run commands inside the PHP container:

```bash
docker compose exec php php bin/console [command]
```

Available commands:
- `doctrine:migrations:migrate` - Run pending migrations
- `doctrine:query:sql` - Execute SQL queries
- `cache:clear` - Clear application cache

### Wallet Balance Check

Check wallet consistency:

```bash
docker compose exec php php bin/console app:wallet:check
```

## Development

### Making Migrations

After modifying entities:

```bash
docker compose exec php php bin/console make:migration
docker compose exec php php bin/console doctrine:migrations:migrate -n
```

### Composer Commands

Install dependencies:

```bash
docker compose run --rm php composer install
```

Add new packages:

```bash
docker compose run --rm php composer require [vendor/package]
```

## Database Access

### Via MySQL CLI

```bash
docker compose exec mysql mysql -u loyalty_user -ployal_pass loyalty_db
```

### Via phpMyAdmin

Navigate to http://localhost:8081
- Server: `mysql`
- Username: `root`
- Password: `root`

## Docker Services

### PHP Service
- Container: `loyalty_php`
- PHP-FPM with Composer
- Source code volume mounted

### Nginx Service
- Container: `loyalty_nginx`
- Reverse proxy to PHP-FPM
- Port: 8090

### MySQL Service
- Container: `loyalty_mysql`
- Database: `loyalty_db`
- Port: 3308 (host) → 3306 (container)
- Data volume: `mysql_data`

### phpMyAdmin Service
- Container: `loyalty_phpmyadmin`
- Port: 8081
- Database UI management

## Stopping Services

```bash
docker compose down
```

To also remove volumes:

```bash
docker compose down -v
```

## Troubleshooting

### Database Connection Issues
- Ensure MySQL container is healthy: `docker compose ps`
- Check logs: `docker compose logs mysql`
- Verify connection: `docker compose exec php php bin/console doctrine:query:sql "SELECT 1"`

### Permission Issues
- If composer fails, check container user permissions
- Files may need permission adjustment on the host system

### Port Conflicts
If ports 8090, 8081, or 3308 are already in use, modify `docker-compose.yml`:
- Change `8090:80` to `8092:80` for Nginx
- Change `8081:80` to `8082:80` for phpMyAdmin
- Change `3308:3306` to `3309:3306` for MySQL

## Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make your changes and commit: `git commit -m 'Add your feature'`
3. Push to the branch: `git push origin feature/your-feature`
4. Open a pull request

## License

This project is proprietary. All rights reserved.

## Support

For issues, questions, or contributions, please create an issue in the repository.
