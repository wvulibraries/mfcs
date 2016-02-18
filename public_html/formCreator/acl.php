<?php

	if (!mfcsPerms::isFormCreator()) {
		header('Location: /index.php?permissionFalse');
	}

?>