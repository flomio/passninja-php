<p align="center">
    <img width="400px" src=https://user-images.githubusercontent.com/1587270/74537466-25c19e00-4f08-11ea-8cc9-111b6bbf86cc.png>
</p>
<h1 align="center">passninja-php</h1>
<h3 align="center">
Use <a href="https://passninja.com/docs">passninja-php</a> as a PHP package.</h3>
<div align="center">
    <a href="https://github.com/flomio/passninja-php">
        <img alt="Status" src="https://img.shields.io/badge/status-active-success.svg" />
    </a>
    <a href="https://github.com/flomio/passninja-php/issues">
        <img alt="Issues" src="https://img.shields.io/github/issues/flomio/passninja-php.svg" />
    </a>
    <a href="https://packagist.org/packages/passninja/passninja">
        <img alt="Package" src="https://img.shields.io/gem/v/passninja.svg?style=flat-square" />
    </a>
</div>

# Contents
- [Contents](#contents)
- [Installation](#installation)
- [Usage](#usage)
  - [`Passninja::Client`](#passninjaclient)
  - [`Passninja::Client Methods`](#passninjaclient-methods)
  - [Examples](#examples)
- [Documentation](#documentation)

# Installation
Install via Composer:
```sh
composer require passninja/passninja
```

# Usage
## `Passninja::Client`
Use this class to create a `Passninja::Client` object. Make sure to
pass your user credentials to make any authenticated requests.
```php
require 'passninja'

account_id = '**your-account-id**'
api_key = '**your-api-key**'
pass_ninja_client = Passninja::Client.new(account_id, api_key)
```

We've placed our demo user API credentials in this example. Replace it with your
[actual API credentials](https://passninja.com/auth/profile) to test this code
through your PassNinja account and don't hesitate to contact
[PassNinja](https://passninja.com) with our built in chat system if you'd like
to subscribe and create your own custom pass type(s).

## `Passninja::Client Methods`
This library currently supports methods for creating, getting, updating, and
deleting passes via the PassNinja API. The methods are outlined below.

### Get Pass Template Details
```php
pass_template = pass_ninja_client.pass_templates.find('ptk_0x14') # pass template key
puts pass_template['pass_type_id']
```

### Create
```php
simple_pass_object = pass_ninja_client.passes.create(
  'ptk_0x14', # passType
  { discount: '50%', memberName: 'John' } # passData
)
puts simple_pass_object['url']
puts simple_pass_object['passType']
puts simple_pass_object['serialNumber']
```

### Find
Finds issued passes for a given pass template key
```php
pass_objects = pass_ninja_client.passes.find('ptk_0x14') # passType aka pass template key
```

### Get
```php
detailed_pass_object = pass_ninja_client.passes.get(
  'ptk_0x14', # passType
  'ce61b0e13da9a7fe7e' # serialNumber
)
```

### Decrypt
Decrypts issued passes payload for a given pass template key
```php
decrypted_pass_object = pass_ninja_client.passes.decrypt(
  'ptk_0x14', # passType
  '55166a9700250a8c51382dd16822b0c763136090b91099c16385f2961b7d9392d31b386cae133dca1b2faf10e93a1f8f26343ef56c4b35d5bf6cb8cd9ff45177e1ea070f0d4fe88887' # payload
)
```

### Update
```php
updated_pass_object = pass_ninja_client.passes.update(
  'ptk_0x14', # passType
  'ce61b0e13da9a7fe7e', # serialNumber
  { discount: '100%', memberName: 'Ted' } # passData
)
```

### Delete
```php
deleted_pass_serial_number = pass_ninja_client.passes.delete(
  'ptk_0x14', # passType
  'ce61b0e13da9a7fe7e' # serialNumber
)
puts "Pass deleted. Serial_number: #{deleted_pass_serial_number}"
```

# Documentation
- [PassNinja Docs](https://www.passninja.com/documentation)
