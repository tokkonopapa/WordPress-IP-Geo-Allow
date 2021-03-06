# WordPress-IP-Geo-Allow
Private access to WordPress admin panel, only from your internet connection!

WordPress Plugin Exension for [WordPress-IP-Geo-Block](https://github.com/tokkonopapa/WordPress-IP-Geo-Block) Plugin

Plugin download: https://github.com/ddur/WordPress-IP-Geo-Allow/releases/latest

Get free [Dynamic-DNS service](https://doc.pfsense.org/index.php/Dynamic_DNS) for your internet connection, or add Dynamic-DNS record to your domain if your DNS Name Service provider has support for it ([ie: NameCheap](https://www.namecheap.com/))

![ip-geo-allow-admin-screen](https://cloud.githubusercontent.com/assets/3501612/26226617/61b84152-3c2d-11e7-8a4b-3d7a919ee538.png)

* **Host Name (DNS) Whitelist:**
  
  When your Host accessing WordPress admin panel has DNS or [Dynamic-DNS](https://en.wikipedia.org/wiki/Dynamic_DNS) name.
  
  Automatic whitelist IP by resolving host name (Dynamic&Static DNS)

  Use case 1): 
  
  If your host has dynamic IP and [Dynamic-DNS](https://en.wikipedia.org/wiki/Dynamic_DNS), or your host has (classic) DNS name, then configure IP-Geo-Allow by entering your Host Name and, if appropriate, change IP-Geo-Block country code to XX or any country code that is unlikely to ever access WordPress admin area (like YU). XX is [private range](https://en.wikipedia.org/wiki/Private_network). See [Country codes](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Current_codes).
  
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
  Change IP-Geo-Block Allowed Country Code to XX or any other by your choice.

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

