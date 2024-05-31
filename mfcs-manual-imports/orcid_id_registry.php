<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2023-09-25
     * Description: Import ORCID ID Registry CSV into MFCS
     */

    session_save_path('/tmp');
    include "../public_html/header.php";

    // create new mfcs object
    function create($formID, $values) {
		// Get the current Form
		if (($form = forms::get($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
			return FALSE;
		}

        $item = objects::create($formID,$values,$form['metadata'],"0",null,null,"0");
        // var_dump($item);
        // die();
    }

    session_save_path('/tmp');

    include "../public_html/header.php";

    $csv_file = "RDMRegistryPilotData20230911.csv";
    $import_dir = "/home/mfcs.lib.wvu.edu/data/imports/";

    // test values
    $engine               = mfcs::$engine;
    $formID               = 154; 
    
    // Opening the file for reading...
    $fp = fopen($import_dir.$csv_file, 'r');

    // fix to remove BOM from UTF-8
    // we were having issues with the BOM being included in the first column
    // this is a fix to remove the BOM
    // https://stackoverflow.com/questions/22529854/php-undefined-index-even-if-it-exists
    $bom = pack('CCC', 0xef, 0xbb, 0xbf);

    if (0 !== strcmp(fread($fp, 3), $bom)) {
        fseek($handle, 0);
    }

    // Headrow
    $head = fgetcsv($fp, 4096, ',', '"');

    // Rows
    $count = 1;
    while($column = fgetcsv($fp, 4096, ',', '"'))
    {
        // combine headrow and row
        $values = array_combine($head, $column);

        // reformat the publication_date to be YYYY-MM-DD
        $values['publication_date'] = date("Y-m-d", strtotime($values['publication_date']));


        // cleanup first column was giving issues most likely due to BOM

        // get surname from array
        $surname = $values["surname"];

        // remove surname from array
        unset($values["surname"]);

        // add surname to array ... this cleans up the first column
        $values["surname"] = $surname;

        // die();

        // create record
        create($formID, $values);

        // increment count
        $count++;

        // to avoid deadlocks we needed a pause after every record
        // we tried after every 100 but we kept getting deadlocks.
        // So we added a pause after every record.
        // this wasn't a issue locally only on the server.
        // Tracy A. McCormick 2021-09-25

        sleep(1);
    }    

?>