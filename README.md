# NhGenerator

An simple packages which help the developers create the super boring C-R-U-D features in a second with CLI.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

You need to install the Laravel Collective packages before using this packages.

```
composer require "laravelcollective/html":"^5.4.0"
```
Next, add your new provider to the providers array of config/app.php:

```
  'providers' => [
    // ...
    Collective\Html\HtmlServiceProvider::class,
    // ...
  ],
```
Finally, add two class aliases to the aliases array of config/app.php:
```
  'aliases' => [
    // ...
      'Form' => Collective\Html\FormFacade::class,
      'Html' => Collective\Html\HtmlFacade::class,
    // ...
  ],
 ```

 The Second things to need to do is clone the Admin SB2 to create the templates. I don't want to include all these css, js because it's not necessary. Please following the step.

 ```
git clone https://github.com/BlackrockDigital/startbootstrap-sb-admin-2.git
 ``` 

Then copy the html and css assets into the folder public/assets/admin.

*Please copy exactly the folder*

### Installing

Runining the following CLI code to install this packages

```
composer require "nguyenhoang/nhgenerator @dev"
```

Next, add your new provider to the providers array of config/app.php:

```
'providers' => [
    // ...
    NguyenHoang\NhGenerator\NhGeneratorServiceProvider::class,
    // ...
  ],
```

## Usage

After you install this package, you open the ternimal and you will see an new cli called "make:crud".

The artisan commands is

```
php artisan make:crud your-model-name
```
**The model name should be in Upper Case first letter and no Plural Form.**



## Example:

Firstly, you need to create a migration and model.

```
php artisan make:model User -m
```

Then, you can use the artisan commds below to create the full C-R-U-D features with Mode, Controller and View.
```
php artisan make:crud User
```

## Authors

* **Nguyen Hoang** - *Initial work* - 
* **kEpEx** - Special thanks to kEpEx to help me create this package base on yours [laravel-crud-generator
](https://github.com/kEpEx/laravel-crud-generator)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

