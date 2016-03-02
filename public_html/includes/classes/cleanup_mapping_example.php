<?php

class cleanup {

  private static $mappings = array(

                                    array("/&quot;/",                "\""),
                                    array("/&/",                     "&amp;"),
                                    array("/&amp;amp;/",             "&amp;"),
                                    array("/&amp;#/",                "&#"),
                                    array("/&amp;([a-zA-Z0-9]+?);/", "&$1;"),
                                    array("/&rsquo;/",               "'"),
                                    array("/&lsquo;/",               "'"),
                                    array("/&ldquo;/",               '"'),
                                    array("/&rdquo;/",               '"'),
                                    array("/&nbsp;/",                ' '),
                                    array("/&eacute;/",              "é"),
                                    array("/&ndash;/",               "-"),
                                    array("/&mdash;/",               "-"),
                                    array("/&cedil;/",               "ç"),
                                    array('/&auml;/',                "ä"),
                                    array("/&#39;/",                 "'"),
                                    array('/&nbsp;/',                " ")

                                  );

  public static function clean($string) {

    foreach (self::$mappings as $map) {
      $string = preg_replace($map[0],$map[1],$string);
    }

    return $string;
  }

}

?>
