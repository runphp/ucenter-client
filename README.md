UcenterClient
=============


Introduction
------------

UcenterClient is a ucenter client for zf2.

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
2. Copy the `config/ucenter.global.php.dist` file into your `./config/autoload` directory. (Remove the .distpart) and configure it

3. use

    ```php
    // ...
    $cookie = $this->getRequest()->getCookie();
    if ($cookie->offsetExists('ucenter_auth')) {
       $param = $cookie->offsetGet('ucenter_auth');
       $uc = $this->getServiceLocator()->get('ucenter_service');
       $user = $uc->getUser($param);
    }
    // ...
    ```
4. Any problem email to runphp@qq.com
