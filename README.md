# Azure Client Credentials for Laravel
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
![GitHub Workflow Status (event)](https://img.shields.io/github/workflow/status/yayasanvitka/azure-client-credentials/PHPUnit%20Tests)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/yayasanvitka/azure-client-credentials)
[![codecov](https://codecov.io/gh/yayasanvitka/azure-client-credentials/branch/master/graph/badge.svg?token=46XEANZJOT)](https://codecov.io/gh/yayasanvitka/azure-client-credentials)

## About

This package does OAuth2 Client credentials grant with Microsoft Azure OAuth2 backend.
Client Credentials Grant is used to authorize application when calling API on another application. For more information, please visit [Microsoft OAuth2 Client Credentials Grant Documentation](https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-client-creds-grant-flow).

## Documentation, Installation, and Usage Instructions

### Installation
#### 1. Install the Package
Run the following command to install the package:
```bash
composer require yayasanvitka/azure-oauth2-validator
composer require rootinc/laravel-azure-middleware
```

#### 2. Publish the Package
Run the following command to publish the package:
```bash
php artisan vendor:publish --provider="Yayasanvitka\AzureOauth2Validator\AzureOauth2ValidatorServiceProvider"
```

#### 3. Add Configurations to Database Seeder
Add the following array to `Database/Seeders/ConfigTableSeeder@SettingList`:
```php
[
'key' => 'system.employee.allowed_domains',
'name' => 'Allowed domain to login',
'description' => '',
'value' => '[{"domain":"btp.ac.id"},{"domain":"iteba.ac.id"},{"domain":"yayasanvitka.id"}]',
'field' => '{"name":"value","label":"Value","type":"repeatable","fields":[{"name":"domain","type":"text","label":"Domain"}]}',
'active' => 1,
'created_at' => now('Asia/Jakarta'),
'updated_at' => now('Asia/Jakarta'),
],
```
> **Note:** You may need to log in to the app as a sysadmin (non-Microsoft account) first to ensure the config is loaded.

#### 4. Run Database Migrations
Run the following command to refresh the database and seed the new configuration:
```bash
php artisan db:seed --class=ConfigTableSeeder
```

#### 5. Add Routes for Azure Authentication
Add the following routes to `routes/azure.php`:
```php
<?php

use App\Http\Middleware\AppAzureMiddleware;

Route::get('/login/azure', [AppAzureMiddleware::class, 'azure'])->name('auth.azure');
Route::get('/login/azurecallback', [AppAzureMiddleware::class, 'azurecallback'])->name('auth.azurecallback');
Route::get('/logout/azure', [AppAzureMiddleware::class, 'azurelogout'])->name('auth.logout');
```

#### 6. Register Azure Routes
Add the following code to `bootstrap/app.php` to register the Azure routes:
```php
Route::middleware('web')
    ->group(base_path('routes/azure.php'));
```

#### 7. Implement Middleware for Azure Authentication
Create a file `app/Http/Middleware/AppAzureMiddleware.php` and add the following content:
```php
<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;
use Prologue\Alerts\Facades\Alert;
use RootInc\LaravelAzureMiddleware\Azure;
use Yayasanvitka\AzureOauth2Validator\WebToken;

class AppAzureMiddleware extends Azure
{
    public function handle($request, Closure $next)
    {
        $webToken = new WebToken($request->user(), $request->getClientIp());

        try {
            $webToken->validateUserToken();
        } catch (Exception $exception) {
            Alert::error($exception->getMessage())->flash();

            return $this->redirect($request);
        }

        return $next($request);
    }

    protected function redirect(Request $request)
    {
        auth()->logout();

        return redirect()->guest($this->login_route);
    }

    protected function success(Request $request, $access_token, $refresh_token, $profile): mixed
    {
        try {
            $user = activity()->withoutLogs(function () use ($profile, $request) {
                $user = User::updateOrCreate(
                    [
                        'email' => $profile->upn,
                        'uuid' => $profile->oid,
                    ],
                    [
                        'name' => trim($profile->name),
                        'password' => bcrypt(Str::random(18)),
                        'last_login_ip' => $profile->ipaddr,
                        'last_login_at' => now()->toDateTimeString(),
                        'azure_user' => true,
                    ]
                );

                if (User::all() == null) {
                    $user->roles()->sync(1);
                }

                Auth::login($user, true);

                (new WebToken(
                    $user,
                    $request->getClientIp()
                ))->storeAuthorizedUserTokens();

                if (app()->environment('local') && User::count() == 1) {
                    $user->roles()->sync(Role::first()->id);
                }

                return $user;
            });

            activity('access')->log('Login')->causedBy($user);
        } catch (Exception $exception) {
            Alert::error($exception->getMessage())->flash();

            return $this->redirect($request);
        }

        return parent::success($request, $access_token, $refresh_token, $profile);
    }

    public function azurecallback(Request $request)
    {
        $client = new Client();

        $code = $request->input('code');

        try {
            $response = $client->request('POST', $this->baseUrl.config('azure.tenant_id').$this->route.'token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('azure.client.id'),
                    'client_secret' => config('azure.client.secret'),
                    'code' => $code,
                    'resource' => config('azure.resource'),
                ],
            ]);

            $contents = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            return $this->fail($request, $e);
        }

        $profile = json_decode(base64_decode(explode('.', $contents->id_token)[1]));

        if (! $this->validateDomains($profile->upn)) {
            return $this->fail($request, new UnauthorizedException('You are not allowed to logon to this app!', 401));
        }

        session()->put('_rootinc_azure_access_token', $contents->access_token);
        session()->put('_rootinc_azure_refresh_token', $contents->refresh_token);

        (new WebToken(new User(), $request->getClientIp()))->storeTokens($contents);

        return $this->success($request, $contents->access_token, $contents->refresh_token, $profile);
    }

    private function validateDomains(string $email): bool
    {
        [, $domain] = explode('@', $email);

        if (! in_array($domain, Setting::allowedDomains())) {
            return false;
        }

        return true;
    }
}
```

#### 8. Update Environment Variables
Add these entries to your `.env` file:
```env
AZURE_CLIENT_ID=
AZURE_CLIENT_SECRET=
AZURE_TENANT_ID=
AZURE_RESOURCE=
AZURE_SCOPE=
```

#### 9. Update `Setting` Model
Add the following method to the `Setting` model:
```php
public static function allowedDomains(): ?array
{
    return collect(json_decode(config('system.employee.allowed_domains'), true))
        ->pluck('domain')
        ->toArray();
}
```

#### 10. Update `User` Model
Add this method to define the relation with user web tokens:
```php
public function webTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(AzureWebToken::class, 'user_id', 'id')
        ->where('revoked', 0);
}
```

### Documentation
<small>WIP</small>

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information.

### Security

If you discover any security-related issues, please email [adly@yayasanvitka.id](mailto:adly@yayasanvitka.id) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
