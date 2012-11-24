### Symphony Cart Extension

- Version 1.0
- David Anderson 2012

The symphony cart extension allows the implementation of a shopping cart within symphony cms


##The Datasource

The XML Output to the page is as follows

	<symphony-cart>
		<item product="220" quantity="1" />
		<item product="224" quantity="3" />
	</symphony-cart>
			  
The datasource adds *$symphony-cart* page parameter which is an array of product ids.

This can be used with a conventional datasource to grab the products for working out a price etc