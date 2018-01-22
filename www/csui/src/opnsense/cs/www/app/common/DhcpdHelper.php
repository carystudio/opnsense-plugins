<?php

require_once ('system.inc');
require_once ('util.inc');
require_once ('services.inc');

class DhcpdHelper
{

    private static function dhcp_clean_leases()
    {
        global $config;

        $leasesfile = services_dhcpd_leasesfile();
        if (!file_exists($leasesfile)) {
            return;
        }

        /* Build list of static MACs */
        $staticmacs = array();
        foreach (legacy_config_get_interfaces(array("virtual" => false)) as $ifname => $ifarr) {
            if (isset($config['dhcpd'][$ifname]['staticmap'])) {
                foreach ($config['dhcpd'][$ifname]['staticmap'] as $static) {
                    $staticmacs[] = $static['mac'];
                }
            }
        }
        /* Read existing leases */
        $leases_contents = explode("\n", file_get_contents($leasesfile));
        $newleases_contents = array();
        $i = 0;
        while ($i < count($leases_contents)) {
            /* Find a lease definition */
            if (substr($leases_contents[$i], 0, 6) == "lease ") {
                $templease = array();
                $thismac = "";
                /* Read to the end of the lease declaration */
                do {
                    if (substr($leases_contents[$i], 0, 20) == "  hardware ethernet ") {
                        $thismac = substr($leases_contents[$i], 20, 17);
                    }
                    $templease[] = $leases_contents[$i];
                    $i++;
                } while ($leases_contents[$i - 1] != "}");
                /* Check for a matching MAC address and if not present, keep it. */
                if (!in_array($thismac, $staticmacs)) {
                    $newleases_contents = array_merge($newleases_contents, $templease);
                }
            } else {
                /* It's a line we want to keep, copy it over. */
                $newleases_contents[] = $leases_contents[$i];
                $i++;
            }
        }
        /* Write out the new leases file */
        $fd = fopen($leasesfile, 'w');
        fwrite($fd, implode("\n", $newleases_contents));
        fclose($fd);
    }

    public static function reconfigure_dhcpd()
    {
        /* Stop DHCP so we can cleanup leases */
        killbyname("dhcpd");
        self::dhcp_clean_leases();
        system_hosts_generate();
        clear_subsystem_dirty('hosts');
        services_dhcpd_configure();
        clear_subsystem_dirty('staticmaps');
    }

}