# dyndns for netcup
**Simple script to Update all or defined (with Option --hosts)  
IPv4 (and/or) IPv6 Addresses for one domain hosted by netcup**  

ONLY UPDATING. No adding, deleting, creating, changing names, ...  

Using the **[Netcup-DNS_API](https://www.netcup-wiki.de/wiki/DNS_API)**.  

## Installation
**Copy the file to your preferred folder.**  

## Configuration
**Fill out your data:**
- $ncid = '123456';
- $apikey = 'Your-apikey';
- $apipw = 'Your-apipassword';
- $domain = 'yourdomain.de';

**Your choice:**  
- $ipv4 = true;  
- $ipv6 = false;  

**Define your Hostnames:**
- $hostsv4 = [];
- $hostsv6 = [];

**Example:**
- $hostsv4 = ['*', '@', 'server', ...];

**Your preferred Server:**  
- $url4 = 'https://ip4.first.de';
- $url4b = 'https://ip4.second.de';
- $url6 = 'https://ip6.first.de';
- $url6b = 'https://ip6.second.de';

## Howto
**At first start:**  
- cip4.log (and/or) cip6.log files for saving current addresses are created in the same folder.  

**At start-up before login:**  
- an ip check compares the current and the stored ip.  

- If nothing has changed, the script is terminated.  
- The script starts if ip changed or option --force is set.  

**Use:** ./dncapi.php  

**Use with option:** ./dncapi.php --force  

| option | description |
|:------:|------------:|
| --force | ignore ip check, starts the script |
| --hosts | use your declared hostnames |
| --debug | some information |

## Output
**There is no output: If nothing to do and NO ERROR**  
**Output by --force, --debug or IP changed**  

IPv4 address for: \<hostname\> not changed.  
**or**  
IPv4 address for: \<hostname\> updated successfully!  

##### Example for IPv4:

yyyy@xxx: ./dncapi.php --force  
IPv4 Address changed or Option --force is enabled.  
Login: success  
IPv4 address for: * not changed.  
IPv4 address for: @ not changed.  
IPv4 address for: dynd not changed.  
IPv4 address for: xxx not changed.  
IPv4 address for: yyy not changed.  
IPv4 ..  
Logout: success  
