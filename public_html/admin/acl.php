<?php

	if (!mfcsPerms::isAdmin(0)) {
		header('Location: /index.php?permissionFalse');
	}

?>
