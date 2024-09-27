# React Test Application

## Prerequisites

- PHP 8.2 or newer
- composer

## Installation

1. Install Symfony CLI

See instructions here https://symfony.com/download
Then check the installation
```bash
symfony -v
```

2. Clone the git repo
```bash
git clone https://github.com/davramenko/react-test-app-backend.git
```

3. Enter the directory
```bash
cd react-test-app-backend
```

4. Run
```bash
php composer install
```
If you are running on Windows, add the full path to `php` and `composer` if required. For example `c:\php83\php.exe` or `c:\utils\php\composer.phar`

5. Create `.env.local` file in the root folder of the project
```
DATABASE_URL="mysql://rta_user:5!eKj1IncqOOGuvFHy@127.0.0.1:3306/react_test_app_backend_db?serverVersion=8.0.32&charset=utf8mb4"
MAILER_DSN=sendgrid+smtp://{{ SG.MY_SECRET }}
SIGNATURE_EXPIRATION_GAP=86400
FRONTEND_BASE_URL=http://localhost:4422
FRONTEND_CONFIRMATION_URI=/confirm_email
AUTH_SIGNATURE_SECRET=5be009ddb870442cbed8caf2c489d5ba
APP_MAIL_SENDER=dvavramenko@gmail.com
```
Instead of `{{ SG.MY_SECRET }}` ask me for the secret or use your own )

6. Save the following SQL commands into a file, say `init_backend_db.sql`
```sql
DROP DATABASE IF EXISTS react_test_app_backend_db_1;

CREATE SCHEMA react_test_app_backend_db_1 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE react_test_app_backend_db_1;

CREATE USER IF NOT EXISTS 'rta_user'@'localhost' IDENTIFIED BY '5!eKj1IncqOOGuvFHy';

GRANT ALL PRIVILEGES ON react_test_app_backend_db_1.* TO 'rta_user'@'localhost';

FLUSH PRIVILEGES;

```

7. Run the commands above against the local MySQL server. To do so you need to know
the local `root` user password if any. You may do that running the following
command:
```bash
mysql -h localhost -u root -p <init_backend_db.sql 
```
If `root` user of local MySQL server does not use password, remove the `-p`
option from the command above.

If you are running on Windows OS, add the full path for `mysql` command if required.


8. Run the following commands
```bash
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate
```

9. Create users
```bash
php bin/console app:user:create <username>
php bin/console app:admin:create <username>
```
It's recommended to use a real email address as the username

10. Start backend app

On Windows (change path to `php` executable if needed)
```bash
PATH=c:\php83;%PATH% && symfony server:start --port 8898
```
On Linux
```bash
symfony server:start --port 8898
```
