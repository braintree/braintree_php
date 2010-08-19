## 2.5.0

* Added Braintree_AddOns/Braintree_Discounts
* Enhanced Braintree_Subscription search
* Enhanced Braintree_Transaction search
* Added constants for Braintree_Result_CreditCardVerification statuses
* Added EXPIRED and PENDING statuses to Braintree_Subscription
* Allowed prorateCharges to be specified on Braintree_Subscription update
* Added Braintree_AddOn/Braintree_Discount details to Braintree_Transactions that were created from a Braintree_Subscription
* Removed 13 digit Visa Sandbox Credit Card number and replaced it with a 16 digit Visa
* Added new fields to Braintree_Subscription:
  * billingDayOfMonth
  * daysPastDue
  * firstBillingDate
  * neverExpires
  * numberOfBillingCycles

## 2.4.0

* Added ability to specify country using countryName, countryCodeAlpha2, countryCodeAlpha3, or countryCodeNumeric (see [ISO_3166-1](http://en.wikipedia.org/wiki/ISO_3166-1))
* Added gatewayRejectionReason to Braintree_Transaction and Braintree_Verification
* Added unified message to result objects

## 2.3.0

* Added unified Braintree_TransparentRedirect url and confirm methods and deprecated old methods
* Added functions to Braintree_CreditCard to allow searching on expiring and expired credit cards
* Allow card verification against a specified merchant account
* Added ability to update a customer, credit card, and billing address in one request
* Allow updating the paymentMethodToken on a subscription

## 2.2.0

* Prevent race condition when pulling back collection results -- search results represent the state of the data at the time the query was run
* Rename ResourceCollection's approximate_size to maximum_size because items that no longer match the query will not be returned in the result set
* Correctly handle HTTP error 426 (Upgrade Required) -- the error code is returned when your client library version is no long compatible with the gateway
* Add the ability to specify merchant_account_id when verifying credit cards
* Add subscription_id to transactions created from subscriptions

## 2.1.0

* Added transaction advanced search
* Added ability to partially refund transactions
* Added ability to manually retry past-due subscriptions
* Added new transaction error codes
* Allow merchant account to be specified when creating transactions
* Allow creating a transaction with a vault customer and new payment method
* Allow existing billing address to be updated when updating credit card
* Correctly handle xml with nil=true

## 2.0.0

* Updated success? on transaction responses to return false on declined transactions
* Search results now include Enumerable and will automatically paginate data
* Added credit_card[cardholder_name] to allowed transaction params and CreditCardDetails (thanks [chrismcc](http://github.com/chrismcc))
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

