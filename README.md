Coinotron API
=============
Simple page scrapper for [coinotron.com](http://coinotron.com). The idea is to transform the data displayed on the page into a manipulable set of data to be used in whatever you want.

You can use it to log your mining rewards or just integrate it in some custom panel you currently have.

It uses PHP and cURL.

Example
-------
There is already an `example.php` file included with the basic usage to get the data.

Here I will explain the basics.

When creating a `new Coinotron()` object, you can filter which coin data you will be getting by passing the coins currently supported by the pool.

This will return data only for LTC and FTC coins:
```php
$c = new Coinotron(array(
	Coinotron::COIN_LTC,
	Coinotron::COIN_FTC
));
```
Or you can just pass one value: (notice the missing `array`, it's optional if you pass just one coin name)
```php
$c = new Coinotron(Coinotron::COIN_TRC);
```
Or you can instantiate it just like this:
```php
$c = new Coinotron();
```
This way you will be getting the info about every coin supported.

Once you have your shiny new *Coinotron* object, just log in:
```php
$c->login('YOUR_USER', 'YOUR_PASSWORD');
```
Finally, to get your precious data, just use:
```php
$data = $c->getAccountData();
```
I suggest to wrap your code inside a `try` `catch` block to capture possible errors. Like site under maintenance, wrong log in, and anoying stuff. I guess you dont want to log wrong data!