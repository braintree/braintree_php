## 2.0.0

* Updated success? on transaction responses to return false on declined transactions
* Search results now include Enumerable and will automatically paginate data
* Added credit_card[cardholder_name] to allowed transaction params and CreditCardDetails (thanks chrismcc[http://github.com/chrismcc])
* Fixed a bug with Customer::all
* Added constants for error codes

## 1.2.1

* Added methods to get both shallow and deep errors from a Braintree_ValidationErrorCollection
* Added the ability to make a credit card the default card for a customer
* Added constants for transaction statuses
* Updated Quick Start in README.md to show a workflow with error checking

## 1.2.0

* Added subscription search
* Provide access to associated subscriptions from CreditCard
* Switched from using Zend framework for HTTP requests to using curl extension
* Fixed a bug in Transparent Redirect when arg_separator.output is configured as &amp; instead of &
* Increased http request timeout
* Fixed a bug where ForgedQueryString exception was being raised instead of DownForMaintenance
* Updated SSL CA files

## 1.1.1

* Added Braintree_Transaction::refund
* Added Braintree_Transaction::submitForSettlementNoValidate
* Fixed a bug in errors->onHtmlField when checking for errors on custom fields when there are none
* Added support for passing merchantAccountId for Transaction and Subscription

## 1.1.0

* Added recurring billing support

## 1.0.1

* Fixed bug with Braintree_Error_ErrorCollection.deepSize
* Added methods for accessing validation errors and params by html field name

## 1.0.0

* Initial release

