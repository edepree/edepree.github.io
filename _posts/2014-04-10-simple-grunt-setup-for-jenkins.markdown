---
layout: post
title:  "Simple Grunt Setup for Jenkins"
---

I know that I am late to the party, but I have been doing more web development in my free time and I have discovered Grunt! I was so happy to have a tool that would minify my code automatically. After learning about the different packages and setting up my Gruntfile.js I needed up update my Jenkins server to run my Grunt tasks as part of my build plan.

Getting Jenkins to execute my Grunt script turned out to be a relatively easy task with a [NodeJS plugin][1] for Jenkins and a little modification to the Jenkins' job. The first thing was to install the plugin which pulled NodeJS down from [nodejs.org][2]. I also updated my configuration to install grunt-cli globally as specified in Grunt's [getting started guide][3].

![Jenkin's NodeJS Configuration]({{ site.github.url }}/assets/images/003/01.png){:class="img-responsive center-margin"}

After NodeJS is setup and grunt-cli is installed, all that is needed is to configure the Jenkins job to use it. The first task is to add NodeJS and its global packages to the build path for the job. The plugin adds a nifty UI element for this. Just check the box and select which NodeJS installation you want to use. The final task is setting up the commands to install the npm packages and run Grunt. In my configuration I perform the following tasks: update npm, run Grunt, run Composer and finally execute my unit tests.

![Adding NodeJS to the Job's Build Path]({{ site.github.url }}/assets/images/003/02.png){:class="img-responsive center-margin"}

![Executing Grunt in a Jenkins Job]({{ site.github.url }}/assets/images/003/03.png){:class="img-responsive center-margin"}

Doing all this takes about 10 minutes from start to finish. One thing I like about this solution is that it pulls in NodeJS automatically through Jenkins. Another option would be to install NodeJS on the build server and manage it through traditional means (apt-get, dpkg, etc). I like having Jenkins manage this instead but both solutions work well.

[1]: https://wiki.jenkins-ci.org/display/JENKINS/NodeJS+Plugin
[2]: http://nodejs.org/
[3]: http://gruntjs.com/getting-started
