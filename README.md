# Tier Price Upload # 

# Install instructions #

`composer require dominicwatts/tierpricingupload`

`php bin/magento setup:upgrade`

# Usage instructions #

Managed within admin

Content > Csv >
  - Tier Pricing Import

Once import queue has been built tier pricing can be inserted a couple of ways

## Submit screen ##

![Submit](https://i.snag.gy/hKoifb.jpg)  

## Console commnad ## 

xigen:tierpricing:import <import>

`php bin/magento xigen:tierpricing:import import`