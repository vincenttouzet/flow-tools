Flow-Tools
==========

[![Build Status](https://travis-ci.org/vincenttouzet/flow-tools.png?branch=master)](https://travis-ci.org/vincenttouzet/flow-tools)

The `flow-tools` is a lightweight CLI to export datas from and to different format.

The source data may be:
* a csv file
* an xml file
* an XmlExcel file

You can export into the following format:
* CSV
* XML
* XmlExcel
* JSON
* Xls

Usage
-----

To simply export a csv file into json:
```
php flow-tools convert my_file.csv my_export.json
```

You can use different options to customize the conversion. Just use the `help` command to show available options.

```
php flow-tools help convert
```

Compile
-------

You can compile into a phar file with the following command:
```
php flow-tools compile
```

Note: If you're running php with suhosin you must add phar to the `suhosin.executor.include.whitelist` configuration option.
```
suhosin.executor.include.whitelist = phar
```
