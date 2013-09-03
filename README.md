UcenterClient
=============


Introduction
------------

UcenterClient is a client for ucenter client.
[ucenter]http://www.comsenz.com/products/ucenter
#### Installation
download this project it into `./vendor/` or `./modules/`
#### Post installation

1. Enabling it in your `application.config.php`file.

    ```php
    <?php
    return array(
        'modules' => array(
            'UcenterClient',
        ),
        // ...
    );
    ```
#### use
```php
$cookie = $this->getRequest()->getCookie();
if ($cookie->offsetExists('ucenter_auth')) {
    $param = $cookie->offsetGet('ucenter_auth');
    $uc = $this->getServiceLocator()->get('ucenter_service');
    $user = $uc->getUser($param);
}
```