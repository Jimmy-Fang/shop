#!/bin/bash

cas_url=(##HINKDIY##)
i=0
sleeptime=300
jobtmot=1
sttmot=1
backup_vm_job(){
	url=$1
	#echo  start job $1
	result="$(curl -s $url)"
    echo $result
    if [[ "$result" =~ "success" ]];then
		return  0
	elif [[ "$result" =~ "Connection" ]];then
		if [ "$jobtmot" -eq 4 ];then
			return -1
		else
			sleep $sleeptime
			let jobtmot=$jobtmot+1
			backup_vm_job $url
		fi
	else
       	return 1
	fi
}

backup_vm_status(){
	url=$1
	#echo  start job $1
	result="$(curl -s $url)"
    echo $result
    if [[ "$result" =~ "result" ]];then
		return 0
	elif [[ "$result" =~ "Connection" ]];then
		if [ "$sttmot" -eq 4 ];then
			return -1
		else
			sleep $sleeptime
			let sttmot=$sttmot+1
			backup_vm_status $url
		fi
	else
       	return 1
	fi
}

backup_allvm(){
	backup_vm_job $1
	status=$?
	if [ "$status" -eq -1 ];then
		return 1;
	elif [ "$status" -eq 0 ];then
		backup_vm_status $2
        status=$?
		if [ "$status" -eq -1 ];then
			return 1;
		else
			timeout=1
			while [ $status -ne 0 ]
			do
				if [ "$status" -eq -1 ];then
					return 1
				elif [ "$status" -eq 0 ];then
					return 0
				elif [ $timeout -lt 144  ];then
					sleep $sleeptime
					backup_vm_status $2
					status=$?
					let timeout=$timeout+1
				else
					return 1
					break
				fi
			done
		fi
	else
		return 1
	fi
}

while [ $i -lt ${#cas_url[*]} ]
do
	sleep 2
	backup_allvm  ${cas_url[$i]}  ${cas_url[$i+1]}  ;
	if [ "$?" -eq 0 ];then
        echo backup vm successfully!
    else
        echo backup vm failed!
    fi
    let i=$i+2
done
exit 0
