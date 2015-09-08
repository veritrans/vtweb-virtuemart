Veritrans Joomla Virtuemart3 VT-WEB Payment Gateway Plugin
==========================================================

Veritrans :heart: Joomla & Virtuemart!

Let your Joomla Virtuemart store integrated with Veritrans VT-WEB payment gateway.

### Description
This is the official Veritrans extension for the Joomla Virtuemart E-commerce platform.

### Version
1.0 for Virtuemart v3.0.x & Joomla v2.5.x

### Requirements
The following plugin is tested under following environment:

* PHP v5.4.x or greater
* MySQL version 5.0 or greater
* Virtuemart v3.0.x 
* Joomla v2.5.x

#### Installation Process
The manual installation method involves downloading our feature-rich plugin and uploading it to your webserver via your favourite FTP application.

1. [Download](https://github.com/rizdaprasetya/vtweb-virtuemart3/archive/master.zip) the plugin file to your computer and unzip it, open the extracted folder, rename ``vtweb-virtuemart3-master`` folder to ``veritrans`` 
2. Using an FTP program, or your hosting control panel, upload the renamed folder to your Joomla modules installation's ``[Joomla folder]\plugins\vmpayment\`` directory.
3. Make sure the uploaded plugin folder structure looks like this:
```
[Joomla folder]
	|--plugins
	    |--vmpayment
	    	|--veritrans
	           |veritrans.php
	           |veritrans.xml
	           |readme.md
	           |language
	           |lib
	           |veritrans
```



#### Plugin Configuration

1. Open Joomla admin page, open menu **extensions > extensions manager**.
2. Click **Discover** under Extension Manager title, then click icon **Discover** on upper right menu
3. Tick **VM Payment - veritrans**, then click icon **Install**
4. Click **Manage**, input ``veritrans`` in form **filter** then click search. Tick **VM Payment - veritrans**, then click icon **Enable**
5. Go to menu **Components > VirtueMart > Payment Methods**
6. Click on icon **New** in upper right corner
7. Fill in the payment that will be shown to your customer (or fill it with ``Pay by Veritrans``), set Published to **yes**, Payment method **VM Payment - veritrans**. Click icon **Save**. Then click the **Configuration** tab.
8. On the Configuration tab, fill the configuration as described on screen.
9. Click save.
10. Now VT-Web should appear as a payment options when your customer checkout.

#### Veritrans Map Configuration

1. Go to **Settings > Configuration**
2. Insert ``http://[YourWebsite.com]/index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component`` as your Payment Notification URL in your MAP
3. Insert ``http://[your web]`` link as Finish/Unfinish/Error Redirect URL in your MAP configuration.

#### Get help

* [Veritrans sandbox login](https://my.sandbox.veritrans.co.id/)
* [Veritrans sandbox registration](https://my.sandbox.veritrans.co.id/register)
* [Veritrans registration](https://my.veritrans.co.id/register)
* [Veritrans documentation](http://docs.veritrans.co.id)
* Technical support [support@veritrans.co.id](mailto:support@veritrans.co.id)
