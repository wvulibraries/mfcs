<script type="text/javascript" src="{engine var="jquery"}"></script>
<script type="text/javascript" src="{engine var="jqueryDate"}"></script>
<script type="text/javascript" src="{engine var="selectBoxJS"}"></script>
<script type="text/javascript" src="{engine var="convert2TextJS"}"></script>
<script type="text/javascript" src="{engine var="engineListObjJS"}"></script>
<script type="text/javascript" src="{engine var="engineWYSIWYGJS"}"></script>
<script type="text/javascript" src="{engine var="tiny_mce_JS"}"></script>

<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-expose.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-validate.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-ui.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/functions.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/dynamic.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/validate.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="{local var="siteRoot"}includes/stylesheet.css" />

<link rel="stylesheet" type="text/css" media="screen" href="{local var="siteRoot"}includes/date_input.css" />
<script>
	$.extend(DateInput.DEFAULT_OPTS, {
		stringToDate: function(string) {
			var matches;
			if (matches = string.match(/^(\d{2,2})\/(\d{2,2})\/(\d{4,4})$/)) {
				return new Date(matches[3], matches[1] - 1, matches[2]);
			}
			else {
				return null;
			};
		},

		dateToString: function(date) {
			var month = (date.getMonth() + 1).toString();
			var dom = date.getDate().toString();
			if (month.length == 1) {
				month = "0" + month;
			}
			if (dom.length == 1) {
				dom = "0" + dom;
			}
			return month + "/" + dom + "/" + date.getFullYear();
		}
	});
</script>
