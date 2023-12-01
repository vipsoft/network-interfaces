# VIPSoft\NetworkInterfaces

A class wrapper to get network interfaces. Provides a shim (using `ifconfig`)
for `net_get_interfaces` (added in PHP 7.3).

## Features

* Simple to use!

    ```php
    use VIPSoft\NetworkInterfaces;

    $netif = new NetworkInterfaces;

    // gets the host name
    $host = $netif->getHostAddr();

    // get network interfaces
    $interfaces = $netif->getNetworkInterfaces();
    ```

## Copyright

Copyright (c) 2023 Anthon Pang. See LICENSE for details.

## Contributors

* Anthon Pang [robocoder](http://github.com/robocoder)
