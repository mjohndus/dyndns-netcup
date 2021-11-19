# dyndns for netcup
simple script to update all ip4(A) (and/or) ip6(AAAA) for one domain hosted by netcup
**using Netcup DNS.API:**
**https://www.netcup-wiki.de/wiki/DNS_API**

#### Installation
Copy the file to your preferred folder.

At first start:
cip4.log (and/or) cip6.log files for saving current addresses are created in the same folder.

At start-up before login:
an ip check compares the current and the stored ip.

If nothing has changed, the script is terminated.
The script starts if ip changed or option --force is set.

#### usage:
./xxx.sh (optional) --force

| option | short description | example |
|:--------------:|:-------------:|:--------------:|
| --force | ignore ip check, starts the script | ./xxx.sh --force |
