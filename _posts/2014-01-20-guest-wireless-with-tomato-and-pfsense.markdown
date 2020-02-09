---
layout: post
title:  "Guest Wireless with Tomato and pfSense"
---

I finally decided that it was time to implement a guest wireless network at home so I could have some tighter control when people came to visit. My existing network setup consisted of an HP ProLiant server running the latest version of pfSense and an Asus RT-N16 running DD-WRT.

The first phase of this project consisted of flashing my router from DD-WRT to the [Toastman build][1] of Tomato. I have used Tomato a lot in the past and I enjoyed the experience. I could have completed this project using DD-WRT, but I wanted to return to the firmware I had more fun with. The build that I ended up using was the latest version of RT-VLAN-Ext that worked for my router. After doing all the appropriate flashing I went about setting up the RT-N16 as a dumb AP. There are may guides on how to do this, so I won't get into details here. My AP was in the following state when I started implementing the guest wireless:

* DHCP turned off. This will come from pfSense.
* DNS turned off. This will come from pfSense.
* One working wireless network.

The first step in configuring a single AP to serve up two isolated SSIDs is to create a LAN bridge that has two distinct networks. In my case the 192.168.12.X network is the LAN and 192.168.13.X is the guest network. This is set in the 'Basic -> Network' tab.

![LAN Bridge]({{ site.github.url }}/assets/images/001/01.png){:class="img-responsive center-margin"}

The next step is to go to the 'Advanced -> VLAN' tab. From here I added a new VLAN, assigned two ports to it, and associated it with the new bridge (br1). The specific VLAN tag isn't as important here since I'm only using the VLAN functionality on my AP to isolate the wireless networks. In this case I have ports one and two as part of the LAN and ports three and four as part of the guest network. Wiring this up to the pfSense box I connected port one from the AP to the LAN NIC and port four from the AP to the guest NIC.

![VLANs]({{ site.github.url }}/assets/images/001/02.png){:class="img-responsive center-margin"}

The third step is to create a new virtual wireless interface under the 'Advanced -> Virtual Wireless' tab. This is pretty easy by clicking through the tabs and reading the notes on the page. The main thing to note here is I have one SSID associated with the LAN (br0) and the other associated with the guest network (br1).

![Wireless Interfaces]({{ site.github.url }}/assets/images/001/03.png){:class="img-responsive center-margin"}

Finally, the last item to do is push a route across so the guest network knows what gateway to talk to. In the routing table there was a default route already added when the LAN wireless network was created through the GUI. The Tomato firmware will set this up for users since it is trying to be plug-and-play. What I needed to do was add another default route for the guest network so it could properly route traffic.

![Routing Table]({{ site.github.url }}/assets/images/001/04.png){:class="img-responsive center-margin"}

With the AP configuration completed, all that was left was to create some firewall rules in pfSense that allowed communication from the guest network to the Internet, and restricted communication from the LAN to the guest network. Some simple testing found that computers on one network could not ping or access web pages from computers on the other.

[1]: http://toastmanfirmware.yolasite.com/
