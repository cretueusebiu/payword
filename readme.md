## Payword

#### hosts

```
127.0.0.1           broker.payword.app
127.0.0.1           vendor.payword.app
127.0.0.1           client.payword.app
```

#### Apache vhosts

```
<VirtualHost *:80>
    DocumentRoot "path/to/payword/public"
    ServerName broker.payword.app
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "path/to/payword/public"
    ServerName vendor.payword.app
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "path/to/payword/client"
    ServerName client.payword.app
</VirtualHost>
```

##### Commands

- `composer install`
- `cp .env.example .env`
- `php artisan key:generate`
- `php artisan migrate`
- `php artisan db:seed`
- `cd client`, `npm install`, `gulp`
