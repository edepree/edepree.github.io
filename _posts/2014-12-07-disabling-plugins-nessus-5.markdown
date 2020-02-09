---
layout: post
title:  "Disabling Plugins with the Nessus v5 API"
---

I recently found myself creating a policy in Nessus manually in a way that should be automated. Periodically, a new Nessus profile needs to be created that has selected plugins disabled based on filter criteria. Since Nessus releases new plugins all the time, the enabled plugins need to be reevaluated when a new profile is built. Nessus v5 has an API for interfacing with it, but the process for making a new policy with disabled plugins is not clearly defined in the documentation.

The Nessus API has a method for creating a policy, defining the setting on it and even setting up the plugins for the policy. The example POST request in the documentation shows how to use the API.

{% highlight html %}
POST /policy/add HTTP/1.1
Host: localhost:8834
Connection: keep-alive
Content-Length: 2163937
Origin: https://localhost:8834
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.5 (KHTML, like Gecko)
Chrome/19.0.1084.52 Safari/536.5
content-type: application/x-www-form-urlencoded
Accept: */*
Referer: https://localhost:8834/NessusClient.swf
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3
Cookie: token=765023803756f2bc7f882929f3324e5f111470bfc8bb38eb
Cleartext%20protocols%20settings%5Bcheckbox%5D%3ATry%20to%20perform%20patch%20level%20c
hecks%20over%20rexec=no&Patch%20Management%3A%20VMware%20Go%20Server%20Settings%
5Bentry%5D%3ADomain%20%3A=&Ping%20the%20remote%20host%5Bentry%5D%3ATCP%20ping%20
destination%20port%28s%29%20%3A=built%2Din&Login%20configurations%5Bentry%5D%3AI
MAP%20account%20%3A=&Global%20variable%20settings%5Bcheckbox%5D%3AEnable%20CGI%2
0scanning=no&SSH%20settings%5Bfile%5D%3ASSH%20known%5Fhosts%20file%20%3A=&Web%20
Application%20Tests%20Settings%5Bcheckbox%5D%3AHTTP%20Parameter%20Pollution=no
[..]
{% endhighlight %}

Unfortunately the documentation states “This function creates a new policy with scan options specified. The policy must be submitted with a defined list of server preferences, plugin preferences, and the plugin list (all of which can be obtained via other API calls).” It does not provide specific examples of how to structure the plugin information. My first idea was to review how the web interface creates a request.

Reviewing the POST request from the Nessus web interface in Burp was not helpful. First, Nessus is creating a new policy using an update request instead of the add request defined in the API. Second, the parameters used by the web interface are not the same ones defined in the Nessus API. For example, general.Basic.0=ggggggggggggggggg is setting the policy name to “ggggggggggggggggg” where as using the API this is accomplished with policy_name=ggggggggggggggggg. Instead of analyzing all the POST requests needed to build a profile I decided to search the Nessus forms for help. This lead me to a [form post][1] which introduced me to two new commands.

{% highlight html %}
plugin_selection.family.<Family Name>=enabled
plugin_selection.individual_plugin.<plugin_id>=enabled
{% endhighlight %}

I first tested out disabling a plugin family and it worked like a charm! Next I sent a request to disabled some plugins when building a profile…and nothing happened. The Nessus web interface reported all plugins as enabled. After hitting my head against a wall as to why this was not working I decided to try the opposite approach. Disable a family of plugins and enabled specific plugins. This produced similar results as before. The entire family was disabled including any plugins that I explicitly enabled.

After some analysis of a .nessus file from an existing policy I made a discovery to how Nessus disables plugins, enter mixed mode. Instead of setting a plugin family to enabled or disabled, Nessus sets it to mixed. This disabled all plugins for that family by default. Nessus then enabled individual plugins for that family. It is similar to the approach I tried earlier. It appears setting a family to disabled overrides all individual plugin settings where mixed mode does not.

All this information will be deprecated with the release of the Nessus v6 API, but hopefully it’s helpful for anyone looking to add some automation to Nessus and has not upgraded yet.

[TL;DR] After a weekend struggling with the Nessus API, enabling a subset of plugins when creating a new policy proved to be easy. An example command to only enabled the ZXShell Malware Services Detection plugin in the Backdoors family is:

{% highlight html %}
plugin_selection.family.Backdoors%3Dmixed&plugin_selection.individual_plugin.78430%3Denabled
{% endhighlight %}

[1]: https://discussions.tenable.com/thread/7787
