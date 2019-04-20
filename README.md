## About PHP Currency Converter

This library helps you convert currency using latest rates available at [https://www.ecb.europa.eu](https://www.ecb.europa.eu).
The library caches the XML available at below endpoints
- For latest rates - https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml
- For rates 90 days prior - https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml
- For Historical rates - https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml

## Usage
#### Loading latest rates
By default all the rates are loaded keeping `EUR` as the **base** currency.
```php
$cc = new CurrencyConverter;

$rates = $cc->load()->rates();

/* Output
Array
(
    [base] => EUR
    [data] => Array
        (
            [2019-04-18] => Array
                (
                    [EUR] => 1
                    [USD] => 1.125
                    [JPY] => 125.86
                    [BGN] => 1.9558
                    [CZK] => 25.682
                    [DKK] => 7.4663
                    [GBP] => 0.8647
                    [HUF] => 320.09
                    [PLN] => 4.2786
                    [RON] => 4.7618
                    [SEK] => 10.476
                    [CHF] => 1.1383
                    [ISK] => 135.6
                    [NOK] => 9.5978
                    [HRK] => 7.435
                    [RUB] => 71.9719
                    [TRY] => 6.5486
                    [AUD] => 1.5719
                    [BRL] => 4.4206
                    [CAD] => 1.5065
                    [CNY] => 7.5445
                    [HKD] => 8.8265
                    [IDR] => 15797.81
                    [ILS] => 4.0432
                    [INR] => 78.068
                    [KRW] => 1278.95
                    [MXN] => 21.2303
                    [MYR] => 4.6609
                    [NZD] => 1.6819
                    [PHP] => 58.147
                    [SGD] => 1.5251
                    [THB] => 35.792
                    [ZAR] => 15.8482
                )
        )
)
*/
```

You can change the `base` currency by passing the correct symbol during intialization.
```php
$cc = new CurrencyConverter('INR');

$rates = $cc->load()->rates();

/* Output
Array
(
    [base] => INR
    [data] => Array
        (
            [2019-04-18] => Array
                (
                    [EUR] => 0.012809345698622
                    [USD] => 0.014410513910949
                    [JPY] => 1.6121842496285
                    [BGN] => 0.025052518317364
                    [CZK] => 0.328969616232
                    [DKK] => 0.095638417789619
                    [GBP] => 0.011076241225598
                    [HUF] => 4.1001434646718
                    [PLN] => 0.054806066506123
                    [RON] => 0.060995542347697
                    [SEK] => 0.13419070553876
                    [CHF] => 0.014580878208741
                    [ISK] => 1.7369472767331
                    [NOK] => 0.12294153814623
                    [HRK] => 0.095237485269252
                    [RUB] => 0.92191294768663
                    [TRY] => 0.083883281241994
                    [AUD] => 0.020135010503663
                    [BRL] => 0.056624993595327
                    [CAD] => 0.019297279294974
                    [CNY] => 0.096640108623252
                    [HKD] => 0.11306168980888
                    [IDR] => 202.35960957114
                    [ILS] => 0.051790746528667
                    [INR] => 1
                    [KRW] => 16.382512681252
                    [MXN] => 0.27194625198545
                    [MYR] => 0.059703079366706
                    [NZD] => 0.021544038530512
                    [PHP] => 0.74482502433776
                    [SGD] => 0.019535533124968
                    [THB] => 0.45847210124507
                    [ZAR] => 0.2030050725009
                )
        )
)
*/
```
#### Load only specific currency symbols.
The `symbols` method lets you load only specific symbols instead of the complete collection.
```php
$cc = new CurrencyConverter;

$rates = $cc->symbols(['USD', 'JPY', 'SEK', 'INR'])->load()->rates();

/* Output
Array
(
    [base] => EUR
    [data] => Array
        (
            [2019-04-18] => Array
                (
                    [USD] => 1.125
                    [JPY] => 125.86
                    [SEK] => 10.476
                    [INR] => 78.068
                )
        )
)
*/
```

#### Load rates of specific Date or Date Range
The `load` method accepts `from` and `to` date parameters to load rates of particular Date or Date Range.
```php
$cc = new CurrencyConverter;

$rates = $cc->symbols(['USD', 'JPY', 'SEK', 'INR'])->load('2019-02-25')->rates();

/* Output
Array
(
    [base] => EUR
    [data] => Array
        (
            [2019-02-25] => Array
                (
                    [USD] => 1.1355
                    [JPY] => 125.75
                    [SEK] => 10.5793
                    [INR] => 80.5315
                )
        )
)
*/
```

To load rates between a Date Range pass 2 Date parameters in `load` method.
```php
$cc = new CurrencyConverter;

$rates = $cc->symbols(['USD', 'JPY', 'SEK', 'INR'])->load('2019-02-25', '2019-03-01')->rates();

/* Output
Array
(
    [base] => EUR
    [data] => Array
        (
            [2019-03-01] => Array
                (
                    [USD] => 1.1383
                    [JPY] => 127.35
                    [SEK] => 10.5003
                    [INR] => 80.695
                )

            [2019-02-28] => Array
                (
                    [USD] => 1.1416
                    [JPY] => 126.44
                    [SEK] => 10.4844
                    [INR] => 80.8915
                )

            [2019-02-27] => Array
                (
                    [USD] => 1.1386
                    [JPY] => 125.9
                    [SEK] => 10.5443
                    [INR] => 81.1585
                )

            [2019-02-26] => Array
                (
                    [USD] => 1.1361
                    [JPY] => 125.93
                    [SEK] => 10.5858
                    [INR] => 80.853
                )

            [2019-02-25] => Array
                (
                    [USD] => 1.1355
                    [JPY] => 125.75
                    [SEK] => 10.5793
                    [INR] => 80.5315
                )
        )
)
*/
```

If rates are **not available** for the given Date, the `data` will consist of an empty `array`.
```php
$cc = new CurrencyConverter;

$rates = $cc->load('2019-06-01')->rates();

/* Output
Array
(
    [base] => EUR
    [data] => Array
        (
            
        )
)
*/
```

#### Convert an amount to other currency
The `convert` method lets you convert an amount to the loaded currencies as per the rates of those dates.
```php
$cc = new CurrencyConverter;

$rates = $cc->symbols(['USD', 'INR', 'JPY'])->load('2019-03-04', '2019-03-06')->convert(400);

/* Output
Array
(
    [base] => EUR
    [base_amount] => 400
    [data] => Array
        (
            [2019-03-06] => Array
                (
                    [USD] => 452.2
                    [JPY] => 50560
                    [INR] => 31772.8
                )

            [2019-03-05] => Array
                (
                    [USD] => 453.16
                    [JPY] => 50720
                    [INR] => 31974.4
                )

            [2019-03-04] => Array
                (
                    [USD] => 453.48
                    [JPY] => 50764
                    [INR] => 32145.2
                )
        )
)
*/
```

## Note
The [https://www.ecb.europa.eu](https://www.ecb.europa.eu) does not always have rates of all dates. I've observed that rates are not updated on weekends and hence this needs to be taken care of if you intend to use this library.