<?php
/**
 * @copyright 2023 Anthon Pang
 * @license MIT
 */
namespace VIPSoft;

/**
 * Network Interfaces
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class NetworkInterfaces
{
    // protocol families
    const PF_INET   = 2;
    const PF_INET6  = 10;
    const PF_PACKET = 17;

    // device/interface flags
    const IFF_UP          = 1 << 0;
    const IFF_BROADCAST   = 1 << 1;
    const IFF_DEBUG       = 1 << 2;
    const IFF_LOOPBACK    = 1 << 3;
    const IFF_POINTOPOINT = 1 << 4;
    const IFF_NOTRAILERS  = 1 << 5;
    const IFF_RUNNING     = 1 << 6;
    const IFF_NOARP       = 1 << 7;
    const IFF_PROMISC     = 1 << 8;
    const IFF_ALLMULTI    = 1 << 9;
    const IFF_MASTER      = 1 << 10;
    const IFF_SLAVE       = 1 << 11;
    const IFF_MULTICAST   = 1 << 12;
    const IFF_PORTSEL     = 1 << 13;
    const IFF_AUTOMEDIA   = 1 << 14;
    const IFF_DYNAMIC     = 1 << 15;

    /**
     * Get this host's IP address
     *
     * @return array
     */
    public function getHostAddr()
    {
        $interfaces = $this->getNetworkInterfaces();

        if (is_array($interfaces) && count($ips = $this->filterInterfaces($interfaces))) {
            return $ips;
        }

        return [gethostbyaddr(gethostname())];
    }

    /**
     * Get network interfaces
     *
     * @return array|null
     */
    public function getNetworkInterfaces()
    {
        if (version_compare(PHP_VERSION, '7.3') >= 0 && function_exists('net_get_interfaces')) {
            return net_get_interfaces();
        }

        $rc = exec('ifconfig', $output, $resultCode);

        if ($rc === false) {
            return false;
        }

        return $this->parseIfconfig($output);
    }

    private function filterInterfaces($interfaces)
    {
        $ips = [];

        foreach ($interfaces as $name => $interface) {
            if (empty($interface['up']) || ! isset($interface['unicast'])) {
                continue;
            }

            foreach ($interface['unicast'] as $device) {
                if ($device['flags'] & self::IFF_LOOPBACK || ! ($device['flags'] & self::IFF_UP) || ! ($device['flags'] & self::IFF_RUNNING)) {
                    continue 2;
                }

                if ($device['family'] !== self::PF_INET) {
                    continue;
                }

                $ips[] = $device['address'];
            }
        }

        return $ips;
    }

    /**
     * Parse ifconfig output
     *
     * @param array $output
     *
     * @return array|false
     */
    private function parseIfconfig($output)
    {
        $interfaces = [];
        $name = null;
        $flags = null;

        foreach ($output as $line) {
            if (preg_match('~^([A-Za-z0-9]+):?[ \t]+(.*)~', $line, $matches)) {
                $name = $matches[1];
                $line = $matches[2];

                $flags = null;

                $interfaces[$name]['unicast'] = [[
                    'flags'  => $flags,
                    'family' => self::PF_PACKET,
                ]];
                $interfaces[$name]['up'] = false;
            }

            if ( ! $name) {
                continue;
            }

            if (($result = $this->extractFlags($line)) !== null) {
                $flags = $result;

                foreach ($interfaces[$name]['unicast'] as $i => $interface) {
                    $interfaces[$name]['unicast'][$i]['flags'] = $flags;
                }

                if ($flags & 1) {
                    $interfaces[$name]['up'] = true;
                }
            }

            if (preg_match('~\s(?:HWaddr|ether)\s([A-Fa-f0-9:]+)~', $line, $matches)) {
                $interfaces[$name]['mac'] = $matches[1];
                continue;
            }

            if (preg_match('~\s(?:mtu\s|MTU:)([0-9]+)~', $line, $matches)) {
                $interfaces[$name]['mtu'] = (int) $matches[1];
            }

            if (preg_match('~^\s+inet addr:([0-9.]+)\s+Mask:([0-9.]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'     => $flags,
                    'family'    => self::PF_INET,
                    'address'   => $matches[1],
                    'netmask'   => $matches[2],
                ];
                continue;
            }

            if (preg_match('~^\s+inet addr:([0-9.]+)\s+Bcast:([0-9.]+)\s+Mask:([0-9.]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'     => $flags,
                    'family'    => self::PF_INET,
                    'address'   => $matches[1],
                    'netmask'   => $matches[3],
                    'broadcast' => $matches[2],
                ];
                continue;
            }

            if (preg_match('~^\s+inet ([0-9.]+)\s+netmask ([0-9.]+)\s+broadcast ([0-9.]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'     => $flags,
                    'family'    => self::PF_INET,
                    'address'   => $matches[1],
                    'netmask'   => $matches[2],
                    'broadcast' => $matches[3],
                ];
                continue;
            }

            if (preg_match('~^\s+inet ([0-9.]+)\s+netmask ([0-9.]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'     => $flags,
                    'family'    => self::PF_INET,
                    'address'   => $matches[1],
                    'netmask'   => $matches[2],
                ];
                continue;
            }

            if (preg_match('~^\s+inet6 addr: ([:A-Fa-f0-9.]+)[/]([0-9]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'   => $flags,
                    'family'  => self::PF_INET6,
                    'address' => $matches[1],
                    'netmask' => $this->prefixLenToNetMask($matches[2]),
                ];
                continue;
            }

            if (preg_match('~^\s+inet6 ([:A-Fa-f0-9.]+)\s+prefixlen ([0-9]+)~', $line, $matches)) {
                $interfaces[$name]['unicast'][] = [
                    'flags'   => $flags,
                    'family'  => self::PF_INET6,
                    'address' => $matches[1],
                    'netmask' => $this->prefixLenToNetMask($matches[2]),
                ];
                continue;
            }
        }

        return $interfaces;
    }

    private function extractFlags($line)
    {
        static $deviceFlags = [
            'UP'          => self::IFF_UP,
            'BROADCAST'   => self::IFF_BROADCAST,
            'DEBUG'       => self::IFF_DEBUG,
            'LOOPBACK'    => self::IFF_LOOPBACK,
            'POINTOPOINT' => self::IFF_POINTOPOINT,
            'NOTRAILERS'  => self::IFF_NOTRAILERS,
            'RUNNING'     => self::IFF_RUNNING,
            'NOARP'       => self::IFF_NOARP,
            'PROMISC'     => self::IFF_PROMISC,
            'ALLMULTI'    => self::IFF_ALLMULTI,
            'MASTER'      => self::IFF_MASTER,
            'SLAVE'       => self::IFF_SLAVE,
            'MULTICAST'   => self::IFF_MULTICAST,
            'PORTSEL'     => self::IFF_PORTSEL,
            'AUTOMEDIA'   => self::IFF_AUTOMEDIA,
            'DYNAMIC'     => self::IFF_DYNAMIC,
        ];

        if (preg_match('~(?:^|\s)flags=([0-9]+)~', $line, $matches)) {
            return (int) $matches[1];
        }

        if (strpos($line, 'MTU:') !== false) {
            $flags = 0;

            foreach (explode(' ', trim($line)) as $word) {
                if (array_key_exists($word, $deviceFlags)) {
                    $flags |= $deviceFlags[$word];
                }
            }

            return $flags;
        }
    }

    private function prefixLenToNetMask($prefixLen)
    {
        $words = [];

        for ($i = $prefixLen; $i > 0; $i -= 16) {
            $n = $i > 16 ? 0 : 16 - $i;

            $words[] = sprintf('%4x', 0xffff & (~0 << $n));
        }

        $netMask = implode(':', $words);

        if ($prefixLen <= 112) {
            $netMask .= '::';
        }

        return $netMask;
    }
}
