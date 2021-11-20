---
layout: post
title:  "Configuring WireGuard Package on pfSense 21.05 and Android"
---

As of May 5th 2021 WireGuard is available as a package through pfSense's Package Manager. This guide covers configuring a WireGuard "server" using the WireGuard package v0.1.5_3 on pfSense 21.05_2 and a WireGuard "client" on Android.

While the terms "server" and "client" are not correct WireGuard nomenclature; they will be used throughout this post to reference the pfSense appliance and remote endpoints respectively.

The requirements for this deployment were:
* Create a full tunnel VPN allowing "road warrior" clients the ability to egress all traffic from pfSense, and
* Allow clients the ability to perform name resolution to an on-premises server (e.g. Pihole).

To help conceptualize this, a simple network diagram was created.

![Network Diagram]({{ site.github.url }}/assets/images/007/01.png){:class="img-responsive center-margin"}

### Configuring the Server

Setting up the server is relatively quick in comparison to alternatives such as OpenVPN. It involves:
* Installing the WireGuard package,
* Adding a tunnel at `VPN > WireGuard`,
* Adding an interface at `Interfaces > Assignments`,
* Configuring the interface at `Interfaces > <INTERFACE_NAME>`,
* Configuring the tunnel, and
* Exposing the tunnel to the Internet.

The following two images shows a configured WireGuard tunnel and its associated interface.

![WireGuard Tunnel Configuration]({{ site.github.url }}/assets/images/009/01.png){:class="img-responsive center-margin"}

![WireGuard pfSense Interface Configuration]({{ site.github.url }}/assets/images/009/02.png){:class="img-responsive center-margin"}

After setting up the tunnel (adjusting default options as one desires), the next step is to configure firewall rules for the WireGuard interface under `Firewall > Rules > WireGuard`. In this specific deployment the following Access Control Lists (ACLs) were deployed:
1. Allow clients access to the internal name resolution server,
2. Allow clients access to the Internet, and
3. Restrict all internal communication that was not explicitly allowed above.

One will see two tabs associated with WireGuard. One for the VPN (WireGuard) and one for the tunnel's interface (in this example TUN_WG0; however naming will vary based on how it was configured). The firewall rules associated with the tunnel's interface itself can remain blank as no additional configuration is required.

The image below shows how I deployed my ACLs, but it is not the only way. For someone not aiming to perform segmentation with their deployment these ACLs may be substituted for an allow any/any rule.

![WireGuard Interface ACLs]({{ site.github.url }}/assets/images/009/03.png){:class="img-responsive center-margin"}

Finally, after configuring the firewall rules for the VPN; one needs to permit external access to the server. This involves deploying a firewall rule to the WAN interface under `Firewall > Rules > WAN`. This should be configured to allow traffic to the port that was configured when setting up the WireGuard server.

![pfSenes WAN ACLs]({{ site.github.url }}/assets/images/009/04.png){:class="img-responsive center-margin"}

### Configuring the Client

Now that the server tunnel is configured we turn our focus to the client (a.k.a peer). This involves configuring it as a peer in WireGuard on pfSense and configuring it on the device itself.

First we must generate a new public/private key pair for the Android device. Then we must exchange public keys between the Android device and pfSense.

After the key exchange is completed the Android peer can be configured on the WireGuard server. To complete this navigate to `VPN > WireGuard > Peers` and select `Add Peer`. Within this screen one can set a description for the peer, the public key for the peer, and assorted other settings. One area of importance is "Allowed IPs". By configuring the "Allowed IPs" to a single network address we can inform pfSense that any traffic destine for that IP should be delivered to that peer. The "Allowed IP" configured for a peer should be in alignment with the "Addresses" configured within the Android client (see below). This will allow for pfSense to route traffic appropriately in a multi-peer environment.

![WireGuard Peer Configuration]({{ site.github.url }}/assets/images/009/05.png){:class="img-responsive center-margin"}

Finally, the Android client needs to be configured. As illustrated in the image below the client is setup with its public/private key pair. It is also configured with a static address within the subnet configured earlier (see "Allowed IPs" above). Also, it is configured with an explicit DNS server in order to leverage the on-premises name resolution server.

Additionally, the WireGuard server is configured as a peer of the Android device. This involves installing the server's public key, configuring the public address (or domain name) of the server, and setting up the "Allowed IPs" to 0.0.0.0/0 in order to establish a full tunnel connection.

![Android Configuration]({{ site.github.url }}/assets/images/007/06.png){:class="img-responsive center-margin"}

After completing the configuration, one should be able to turn on the Android VPN client and communicate to the Internet through the VPN tunnel.