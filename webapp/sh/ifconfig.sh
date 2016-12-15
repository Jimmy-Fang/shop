#!/bin/bash
#-------------------- ./network.sh 192.168.1.2 255.255.255.0 192.168.1.1 192.168.1.1
#如果出现设置不成功跳成DHCP的情况，请做如下操作。问题的原因是RedHat自己开发的NetworkManager管理工具和/etc/sysconfig/network-scripts/ifcfg-ethx配置不同步造成的。如果要消除这个提示，请关闭NetworkManager服务即可：
#不能调用请给脚本权限
#chkconfig NetworkManager off  --永久关闭服务，需要重启
#service NetworkManager stop   --立即关闭服务，不需要重启

ip=$1
netmask=$2
gateway=$3
dns=$4


network=/etc/sysconfig/network  
resolv=/etc/resolv.conf  
IP_PATH=/etc/sysconfig/network-scripts/ifcfg-eth0 
GW_PATH=/etc/sysconfig/network

#change ipaddress
echo "DEVICE=eth0">$IP_PATH
echo "BOOTPROTO=static">>$IP_PATH 
echo "IPADDR=$1">>$IP_PATH
echo "NETMASK=$2">>$IP_PATH
echo "ONBOOT=yes">>$IP_PATH
echo "DNS1=$4">>$IP_PATH
echo "GATEWAY=$3">>$IP_PATH

GW_PRI=`grep -i HOSTNAME $GW_PATH|awk -F "=" '{printf $2}'`

echo -ne "NETWORKING=yes
HOSTNAME=$GW_PRI
GATEWAY=$3
" > $network

echo  "nameserver $dns" > $resolv

/etc/init.d/network restart