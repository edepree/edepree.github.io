---
layout: post
title:  "Intalling OPNsense on Amazon Lightsail"
---

This entry covers installing OPNsense on a FreeBSD 12.1 Amazon Lightsail instance. A post on the [OPNsense forums][1] provided the foundation for using the `opnsense-bootstrap.sh` script and configuring OPNsense's LAN interface to work with Lightsail. The main area of improvement in this post over the original is showing how OPNsense can be installed on FreeBSD 12 versus downgrading to FreeBSD 11.

To get started, standup a FreeBSD 12.1 VPS in Lightsail. The lowest tier instance will work; however, in my production setup, I opted for a larger one. The FreeBSD instance takes a few minutes after boot before it is operational. During this time I would suggest using a newer feature in Lightsail and adjusting the firewall rules to limit access to the VPS by both port and IP address. This will limit exposure of the management interface(s) while OPNsense is being configured.

![VPS ACLs in Lightsail]({{ site.github.url }}/assets/images/006/01.png){:class="img-responsive center-margin"}

The next step is to download the `opnsense-bootstrap.sh` script from [OPNsense's GitHub][2]. This script can be found in the "bootstrap" folder. An important item to note is, at at the time of writing this post, the "master" branch only supports FreeBSD 11; however, the "volatile/20.7" branch supports FreeBSD 12. This will eventually change as OPNsense releases mainstream support for FreeBSD 12. As a result, using the volatile branch, and the subsequent URLs, is subject to change.

{% highlight bash %}
su
pkg install wget
wget https://raw.githubusercontent.com/opnsense/update/volatile/20.7/bootstrap/opnsense-bootstrap.sh
chmod +x opnsense-bootstrap.sh
{% endhighlight %}

Once the bootstrap script is downloaded, it must be modified before execution. At the end of the script there is a reboot command which will automatically execute. If left as is, the Lightsail VPS will become unresponsive on reboot. By commenting this out we can install OPNsense and modify the appropriate files before rebooting the VPS.

{% highlight bash %}
if [ -z "${DO_BARE}" ]; then
    if [ -n "${DO_FACTORY}" ]; then
        rm -rf /conf/*
    fi

    pkg bootstrap
    pkg install ${TYPE}

    # beyond this point verify everything
    unset SSL_NO_VERIFY_PEER
    unset SSL_CA_CERT_FILE

    opnsense-update -bkf
    #reboot
fi
{% endhighlight %}

After a successful installation, the OPNsense configuration file `/usr/local/etc/config.xml` must be modified. This involves making a series of changes to the LAN interface. The most important change is adjusting the `<ipaddr>` and `<subet>` tags from using a static IP to DHCP. Some additional change I made removes IPv6 from the LAN interface. The total list of changes made, and the final code block for reference are:
* Change the value of "ipaddr" to "dhcp"
* Remove the value for "subnet"
* Remove the value for "ipaddrv6"
* Remove the value for "subnetv6"
* Remove the value for "track6-interface"
* Remove the value for "track6-prefix-id"


{% highlight xml %}
<lan>
    <enable>1</enable>
    <if>mismatch0</if>
    <ipaddr>dhcp</ipaddr>
    <subnet/>
    <ipaddrv6/>
    <subnetv6/>
    <media/>
    <mediaopt/>
    <track6-interface/>
    <track6-prefix-id/>
</lan>
{% endhighlight %}

Finally, since the IP we'll attempt to access is the VPS' public IP, but the IP assigned to OPNsense is the VPS' private IP, we'll need to create and lock the `disable_security_checks` file. If this isn't completed, and one tries to access the management interface over the Internet after reboot, access to OPNsense will not be permitted.

{% highlight bash %}
touch /tmp/disable_security_checks
chflags schg /tmp/disable_security_checks
{% endhighlight %}

If all change were successful the instance should reboot and one should be able to access OPNsense's web interface on the public IP of the VPS. A few lessons learned when configuring OPNsense as a Lightsail VPS are:
* As noted in the original post, changing the Internet facing interface from LAN to WAN will cause the box to become unresponsive and unrecoverable.
* If setting up an OpenVPN server the "Redirect IP" for the port forward should be the private IP of the VPN.
* If setting up an OpenVPN client, select the checkbox "don't pull routes". If this box is not selected, and the OpenVPN client is enabled, this can cause one to lose access to OPNsense's management interfaces. There is a small window of time when OPNsense boots in which once can login and disable the OpenVPN client if need be.

Once again, this post would not be possible without the great guide by ljvb [here][1]. I would like to thank this individual for their excellent work.

[1]: https://forum.opnsense.org/index.php?topic=12889.0
[2]: https://github.com/opnsense/update
