# HelpUkraine.php

## Help to stop the Russian war in Ukraine through information


### What's happening in Ukraine, Russia and Belarus

On the 24th of February 2022 Russian armed forces invaded Ukraine and started war against a sovereign country. The attack came from both Russia and Belarus. Thousands of Ukrainian and Russian soldiers and hundreds of Ukrainian civilians have already been killed.

Russian citizens who protest against the war are getting arrested for up to 15 years. Even if you just say in Russia that attacking another country with weapons is a war, you may be prosecuted the same way.

Foreign media, including social networks [Facebook](https://facebook.com), [Twitter](https://twitter.com), and [Instagram](https://instagram.com), and popular news sites, such as [BBC News Russian](http://www.bbc.com/russian), [Radio Free Europe/Radio Liberty](https://rferl.org), [Deutsche Welle](https://dw.com) are blocked in Russia, making it hard for Russian general public to reach independent information which does not conform to the government's official propaganda.

Therefore majority of Russians still don't know the truth and they believe that there is no war and the "special military operation" in Ukraine is supposed to protect them and Ukrainians against militant groups of neo-nazis formed in Ukraine.

If you want to learn more about internet content censorship in Russia, Wikipedia has a [list of websites blocked in Russia](https://en.wikipedia.org/wiki/List_of_websites_blocked_in_Russia) with some useful links to other sources. Cogitatio has even a [detailed article](https://www.cogitatiopress.com/mediaandcommunication/article/viewFile/816/816), published in 2017, analyzing the censorship methods and situation in Russia in great detail.


### Possible ways to end the war

Various analysts have published their opinions on how the war could end. Even though being among the least likely ones to happen, probably the most peaceful option is commonly agreed to be reasonable Russian citizens stopping their president and his yes-men, taking over the control of Russia.

The main force preventing this is the Russian censorship and propaganda, and one thing that can help is letting as many Russians as possible access independent information sources to make their own opinions.


### How this open-source script can help

In order to bypass the Russian government's censorship and blocks, we have created this small PHP script, which you can easily add to your website. If you do so, any time someone accesses your website from Russia or Belarus, a special page will appear instead of your site, with basic static information about the war and a fresh feed of the most recent balanced news about Ukraine.

There are other similar initiatives already available, such as [web4ukraine.org](https://web4ukraine.org), which simply shows an Ukrainian flag and an appeal to stop the war. However, most of these solutions rely on loading an external JavaScript from a single domain, which is easy to block for the Russian government, just like they block the news sites. They also don't provide much information to the visitors, so someone already biased by the government's propaganda may actually consider the appeal to be fake.

Main benefit of our script is that if you use it, the whole page will be loaded solely from your server, so the Russian government will have to block your website specifically to prevent their citizens from accessing it. It will be loading the fresh news from its source in the backend, so unless your server is physically hosted in Russia, they will have no way to intercept that feed.


### How to install it

Installing this script to your website is very easy, if your website runs on PHP. If you're not using PHP then you may want to get an experienced web developer help you with the installation.

Just download the helpukraine.php file, put it in the root folder of your web server. Then add the following line to the beginning (right after the first line beginning with `<?php`) of your index.php or whatever PHP file you're using to deliver your homepage:

`include("helpukraine.php");`

That's it. Nothing else needed. The special web page will appear in all the following situations:

* your website is accessed from an IP address known to be located in Russia or Belarus
* the query parameter `?helpukraine=1` is added to the URL accessing your website
* the helpukraine.php script is accessed directly, such as by linking to `https://yourdomain.com/helpukraine.php`

There is also a mechanism to prevent it from appearing, simply by adding `?helpukraine=0` to the URL. There is also a link using this approach in the end of the page.


### How to test it

Thanks to the True North PHP Hackathon in November of 2014, an online tool called [ShotSherpa](https://shotsherpa.com/) exists, which lets you check what your website looks like from different places around the world. It selects 8 random cities out of 200+ supported by default, but you can select your own (up to 8), including a few cities in Russia. Try it out at [https://shotsherpa.com/](https://shotsherpa.com/).


### Note the IP geolocation limit

Note that the free API used by default to identify the client's country by IP has a limit of 45 calls per minute. If your website receives more traffict than that 45 visits per minute, then you might want to consider replacing the default API call in the `helpukraine_get_ip_country` function with another one that does not have such a low limit.


### Feel free to make or suggest changes

This is our attempt to help to stop the war. If you want to do more than just install it, if you have ideas how to make it better, feel free to suggest changes or submit pull requests with code changes you make.


### Thanks to AllSides.com, Perigon and DeepL for support

We are happy to thank two external service providers for supporting this initiative.

[AllSides.com](https://www.allsides.com/) is an U.S. based news websites "exposing people to information and ideas from all sides of the political spectrum so they can better understand the world — and each other. Their balanced news coverage, media bias ratings, civil dialogue opportunities, and technology platform are available for everyone and can be integrated by schools, nonprofits, media companies, and more." AllSides kindly created a JSON news feed focused on the war in Ukraine for us to use in our script as a data source.

[Perigon](https://goperigon.com/) is a smart news API providing up-to-the-minute news & events data from over 40,000 sources across the web, structured & enriched by AI. Perigon kindly provided free access to their API for the use by the HelpUkraine.php script.

[DeepL](https://www.deepl.com/) is a German AI-powered language translator capable of accurate translations that are often hard to differentiate from human-made translations. DeepL kindly provided us with a complimentary access to their translation API, so that we can deliver the balanced news from AllSides.com not only in English, but also in Russian.

___

## License

HelpUkraine.php is provided by [24U Software](https://24usoftware.com) ([24U s.r.o.](https://24u.cz)) and licensed under the "GNU LGPLv3" License.

AllSides Media Bias Ratings:tm: by AllSides.com are licensed under a Creative Commons Attribution-NonCommercial 4.0 International License. These ratings may be used for research or noncommercial purposes with attribution.