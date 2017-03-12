<?php

require_once "classes/mfcs.php";

require_once "classes/checks.php";
require_once "classes/checksum.php";
require_once "classes/duplicates.php";
require_once "classes/exporting.php";
require_once "classes/files.php";
require_once "classes/forms.php";
require_once "classes/list.php";
require_once "classes/locks.php";
require_once "classes/log.php";
require_once "classes/navigation.php";
require_once "classes/notification.php";
require_once "classes/objects.php";
require_once "classes/permissions.php";
require_once "classes/projects.php";
require_once "classes/revisions.php";
require_once "classes/search.php";
require_once "classes/stats.php";
require_once "classes/string_utils.php";
require_once "classes/system_information.php";
require_once "classes/users.php";
require_once "classes/FFMPEG.php";
require_once "classes/valid.php";
require_once "classes/videoStream.php";
require_once "classes/virus.php";

require_once "class.tesseract_ocr.php";

if (file_exists(__DIR__."/classes/cleanup_mapping.php")) require_once "classes/cleanup_mapping.php";

?>
