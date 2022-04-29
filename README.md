# STUBBY
A simple way to generate files from stubs.

<br />

## Usage
For example we have a stub file that looks like this:
```txt
<?php

namespace {{ namespace }};

use App\Magic;

class {{ class }} extends Magic
{
    // Write down the magic here
}
```

<br />

### Via CLI
```bash
php stubby generate magic.stub FooBar.php
```
The commnd-line will ask you to give a value for every token found in the stub file.
```txt
Generating file from magic.stub

 Provide a value for {{ namespace }}:
 > App

 Provide a value for {{ class }}:
 > FooBar

Successfully generated Foobar.php
```

<br />

### Via Stubby class
```php
$values = ["namespace" => "App", "class" => "FooBar"];

Stubby::stub("magic.stub")->generate("FooBar.php", $values);
```

<br />

### Expected Output
```php
<?php

namespace App;

use App\Magic;

class FooBar extends Magic
{
    // Write down the magic here
}
```

<br />

## Roadmap
- [x] Proof of Concept
- [ ] Add tests
- [ ] Publish as a composer package
- [ ] Make documentation
