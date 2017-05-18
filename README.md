# WordPress-IP-Geo-Allow
WordPress Plugin Exension for WordPress-IP-Geo-Block Plugin

Download: https://github.com/ddur/WordPress-IP-Geo-Allow/releases/download/v0.9.3/ip-geo-allow.0.9.3.zip

* **Host Name (DNS) Whitelist:**
  
  When your Host accessing WordPress admin panel has DNS or Dynamic-DNS name.
  
  Automatic whitelist IP by resolving host name (Dynamic&Static DNS)

  Use case 1): 
  
  If your whitelist host has changing IP (Dynamic DNS) 
  or you do not want to search/remember IP (classic DNS)
  then configure IP-Geo-Allow by entering your Host Name
  and, if appropriate, change IP-Geo-Block country code to ZZ.
  
  Use case 2): 
  
  Want to allow administrator access to host in another country 
  than by "IP Geo Block" allowed country(ies)?
    Then only configure IP-Geo-Allow by entering that Host Name (DNS)

* **Host Name (DNS) Caching:**
  
  DNS Host-to-IP resolved names are cached by this extension plugin.
  
  Maximum time to keep DNS cache [5-60 minutes].

* **Reverse-DNS Lookup allow-filter for Host or Network Name :**
  
  When your Host accessing WordPress admin panel has Reverse Host Name (set by your internet provider)
  
  Allows Network or Host by matching filter to Reverse-DNS Host Name

  Use case: 
  
  In a large country IP-GEO country-location may not be restricitive enough,
  Then use this filter to restrict allowed hosts to your internet provider.
  Change IP-Geo-Block Allowed Country Code to ZZ or any other by your choice.

  ie: 'my.work.pc.example.com' 
    Matches host with '*my.work.pc.example.com' reverse name
  
  ie: '.example.com' 
    Matches all hosts with reverse name ending with '.example.com'
    ie: www.example.com, user.example.com, my.work.pc.example.com etc.
  
  ie: '.adsl.isp.provider.example.net' 
    Match only clients from your adsl provider with reverse name
    ie: 12.43.55.66.adsl.isp.provider.example.net

  Attention: Reverse host name may be different than DNS host name for same host!

* **Reverse-DNS Lookup Caching:**
  
  Allowed Reverse lookup Hosts/IPs are cached by IP-Geo-Block.

* **Clean uninstallation:**
  
  Nothing is left in your database after uninstallation. 
  
  You can feel free to install and activate to make a trial 
  of this plugin's functionality.
