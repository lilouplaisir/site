[lilouplaisir.com](https://www.lilouplaisir.com) (Magento 2).

## How to deploy the static content 
```shell  
PHP=/opt/plesk/php/7.2/bin/php ;               
rm -rf pub/static/* ;
$PHP bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US fr_FR ;
$PHP bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Mgs/claue \
	-f en_US fr_FR ;
$PHP bin/magento cache:clean ;
```