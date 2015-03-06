<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Merge Tags</title>
<style type="text/css" media="screen">
body {
	margin: 0; 
	padding: 5px 20px;
	color: #555;
	font-family: Helvetica, san-serif;
	font-size: 14px;
}
h1, h2, h3, h4, h5, h6 {
	font-weight: 100;
}
th {
	padding: 5px;
	text-align: left;
	background-color: #EEE;
	text-transform: uppercase;
}
td {
	padding: 5px;
	border: 1px solid #EEE;
}
</style>
</head>

<body>
<?php $tags = array_unique( unserialize( stripslashes( urldecode( $_GET['tags'] ) ) ) ); ?>
	<h1>Merge Tags</h1>
	<p>"alt" is short for "alternate text." For example, if you include the tag for first name and they have no first name, you can alternately include the word "friend"</p>
	<p>* For addresss, you may replace the last number of the tag with the number of the address you want to use. The first address "1" is shown below</p>
	<table>
		<thead>
			<th>Tag</th>
			<th>Code</th>
		</thead>
		<tbody>
	<?php
	foreach ( $tags as $key => $val ) {
		if ( false !== strpos( $key, 'address' ) ) {
			$alt = '*';
		} else {
			$alt = '';
		}
		echo '<tr><td width="37%">'.$val.$alt.'</td><td width="42%">[ppmt k="'.$key.'" alt=""]</td></tr>';
	}
	?>
		</tbody>
	</table>
</body>
</html>