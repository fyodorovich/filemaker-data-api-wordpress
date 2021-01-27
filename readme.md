# WordPress FileMaker Data API integration 
A WordPress plugin to integrate with the FileMaker Data API to allow data to be pulled from FileMaker and displayed easily in your WordPress site.

At this early stage of development only 'pull' is possible. Over time this plugin will be expanded to allow for data to be updated in FileMaker as well.

The primary means of 'pulling' data is through the use of two WordPress shortcodes

## Modifications made for Adelphi Finance
For convenience and increased control we have added two shortcodes which control the display of a users contracts and the display of a contract.

## Installation
Download, copy to your plugins directory, enable, configure and you're ready to go.

For Adelphi sites, copy to the __mu_plugins directory__. The plugin will only be updated by us, and is essential for the operation of the Adelpi Client site.

## Dependencies
### WP User Login
**This is the only piece of non-obvious client information that is stored**
The user login corresponds with the Client ID and is used as a sanity check. If the records found by the UUID are not associated with the current userlogin they are not displayed.

### User data

#### User Login, First Name and Last Name

First and Last Name are stored to provide a sensible welcome.

The user login corresponds with the Client ID and is used as a sanity check. If the records found by the UUID are not associated with the current userlogin they are not displayed.
**This is the only piece of non-obvious client information that is stored**


#### User Metadata: UUID
To prevent accidental transfer of client information we are storing a UUID in user metadata. The field is user_meta::wpcf_uuid. This UUID is generated in the FileMaker and is the ID of the table "clientmetadata."

### Pages and Permalinks
#### Transaction Statement
The shortcode expects the permalinks to be ON and creates an permalink to a post/page with the slug "contract-statement."

## Shortcodes
The shortcodes listed below provide access to your FileMaker data.

### Adelphi Specific Codes

### [FM-DATA-USER-DETAIL]
Pull all contract records associated with the UUID of the logged in user. The contract records include the client name and address. A table of contracts is built including links which are based on permalinks. 

### [FM-DATA-CONTRACT-DETAIL]
Pull all transaction records associated with the contract of the logged in user. The contract is printable as a Contract Statement. A table of transactions is built.

### General Purpose Codes

### [FM-DATA-TABLE]
Pull all records from the specified layout and generate a table of records.

| Parameter | Description | Required
|---|---|---|
|layout|Specify the layout which data should be pulled from|true|
|fields|A list of the fields which should be included in the table. \| separated  (see example below) |true|
|labels|The labels to use for the column headers. If ommited then the field names (as above) are used instead. \| separated|false|
|types|The type of each field. Currently supported are <ul><li>Currency - displays a number in the selected currency (see the settings screen for locale selection)</li><li>Image, which can optionally be follwed by a hypehen and an integer value (e.g. Image-100) which will set the width to 100px (defaults to full size)</li><li>Thumbnail - as for image, however defaults to 50px</li><li>`null` - outputs the content of the field</li></ul>| false| 
|id-field|Which field on the layout acts as a primary key for the given layout|false|
|detail-url|If both this and the id-field are set then the content of the cells is converted to a link to the URL. You must provide the location in the URL which the value of id-field will be embded in using `*id*` e.g. `detail-url="/product/?id=*id*"` |false|
|query|The JSON encoded query to apply to the records selected in the form `'field': '{ operator } value'`.<br><br>It is simplest to use single quotes for the JSON object, which will be transposed prior to submission to FM. e.g. `query="{'Unit Price': '&lt;500', 'Availability': 'In stock'}"`.<br><br>Note that depending on the exact Wordpress editor you're using then less than, and greater than signs may be html encoded. Again, the parser will cope with that. Also be aware that you're performing an `AND` query if you specify more than one key / value pair.|false|

Example
```
 [FM-DATA-TABLE layout="Product Details" fields="Image|Part Number|Name|Unit Price|Category|Availability" types="Thumbnail-50|||Currency||" id-field="Part Number" detail-url="/product/?id=*id*" query="{'Unit Price': '&lt;500', 'Availability': 'In stock'}"]
```

### [FM-DATA-FIELD]
Display a single field value.

| Parameter | Description | Required
|---|---|---|
|layout|Specify the layout which data should be pulled from|true|
|id-field|The field which contains the UUID to locate the correct record|true|
|id|The ID to search for in the above field. Either a static value, or this can be a special value `URL-xxx` in which case the query parameter `xxx` will be used. In the detail-url example above this would correspond to `id`, so it would be `URL-id`|true|
|field|The field to display|true|
|type|The type of output - options are the same as for the 'types' above.|false|

Examples
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="URL-id" field="Image" type="Image-200"]
```
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="23456" field="Price" type="Currency"]
```
```
[FM-DATA-FIELD layout="Product Details" id-field="Part Number" id="URL-id" field="Stock Level"]
```

### TODO
<ul>
<li>More output types</li>
<li>Write data back to FM</li>
