[lilouplaisir.com](https://www.lilouplaisir.com) (Magento 2).

## 1. How to deploy the static content 
### 1.1. On the production server 
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

### 1.2. On `lilouplaisir.mage2.pro` 
```shell  
PHP=php7.2 ;               
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