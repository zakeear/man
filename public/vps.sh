#检查php Money 队列脚本是否启动
php_count=`ps -ef | grep Money | grep -v "grep" | wc -l`
if [ $php_count == 0 ];then
	echo '----php Money queue start'
	`sudo -H -u www bash -c 'nohup php /www/wwwroot/www.demo.com/think queue:listen --queue Money > /www/wwwroot/www.demo.com/logs/Money.txt 2>&1 &'`
else
	echo '----php Money queue ok'
fi

#检查php DestroyQueue 队列脚本是否启动
php_count=`ps -ef | grep Destroy | grep -v "grep" | wc -l`
if [ $php_count == 0 ];then
	echo '----php Destroy queue start'
	`sudo -H -u www bash -c 'nohup php /www/wwwroot/www.demo.com/think queue:listen --queue Destroy > /www/wwwroot/www.demo.com/logs/Destroy.txt 2>&1 &'`
else
	echo '----php Destroy queue ok'
fi