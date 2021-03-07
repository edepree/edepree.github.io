---
layout: post
title:  "Dynamic Blocking of Threat Feeds on pfSense"
---

The objective of this project was to replicate the functionality of Internet Protocol (IP) address block lists in [pfBlockerNG][1] on pfSense without the use of an additional plugin.

### Creating pfSense Readable Feeds

The first phase of this project involved reading threat feeds from the [Internet Storm Center's][2] (ISC) API, transforming them into a format pfSense could natively ingest, and hosting them on the Internet with minimal cost. All three of these objectives were accomplished through the use of AWS Lambda and S3. [A simple Python parser][3] was created which queried the ISC's API, formatted the data for pfSense ingestion, and wrote each feed to an [S3 Bucket][4].

### Importing IP Based Threat Feeds

The second phase of this project involved importing the formatted feeds into pfSense as aliases. This was accomplished through alias tables under `Firewall > Aliases`. When creating an alias containing IP addresses there are two different options of interest: `URL (IPs)` and `URL Table (IPs)`. The [pfSense documentation][5] discusses the difference between these two options in more detail. The driving factor for this project was the size of the block list. As some of the ISC feeds contain more than 3,000 entries the `URL Table (IPs)` option was selected. 

![Creating an IP Alias Table]({{ site.github.url }}/assets/images/008/01.png){:class="img-responsive center-margin"}

After importing a feed, if one wishes to examine the content of it, this can be accomplished by navigating to `Diagnostics > Tables` and selecting the table name correlating to the alias entry previously created.

![Viewing an IP Alias Table]({{ site.github.url }}/assets/images/008/02.png){:class="img-responsive center-margin"}


### Creating Access Control Lists (ACLs)

The final phase of this project involved activating the feeds. In the example below multiple blocking ACLs are deployed to all WAN interfaces on the pfSense appliance through an Interface Group. For a single WAN deployment this can be accomplished in the same manner but by deploying the ACLs to the WAN interface itself.

![WAN_ALL Access Control Lists]({{ site.github.url }}/assets/images/008/03.png){:class="img-responsive center-margin"}

Examining one of the ACLs' shows the configuration is relatively straight forward. The ACL blocks IPv4 connections with a source address within one of the alias table (e.g. Internet_Scanners).

![WAN_ALL Internet_Scanners Access Control List]({{ site.github.url }}/assets/images/008/04.png){:class="img-responsive center-margin"}

[1]: https://docs.netgate.com/pfsense/en/latest/packages/pfblocker.html
[2]: https://isc.sans.edu/threatfeed.html
[3]: https://github.com/edepree/isc-threat-feeds
[4]: https://isc-threat-feed-storage.s3.amazonaws.com/index.html
[5]: https://docs.netgate.com/pfsense/en/latest/firewall/aliases.html#url-aliases