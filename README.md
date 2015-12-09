# Check IP with DNSBLs
This is somewhat the same as StopForumSpam, but it checks DNSBLs instead.

### Configuration
Once you have uploaded the contents of the `Upload` folder, you can go ahead and enable the plugin.  
Once enabled, settings will be created for the plugin. Currently, there are three settings:  
* Enabled? => Check IP addresses on registration against enabled DNSBL(s)?  
* Allow Tor users? => Allow people to register connecting through Tor? For more information on Tor, visit [Tor Project](https://www.torproject.org/)
* DNSBL list => A list of the DNSBLs to check IP addresses against before completing registration (one per line)

### Adding DNSBLs
Add a new line to the `DNSBL list` setting with the DNSBL you would like to add and save. Now you need to edit the language file.  
Edit `/inc/languages/english/check_ip_with_dnsbl.lang.php` and add a variable respective to what you have added. *You need to replace `.` with `_` in the variable name.  
**Example variable if I were to add `foo.bar` as a DNSBL**  
```php
$l["foo_bar"] = "Your IP seems to be connected from a Tor server. Visit this link for more information: http://foo.bar/?ip={1}";
```



### Development thread on the MyBB community forum
http://community.mybb.com/thread-186968.html

License
----

[GNU v3](https://github.com/dequeues/MyBB-Register-Check-DNSBL/blob/master/LICENSE)