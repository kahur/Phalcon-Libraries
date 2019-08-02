# Phalcon Container Builder

Container builder, aim to provide you with dependency injection building as symfony
Instances of classes and services are not created at the stage of building di container
they will be build when you call them.

## Requirements
 * Phalcon-Config
 
#### Usage

**Initial setup**

*bootstrap.php*

```php
/** @var \AW\PhalconConfig\Reader $config */
$config = $configLoader->fromFile(__DIR__.'/config/config.yml');
// init container builder and register services into DI container
$builder = new \AW\PhalconContainerBuilder\Builder($config, $di);

// build definitions and add them into DI containers, instances are not created yet
$builder->build();
```
*services.yml*

it's important that definition with starts with services
```yaml
services:
    serviceName:
      class: MyCalss
      
    anotherService:
      class: MyAnotherService
      arguments:
        - "@services.serviceName"
        - "@db.name"  

```

This definition will add 2 services into phalcon di container, when you will call
``$this->getDI()->get('anotherService');``

it will return fully builded MyAnotherService with injected service MyClass and value from configuration db name
passed as constructor arguments

If you do not wish to add some class as service, you can configure it out of services and pass it as dependency, the 
service will not be included in di but injected as dependency.

You can also name your, arguments wit as following:

```yaml

services:
    serviceName:
      class: MyCalss
      
    anotherService:
      class: MyAnotherService
      arguments:
        myService: "@services.serviceName"
        dbName: "@db.name"  

```

If you want to pass array of named values you can do as follows:

```yaml

services:
    serviceName:
      class: MyClass
      arguments:
        - name: value
          anotherName: value1
        - name1: value2
          anotherName1: value3

```

This combination will be passed as

```php
$service = new MyClass(
    array('name' => 'value', 'anotherName' => 'value1'),
    array('name1' => 'value2', 'anotherName1' => 'value3')
);
```