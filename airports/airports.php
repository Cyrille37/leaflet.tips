<?php

/**
 * 
 */
$action = getReqArg('action');

switch ($action) {
    case 'getAirportsInBox' :
        $swLat = getReqArg('swLat');
        $swLon = getReqArg('swLon');
        $neLat = getReqArg('neLat');
        $neLon = getReqArg('neLon');
        getAirportsInBox(floatval($swLat), floatval($swLon), floatval($neLat), floatval($neLon));
        break;
	default:
		getAirportsInBox();
}

/**
 *
 * @param float $swLat
 * @param float $swLng
 * @param float $neLat
 * @param float $neLng 
 */
function getAirportsInBox($swLat=0, $swLon=0, $neLat=0, $neLon=0) {

    //error_log('getAirportsInBox()');

	if( !is_numeric($swLat) || !is_numeric($swLon) || !is_numeric($neLat) ||!is_numeric($neLon)) {
		die('Coordinates must be valids numbers');
	}
	
    $dsn = 'sqlite:airports.sqlite';
    $table = 'AIRPORTS';
    try {
        $dbh = new PDO($dsn);
        if ($dbh === false) {
            print "DB Error!\n";
            die();
        }
        
        if( $swLat!=0 || $swLon!=0 || $neLat!=0 || $neLon!=0 )
        {
			$sql = 'SELECT * FROM ' . $table . ' WHERE geo_lat >= ' . floatval($swLat) . ' and geo_lon >= ' . floatval($swLon)
					. ' and geo_lat <= ' . floatval($neLat) . ' and geo_lon <= ' . floatval($neLon) . ' ';
		}
		else
		{
			$sql = 'SELECT * FROM ' . $table ;
		}

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
                . "\n"
                . '<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="cyrille37@gmail.com as a Leaflet demo">'
                . '<metadata>'
                . ' <copyright author="many people...">'
                . '  <license>http://creativecommons.org/licenses/by-sa/2.0/</license>'
                . ' </copyright>'
                . '</metadata>'
                . "\n";
        $sth = $dbh->query($sql);
        foreach ($sth as $row) {
            //error_log('getAirportsInBox() icao=' . $row['icao']);
            $xml.= '<wpt lat="' . $row['geo_lat'] . '" lon="' . $row['geo_lon'] . '">'
                    . ' <name>' . $row['name'] . '</name>'
                    . ' <desc>' . $row['icao'] . ', ' . $row['country'] . '</desc>'
                    . ' <sym>airport</sym>'
                    . '</wpt>'
                    . "\n";
        }
        $xml.='</gpx>'. "\n";

        header("Content-type: text/xml; charset=utf-8");
        print $xml;

    } catch (PDOException $e) {
        header("Content-type: text/plain; charset=utf-8");
        die("DB Error!: " . $e->getMessage() . "\n");
    }
}

function getReqArg($key, $default=null) {
    if (array_key_exists($key, $_REQUEST)) {
        return $_REQUEST[$key];
    }
    return $default;
}
