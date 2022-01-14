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
| --info | shows DNS Zone nformations |

## Output
**Example outputs for IPv4:**

**1. Cronjob**
- 0,30 * * * * /your path to/dncapi.php  

     - Using in cronjob:  
       - --> if NO Error or Update  
       - --> debug and force not activated  
     - there is no Output  

**2. Output (--force, --debug)**

yyyy@xxx: ./dncapi.php --force --debug  

Option "--force" is Set: ignore's ipcheck and different ip(4/6) will be changed.  

Get IPv4 from first server ip: 177.198.122.123 is valid
IPv4 Address not changed but Option "--force" is Set. --> continue.  

IPv4 your choice: IPv4 = true.  
IPv6 your choice: IPv6 = false.  

Option --hosts = false.  
Your host declaration v4: 2 --> "xxxx", "yyyy"  
Your host declaration v6: all  

Login: success  

Your Login ID: cjc0M2MxOTI1NjhSM12345678912345672ZCUm1RSTU5NjFBOD
```
--> force
---------------------------------------------------------------------------------------------------
|     ID     |      HostName      |         DNS IP         |        Public IP        |   Status   |
---------------------------------------------------------------------------------------------------
| 44433344   | *                  |        177.198.122.123 |         177.198.122.123 |      equal |
| 44433355   | @                  |        177.198.122.123 |         177.198.122.123 |      equal |
| 44433366   | xxxx               |        199.198.199.123 |         177.198.122.123 |  different |
| 44433366   | xxxx               |                  updated successfully!                        |
| 43355566   | yyyy               |        177.198.122.123 |         177.198.122.123 |      equal |
---------------------------------------------------------------------------------------------------
<-- force
```

Logout: success  

**3. Output (--info)**
- --debug and --force are activated automatically  
  - --> but no changes are made  
  - --> only info  

yyyy@xxx: ./dncapi.php --info  

Option "--info" is Set: no changes will be made.  

Get IPv4 from first server ip: 177.198.122.123 is valid  
IPv4 Address not changed but Option "--info" is Set. --> continue.  

IPv4 your choice: IPv4 = true.  
IPv6 your choice: IPv6 = false.  

Option --hosts = false.  
Your host declaration v4: 2 --> "xxxx", "yyyy"  
Your host declaration v6: all  

Login: success  

Your Login ID: cjc0M2MxOTI1NjhSM12345678912345672ZCUm1RSTU5NjFBOD  
```
--> info: Domain Zone Info
--------------------------------------------------------------------------------
|       ID       |         Name          |  Type  |             IP             |
--------------------------------------------------------------------------------
| 44433344       | *                     |      A |            177.198.122.123 |
| 44433355       | @                     |      A |            177.198.122.123 |
| 44433366       | xxxx                  |      A |            177.198.122.123 |
| 43355566       | yyyy                  |      A |            177.198.122.123 |
--------------------------------------------------------------------------------
<-- info
```
Logout: success  
