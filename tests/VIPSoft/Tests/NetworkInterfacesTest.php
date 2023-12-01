<?php
/**
 * @copyright 2023 Anthon Pang
 * @license MIT
 */
namespace VIPSoft\Tests;

use VIPSoft\NetworkInterfaces;

/**
 * Network Interfaces
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class NetworkInterfacesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test filterInterfaces
     *
     * @param integer $lineNo
     * @param string  $expected
     * @param string  $output
     *
     * @dataProvider provideFilterInterfaces
     */
    public function testFilterInterfaces($lineNo, $expected, $output)
    {
        $service    = new NetworkInterfaces;
        $method     = $this->makeCallable($service, 'parseIfconfig');
        $interfaces = $method->invoke($service, explode("\n", $output));
        $method     = $this->makeCallable($service, 'filterInterfaces');
        $ips        = $method->invoke($service, $interfaces);

        $this->assertEquals($expected, $ips);
    }

    /**
     * Data provider for testFilterInterfaces
     *
     * @return array
     */
    public static function provideFilterInterfaces()
    {
        return [
            // 12.04 LTS
            [
                __LINE__,
                ['10.171.0.29'],
                <<<IFCONFIG
eth0      Link encap:Ethernet  HWaddr ce:51:0e:b7:f0:84
          inet addr:10.171.0.29  Bcast:10.171.255.255  Mask:255.255.0.0
          inet6 addr: fe80::cc51:eff:feb7:f084/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:748173385 errors:0 dropped:0 overruns:0 frame:0
          TX packets:769306587 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000
          RX bytes:1371386185139 (1.3 TB)  TX bytes:66017792360 (66.0 GB)

lo        Link encap:Local Loopback
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:16436  Metric:1
          RX packets:138 errors:0 dropped:0 overruns:0 frame:0
          TX packets:138 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:0
          RX bytes:12315 (12.3 KB)  TX bytes:12315 (12.3 KB)

IFCONFIG
            ],
            // 14.04 LTS
            [
                __LINE__,
                ['10.211.2.19'],
                <<<IFCONFIG
eth0      Link encap:Ethernet  HWaddr 9e:b0:42:f4:85:3f
          inet addr:10.211.2.19  Bcast:10.211.255.255  Mask:255.255.0.0
          inet6 addr: fe80::9cb0:42ff:fef4:853f/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:624738834 errors:0 dropped:0 overruns:0 frame:0
          TX packets:10753209 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000
          RX bytes:39389775051 (39.3 GB)  TX bytes:1372691327 (1.3 GB)

lo        Link encap:Local Loopback
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:65536  Metric:1
          RX packets:9318 errors:0 dropped:0 overruns:0 frame:0
          TX packets:9318 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1
          RX bytes:900680 (900.6 KB)  TX bytes:900680 (900.6 KB)

IFCONFIG
            ],
            // 16.04 LTS (preprod)
            [
                __LINE__,
                ['10.181.0.10'],
                <<<IFCONFIG
docker0   Link encap:Ethernet  HWaddr 02:42:5f:cf:3f:eb
          inet addr:172.17.0.1  Bcast:172.17.255.255  Mask:255.255.0.0
          UP BROADCAST MULTICAST  MTU:1500  Metric:1
          RX packets:0 errors:0 dropped:0 overruns:0 frame:0
          TX packets:0 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:0
          RX bytes:0 (0.0 B)  TX bytes:0 (0.0 B)

ens18     Link encap:Ethernet  HWaddr 26:18:5e:84:f2:4c
          inet addr:10.181.0.10  Bcast:10.181.255.255  Mask:255.255.0.0
          inet6 addr: fe80::2418:5eff:fe84:f24c/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:1064744 errors:0 dropped:0 overruns:0 frame:0
          TX packets:470805 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000
          RX bytes:768572165 (768.5 MB)  TX bytes:135043252 (135.0 MB)

lo        Link encap:Local Loopback
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:65536  Metric:1
          RX packets:920525 errors:0 dropped:0 overruns:0 frame:0
          TX packets:920525 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1
          RX bytes:48218434 (48.2 MB)  TX bytes:48218434 (48.2 MB)
IFCONFIG
            ],
            // 16.04 LTS (sw)
            [
                __LINE__,
                ['172.25.12.1', '172.26.11.64'],
                <<<IFCONFIG
ens3      Link encap:Ethernet  HWaddr 02:d3:f8:be:cf:cc
          inet addr:172.25.12.1  Bcast:172.25.255.255  Mask:255.255.0.0
          inet6 addr: fe80::d3:f8ff:febe:cfcc/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:9000  Metric:1
          RX packets:250511356 errors:0 dropped:0 overruns:0 frame:0
          TX packets:58250718 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000
          RX bytes:32930590946 (32.9 GB)  TX bytes:27785241707 (27.7 GB)

ens20     Link encap:Ethernet  HWaddr 0a:cb:15:bb:8f:e3
          inet addr:172.26.11.64  Bcast:172.26.11.255  Mask:255.255.255.0
          inet6 addr: fe80::8cb:15ff:febb:8fe3/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:1719320 errors:0 dropped:0 overruns:0 frame:0
          TX packets:491215 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000
          RX bytes:115249565 (115.2 MB)  TX bytes:59054537 (59.0 MB)

lo        Link encap:Local Loopback
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:65536  Metric:1
          RX packets:812357 errors:0 dropped:0 overruns:0 frame:0
          TX packets:812357 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1
          RX bytes:107004049 (107.0 MB)  TX bytes:107004049 (107.0 MB)

IFCONFIG
            ],
            // 18.04 LTS
            [
                __LINE__,
                ['10.171.0.63'],
                <<<IFCONFIG
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.171.0.63  netmask 255.255.0.0  broadcast 10.171.255.255
        inet6 fe80::9412:e3ff:fe2b:2481  prefixlen 64  scopeid 0x20<link>
        ether 96:12:e3:2b:24:81  txqueuelen 1000  (Ethernet)
        RX packets 23134935  bytes 3018616400 (3.0 GB)
        RX errors 0  dropped 834  overruns 0  frame 0
        TX packets 1976159  bytes 382994147 (382.9 MB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 6460132  bytes 679375885 (679.3 MB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 6460132  bytes 679375885 (679.3 MB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

IFCONFIG
            ],
            // 20.04 LTS
            [
                __LINE__,
                ['172.25.31.1'],
                <<<IFCONFIG
ens19: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 172.25.31.1  netmask 255.255.0.0  broadcast 172.25.255.255
        inet6 fe80::546f:13ff:fe73:5f  prefixlen 64  scopeid 0x20<link>
        ether 56:6f:13:73:00:5f  txqueuelen 1000  (Ethernet)
        RX packets 3445914122  bytes 609995823746 (609.9 GB)
        RX errors 0  dropped 3012  overruns 0  frame 0
        TX packets 918102076  bytes 75526837580 (75.5 GB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 405203611  bytes 100620563280 (100.6 GB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 405203611  bytes 100620563280 (100.6 GB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

IFCONFIG
            ],
            // 22.04 LTS
            [
                __LINE__,
                ['10.191.0.70'],
                <<<IFCONFIG
docker0: flags=4099<UP,BROADCAST,MULTICAST>  mtu 1500
        inet 172.17.0.1  netmask 255.255.0.0  broadcast 172.17.255.255
        ether 02:42:b6:10:ac:8c  txqueuelen 0  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

ens18: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.191.0.70  netmask 255.255.0.0  broadcast 10.191.255.255
        inet6 fe80::5c29:bbff:fe29:e471  prefixlen 64  scopeid 0x20<link>
        ether 5e:29:bb:29:e4:71  txqueuelen 1000  (Ethernet)
        RX packets 91420685  bytes 39820324090 (39.8 GB)
        RX errors 0  dropped 444  overruns 0  frame 0
        TX packets 136281170  bytes 36652172731 (36.6 GB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 7750070  bytes 15120214230 (15.1 GB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 7750070  bytes 15120214230 (15.1 GB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

IFCONFIG
            ],
        ];
    }

    /**
     * Make private and protected function callable
     *
     * @param mixed  $object   Subject under test
     * @param string $function Function name
     *
     * @return \ReflectionMethod
     */
    protected function makeCallable($object, $function)
    {
        $method = new \ReflectionMethod($object, $function);
        $method->setAccessible(true);

        return $method;
    }
}
