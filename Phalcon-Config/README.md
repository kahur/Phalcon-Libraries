# Phalcon Config

This library main focus is to add layer over configuration files, so you can combine
mutiple type's of configuration files yml, php, xml and it will merge them into one. 

Also to add support to import configurations and reference properties across configurations

## Installation

#### Composer
*composer.json*
```json
{
  "require": {
    "phalcon-libraries" : "1.0.*"
  },
  "repositories": [
    {
      "type" : "vcs",
      "url" : "https://github.com/kamilhurajt/Phalcon-Libraries"
    }
  ]
}
```

and run `composer install`

#### Usage

**Init config loader**
```php
$configLoader = new \AW\PhalconConfig\Config(
    new \AW\PhalconConfig\Importer(),
    new \AW\PhalconConfig\Reader()
);
```

Add Phalcon adapter to resolve specific file types you can register multiple adapters or custom
```php
$configLoader->addAdapter('yml', \Phalcon\Config\Adapter\Yaml::class);
```

**Read configuration file**

```php
/** @var \AW\PhalconConfig\Reader $config */
$config = $configLoader->fromFile(__DIR__.'/config/config.yml');

```

**Usage examples:**

```php

// will print property from first level
echo $config->property;

// will print subproperty under property
echo $config->property->subproperty

// will convert
var_dump($config->propertyArray->toArray());

```

**Single config file example:**

*config.yml*
```yaml
myProperty: value

anotherProperty:
    subProperty: value
    
propertyArray:
    property: value
    property1: value1
    property3: 
      - value2
      - value3

```

```php
$config = $configLoader->fromFile(__DIR__.'/config/config.yml');

// prints value
echo $config->myProperty;

// prints value
echo $config->anotherProperty->subProperty

// returns Reader object
var_dump($config->anotherProperty);

// prints array('subProperty' => 'value')
var_dump($config->anotherProperty->toArray());
```

**Importing files**

*parameters.yml:*
```yaml
db:
    host: localhost
    user: root
    pass: 1234
    name: dummy

```
*global.yml*
```yaml
application:
    name: dummy
    host: localhost

```
*config.yml*
```yaml
imports:
    - { resource: 'parameters.yml' }
    - { resource: 'global.yml '}
```

**Reading configuration**
 
 as config.yml have defintion import the config loader will automaticaly import and merge configurations
like this you can import multiple configurations into one place.
It's required to define full path to the config file if they're not located in same directory as index
```php
$config = $configLoader->fromFile(__DIR__.'/config/config.yml');

// will return array with host, usre, pass and name
$config->db->toArray();

// will return array with name and host
$config->application->toArray();
```

**Referencing values from other configuration**

global.yml
```yaml
application:
    name: dummy
    host: localhost
    ## this will use value from configuration parameters.yml under db and name
    dbname: "@db.name"
```
By this approax you can reference any value configured, keep in mind that reference with multiple values ( array ) will return Reader object

**Using environment variables in configuration**

*parameters.yml:*
```yaml
db:
    host: "~DB_HOST"
    user: "~DB_USER"
    pass: "~DB_PASSWORD"
    name: "~DB_NAME"
```
This definition will read environment variables, for example in docker if you set these environment variables, or if you set them in your system 
these variables will be used as values


