---
layout: post
title:  "VLANs in pfSense 2.4"
---

In the first of (what I hope will be) multiple posts I am going to document my home environment. This post will involve setting up virtual local area networks (VLANs) and access control lists (ACLs) in pfSense 2.4.

The three main pieces of hardware in my environment are:
1. A firewall running pfSense 2.4.x
2. A Cisco layer three managed switch
3. A Intel NUC running ESXi 6.7

One item that initially was daunting to me, but now in reflection is very easy, was setting up multiple VLANs within pfSense. My current environment is logically segmented by different trust levels with six VLANs. In pfSense the two main unique tasks when setting up VLANs and segmentation involve defining interfaces and creating ACLs.

The first step involves creating the VLANs and then associating them with physical interfaces. These two tasks can be accomplished at the following two locations:
* Interfaces > Assignments > VLANs
* Interfaces > Assignments > Interface Assignments

The VLANs screen is used first to define the virtual network segments. They are then associated with a physical interface under Interface Assignments.

![Defining VLANs in pfSense]({{ site.github.url }}/assets/images/005/01.png){:class="img-responsive center-margin"}

![Associating VLANs with NICs in pfSense]({{ site.github.url }}/assets/images/005/02.png){:class="img-responsive center-margin"}

After these interfaces have been created and assigned, they can be configured like the typical WAN and LAN segments. This involves setting up the network address, subnet mask, configuring DHCP, and any additional customizations.

One optional step that take to assist me in setting up easy to read ACLs is to create aliases. The standard two I use are: a port alias for HTTP and HTTPS traffic, and a network alias for private IP addresses. These are created at the Firewall > Aliases screen.

![Aliases in pfSense]({{ site.github.url }}/assets/images/005/03.png){:class="img-responsive center-margin"}

Once the networks and aliases are created, the process of network segmentation can begin. As you'll see in the following screenshot, I take a standard approach to deploying my network. This involves permitting access to pfSense itself for services such as domain name resolution (internal access), opening Internet services for hosts (external access), then explicitly blocking all additional traffic (blocking access).

![Guest Network Segmentation in pfSense]({{ site.github.url }}/assets/images/005/04.png){:class="img-responsive center-margin"}

There are a couple design choices I have made which can be accomplished in different ways. The first is Internet access. In the example of my guest network, I allow HTTP(S) traffic to all non-private addresses. This allows hosts to reach the Internet, but not other network segments. An alternative approach would be to define a higher priority rule blocking access to all private IP address and then permitting access to HTTP(S) traffic without restrictions. Both these configurations would accomplish the same task. The second design chose I made is implementing an explicit deny rule. By default pfSense has an implicit deny at the end which does this action. An explicit deny is unnecessary but something I prefer out of habit.

With these few tasks it is easy to start deploying segmentation to a home environment. Creating VLANs and ACLs allow for devices of different trust levels to be placed in different environments. This allows one to both control what access to internal resources devices have as well as what level of Internet communication devices are allowed to perform. In future posts around my home environment I will go into deeper dives on:
* Switching, PVLANs, and my network's architecture
* VMware, Ubiquiti APs, and getting everything communicating
* Additional network based restrictions and monitoring such as Graylog, pfBlockerNG, and Suricata