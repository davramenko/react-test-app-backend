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

5. Run the following commands
```bash
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. Create users
```bash
php bin/console app:user:create <username>
php bin/console app:admin:create <username>
```

7. Start backend app

On Windows (change path to `php` executable if needed)
```bash
PATH=c:\php83;%PATH% && symfony server:start --port 8898
```
On Linux
```bash
symfony server:start --port 8898
```
