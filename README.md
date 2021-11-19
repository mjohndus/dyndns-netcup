# dyndns for netcup
**simple script to update all IP4 (and/or) IP6 for ONE DOMAIN hosted by netcup**  
ONLY UPDATING. No adding, deleting, creating, changing names, ...  
Using Netcup DNS_API:  

**https://www.netcup-wiki.de/wiki/DNS_API**  

## Installation
**Copy the file to your preferred folder.**  

## Configuration
**Fill out your data:**
- $ncnr = '123456';
- $apikey = '1234567890asdfghjkl0987654321';
- $apipw = '1234567890asdfghjkl0987654321';
- $domain = 'xyz.de';

**Your choice:**  
- $ipv4 = true;  
- $ipv6 = false;  

**Your preferred Server:**  
- $url4 = 'https://ip4.irgendwas.ti';  
- $url6 = 'https://ip6.irgendwas.ti';  

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
|:--------------:|--------------:|
| --force | ignore ip check, starts the script |

## Output
**There is no output: If nothing to do and NO ERROR**  
**Output by --force or IP change:**  

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
