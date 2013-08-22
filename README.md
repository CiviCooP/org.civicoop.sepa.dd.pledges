# Generate SEPA Direct Debit payment files (Pain.008)

##License

This module is provided unde the Academic Free License v3.0 and is developed and maintained by [CiviCoop](http://www.civicoop.org)

## Functionality

Basicly this module provides functionality to generate SEPA Payment files (Pain.008) for SEPA Direct Debit transaction based on pledges.

This module provides the following functionality:

* Custom fields for IBAN/BIC, Acknowledge date of a Sepa Mandate
* Generation and e-mailing of the Sepa Mandate after a Pledge is created
* Generation of pain.008 payment files. The Pain.008 file could be send to the bank to debit the bankaccount of the debitor.

## Installation

You can install this module by placing the code into your local extension directory. Then enable the module this will create the custom fields and the database structure needed for this module.

## Configuration

After enabling you can configure your module by editing the the config.php file in the extension directory. In this file you set the Creditor ID, Creditor IBAN etc...

## Customization

The following customizations of templates is available:

* templates/mandate.tpl the mandate template used to generate the pdf mandate
* templates/mandate_mail_html.tpl the html mail which goes to the debitor with the mandate
* templates/mandate_mail_text.tpl the text email which goes to the debitor with the mandate (this one should be similair to the mandate_mail_html.tpl but without HTML markup

## Usage

After you have installed, configured and customized the module you can use the module by creating pledges. After that you can generate a pain.008 file by going to the menu Contributions --> SEPA Direct debit

The file created contains payments with a transactions which should be processed 5 working days after now. 