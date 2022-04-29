# Stubby
A simple way to generate files from stubs.

<br />

## Usage via CLI
```php
php stubby generate stubfile.stub MyClass.php
```

## Usage via Stubby class
```php
$values = ["namespace" => "App\MyClass", "class" => "MyClass"];

Stubby::stub("stubfile.stub")
    ->generate("MyClass.php", $values);
```
