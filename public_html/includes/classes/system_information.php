<?php

class system_information {

	public static function drive_size() {
		return disk_total_space(mfcs::config("archivalPathMFCS"))/1073741824;
	}

	public static function free_space() {
		return disk_free_space(mfcs::config("archivalPathMFCS"))/1073741824;
	}


	public static function archives_usage() {
		return (self::drive_size() - self::free_space());
	}

}
