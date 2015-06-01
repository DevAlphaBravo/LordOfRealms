<?php
/* **********************************************************************/
/* XMLDB    - Flat Text Based Database                                  */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2005-2006 by Alessandro Vernassa                       */
/* http://speleoalex.altervista.org                                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/* **********************************************************************/
//-----PARSER XML -----


// TODO:
// LA PRIMARYKEY DEVE ESSERE SEMPRE IL PRIMO CAMPO DEL DESCRITTORE


@ini_set("memory_limit","256M");

define("_MAXTENTATIVIDIACCESSO","1000");

function xml2array($data, $elem, $fields)
{
    //eliminazione dei commenti
    $data = removexmlcomments($data);

    //visualizza solo determinati campi
    if ( is_array($fields) )
    {
        $fields = implode("|",$fields);
    }

    $out = "";
    $ret = null;
    //---l' elemento radice, dovrebbe avere il nome del database ma non fa verifica
    preg_match_all('/<' . $elem . '>.*?<\/' . $elem . '>/s',$data,$out);
    if ( is_array($out[0]) )
        foreach ( $out[0] as $innerxml )
        {
            //----------metodo 0 ------------------------
            for ( $oi = 0;$oi < 1;$oi++  )
            {
                $tmp2 = $t1 = null;
                preg_match_all('/<(' . $fields . '[^\/]*?)>([^<]*)<\/\1>/s',$innerxml,$t1);
                foreach ( $t1[1] as $k=>$tt )
                {
                    if ( $t1[2][$k] != null )
                        $tmp2[$tt] = xmldec($t1[2][$k]);
                    else
                        $tmp2[$tt] = "";
                }
            }
            if ( $tmp2 != null )
            {
                $ret[] = ($tmp2);
            }
        }
    return $ret;
}

/**
 * readDatabase
 * legge un file xml e restituisce un array
 * <db>
 *   <elem>
 * 	   <pippo>1</pippo>
 * 	   <pluto>1</pluto>
 * 	 </elem>
 *   <elem>
 * 	   <pippo>2</pippo>
 * 	   <pluto>2</pluto>
 * 	 </elem>
 * </db>
 *
 * readDatabase($filename,"elem")
 * ritorna:
 *
 * $ret[0]['pippo']=1
 * $ret[0]['pluto']=1
 * $ret[1]['pippo']=2
 * $ret[1]['pluto']=2
 *
 * @todo Da risolvere il problema che avviene
 * nel caso un campo abbia lo steso nome della tebella !!!!
 *
 *
 **/
function readDatabase($filename, $elem, $fields = false, $usecache = true)
{
    //print_r($filename);
    if (  !file_exists($filename) )
        return null;

    if ( is_array($fields) )
    {
        $fields = implode("|",$fields);
    }
    $_fields = "_" . $fields;
    static $cache = array();
    if ( $usecache === false )
    {
        if ( isset($cache[$filename][$_fields][$elem]) )
        {
            unset($cache[$filename][$_fields][$elem]);
        }
    }
    $filename = realpath($filename);
    if ( $usecache === true && isset($cache[$filename][$_fields][$elem]) )
    {
        //if ($_fields!="_")
        //	echo "<br >cache $filename, fields:$_fields";
        //dprint_r($cache);
        //if ( $_fields!="_")
        //	echo "<br >cache $filename, fields:$_fields";
        return $cache[$filename][$_fields][$elem];
    }

    $tmp = array();

    // --- gestione xml in più files --------->
    if ( is_dir($filename) )
    {
        $data = null;
        $handle = opendir($filename);
        while (false !== ($file = readdir($handle)))
        {
            $tmp2 = null;
            if ( preg_match('/.php$/is',$file) )
                $tmp2 = readDatabase("$filename/$file",$elem,$fields,$usecache);
            if ( $tmp2 != null )
                foreach ( $tmp2 as $t )
                    $tmp[] = $t;
        }
        closedir($handle);
        $cache[$filename][$_fields][$elem] = $tmp;
        return $tmp;
    }
    //<--------- gestione xml in più files ---


    if ( true )
    {
        //tenta di accedere al file
        for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
        {
            $data = file_get_contents($filename);
            if ( "" != $data ) // funziona ma sarebbe da verificare la chiusura di </database>
                break;
        }
    }
    else
    {
        //su winsows non va ....
        //tenta di accedere al file
        $sem = xmldblockfile($filename);
        $data = file_get_contents($filename);
        xmldbunlockfile($sem);
    }
    //da xml ad array....
    $ret = xml2array($data,$elem,$fields);
    //echo "fname=$filename";
    $cache[$filename][$_fields][$elem] = $ret;
    return $ret;
}

/**
 * xmlenc
 *
 * codifica i dati per inserirli tra i tag xml
 * @param string $str
 * @return stringa codificata
 */

function xmlenc($str, $trans = "")
{
    //return htmlentities ( $str, ENT_QUOTES, "ISO-8859-1" );
    $str = str_replace("&","&amp;",$str);
    $str = str_replace("<","&lt;",$str);
    $str = str_replace(">","&gt;",$str);
    return $str;

}
//die("&#21326;&#20154;&#27665;&#20849;&#21644;&#22269;");
/**
 * xmldec
 *
 * decodifica i dati inseriti tra i tag xml
 * @param string $str
 * @return stringa codificata
 */
function xmldec($str)
{
    if (  !is_string($str) )
        return "";

    return html_entity_decode($str,ENT_QUOTES,"ISO-8859-1");

    /*
        $str = str_replace ( "&gt;", ">", $str );
        $str = str_replace ( "&lt;", "<", $str );
        $str = str_replace ( "&amp;", "&", $str );
        return $str;
    */

}

/**
 * Classe per la gestione dei files xml per avere funzioni
 * simili a quelle di un database.
 * I dati sono salvati in files xml con estensione .php
 * <?exit(0);?> all' inizio del file permette che questo non venga
 * visualizzato da un accesso diretto.
 * Il sistema è composto da un file che descrive la tabella e di uno
 * o più files che contengono i dati.
 *
 * ESEMPIO :
 * -----------FILE DI DESCRIZIONE-----
 *
 * /misc/plugins/stati.php
 *
 * <?php exit(0);?>
 * <tables>
 *	<field>
 *		<name>unirecid</name>
 *		<type>string</type>
 *	</field>
 *	<field>
 *		<name>Codice</name>
 *		<type>string</type>
 *	</field>
 *	<field>
 *		<name>Nazione</name>
 *		<type>string</type>
 *	</field>
 *	<field>
 *		<name>CodiceISO</name>
 *		<type>string</type>
 *	</field>
 *  <driver>xmlphp</driver>
 * </tables>
 *
 * I dati vengono salvati a seconda del driver utilizzato.
 * il driver di default e' xmlphp
 *
 * --------------FILE DEI DATI xmlphp--------
 * /misc/plugins/stati/stati.php
 * <plugins>
 *  <!-- Tabella stati -->
 *   <stati>
 *       <unirecid>MOAS200312191548500468000002</unirecid>
 *        <Codice>I</Codice>
 *       <Nazione>ITALIA</Nazione>
 *        <en>ITALY</en>
 *        <it>ITALIA</it>
 *        <iva>0</iva>
 *    </stati>
 *    <stati>
 *        <unirecid>CASH200410080948160634006779</unirecid>
 *        <Codice>D</Codice>
 *        <Nazione>GERMANY</Nazione>
 *        <CodiceISO>DE</CodiceISO>
 *    </stati>
 */
class XMLTable
{
    var $databasename;
    var $tablename;
    var $primarykey;
    var $mysql;
    var $filename;
    var $indexfield;
    function XMLTable($databasename, $tablename, $path = "misc")
    {
        $this->connection = false;
        $this->driverclass = false;
        $this->driver = "xmlphp";
        $this->tablename = $tablename;
        $this->databasename = $databasename;
        $this->fields = array();
        $this->path = $path;
        $this->numrecords =  -1;
        $this->numrecordscache = array();
        $this->usecachefile = 0;
        $this->xmlfieldname = $tablename;
        $this->xmltagroot = $this->databasename;
        $this->xmlfieldname = $this->tablename;

        $this->datafile = $this->path . "/" . $this->databasename . "/" . $this->tablename . "/";

        //if is xml
        if ( is_array($tablename) )
        {
            $this->xmldescriptor = $tablename['xml'];
            $this->xmlfieldname = $tablename['field'];
            //$this->tablename=$tablename['tablename'];
            $this->xmltagroot = $tablename['tagroot'];
            $this->datafile = $tablename['datafile'];
            $fields = xml2array($this->xmldescriptor,"field",false);
            foreach ( $fields as $field )
            {
                $xmlfield = new XMLField($fields,$field['name']);
                $this->fields[$field['name']] = $xmlfield;
            }
        }
        else
        {

            if (  !file_exists("$path/$databasename/$tablename.php") )
            {
                //die ("$path/$databasename/$tablename.php");
                return false;
            }
            if (  !file_exists("$path/$databasename/$tablename") )
            {
                if (  !is_writable("$path/$databasename/") )
                    return false;
                mkdir("$path/$databasename/$tablename");
            }

            //fix old escriptor--->
            $tmp = file_get_contents("$path/$databasename/$tablename.php");
            if ( false !== strpos($tmp,"multilinguage") )
            {
                if ( is_writable("$path/$databasename/$tablename.php") )
                {
                    $tmp = str_replace("multilinguage","multilanguage",$tmp);
                    $h = fopen("$path/$databasename/$tablename.php","w");
                    fwrite($h,$tmp);
                    fclose($h);
                }
            }
            $this->xmldescriptor = $tmp;
            //fix old escriptor---<


            $this->usecachefile = get_xml_single_element("usecachefile",$this->xmldescriptor);
            //$this->filename = get_xml_single_element("filename", file_get_contents("$path/$databasename/$tablename.php"));
            $this->indexfield = get_xml_single_element("indexfield",$this->xmldescriptor);
            if (  !file_exists("$path/$databasename/$tablename.php") )
                return false;
            $fields = readDatabase("$path/$databasename/$tablename.php","field");
            $this->primarykey = '';
            foreach ( $fields as $field )
            {
                $xmlfield = new XMLField("$path/$databasename/$tablename.php",$field['name']);
                $this->fields[$field['name']] = $xmlfield;
            }
        }

        // cerca la chiave primaria
        foreach ( $fields as $field )
            if ( isset($field['primarykey']) && $field['primarykey'] == "1" )
                $this->primarykey = $field['name'];

        //modalita' database---->
        $this->driver = get_xml_single_element("driver",$this->xmldescriptor);
        if ( $this->driver == "" )
        {
            $this->driver = "xmlphp";
        }
        $classname = "XMLTable_" . $this->driver;
        $this->driverclass = new $classname($this);
        if (  !is_object($this->driverclass) )
            die("vvv");
        //dprint_r ($this);
        //modalita' database----<
    }
    //-----metodi del driver---------------->
    function GetNumRecords($restr = null)
    {
        return $this->driverclass ? $this->driverclass->GetNumRecords($restr) : null;
    }
    function GetRecords($restr = false, $min = false, $length = false, $order = false, $reverse = false, $fields = false)
    {
        return $this->driverclass ? $this->driverclass->GetRecords($restr,$min,$length,$order,$reverse,$fields) : null;
    }
    function GetRecord($restr = false)
    {
        return $this->driverclass ? $this->driverclass->GetRecord($restr) : null;
    }
    function GetRecordByPrimaryKey($unirecid)
    {
        return $this->driverclass ? $this->driverclass->GetRecordByPrimaryKey($unirecid) : null;
    }
    function GetAutoincrement($field)
    {
        return $this->driverclass ? $this->driverclass->GetAutoincrement($field) : null;
    }
    function InsertRecord($values)
    {
        return $this->driverclass ? $this->driverclass->InsertRecord($values) : null;
    }
    function DelRecord($pkvalue)
    {
        return $this->driverclass ? $this->driverclass->DelRecord($pkvalue) : null;
    }
    function GetFileRecord($pkey, $pvalue)
    {
        return $this->driverclass ? $this->driverclass->GetFileRecord($pkey,$pvalue) : null;
    }
    function Truncate()
    {
        return $this->driverclass ? $this->driverclass->Truncate() : null;
    }
    function GetRecordByPk($pvalue)
    {
        return $this->driverclass ? $this->driverclass->GetRecordByPk($pvalue) : null;
    }
    function UpdateRecordBypk($values, $pkey, $pvalue)
    {
        return $this->driverclass ? $this->driverclass->UpdateRecordBypk($values,$pkey,$pvalue) : null;
    }
    function UpdateRecord($values)
    {
        return $this->driverclass ? $this->driverclass->UpdateRecord($values) : null;
    }

    function get_file($recordvalues, $recordkey)
    {
        return $this->driverclass ? $this->driverclass->get_file($recordvalues,$recordkey) : null;
    }
    function get_thumb($recordvalues, $recordkey)
    {
        return $this->driverclass ? $this->driverclass->get_thumb($recordvalues,$recordkey) : null;
    }
    //-----metodi del driver----------------<


    /**
     * gestfiles
     * Gestione ei files ricevuti per post
     * @param array $values
     */

    function gestfiles($values, $oldvalues = null)
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = realpath($this->path);
        //dprint_r($_FILES);
        $newvalues = $values;
        //----gestione campi d tipo FILES o IMAGE
        $unirecid = $newvalues[$this->primarykey];
        foreach ( $newvalues as $key=>$value )
        {
            $type = isset($this->fields[$key]) ? $this->fields[$key] : null;
            if ( isset($type->type) && ($type->type == 'file' || $type->type == 'image') )
            {
                //dprint_r($values);
                //cancello i vecchi record se esiste il nuovo
                if ( isset($_FILES[$key]['tmp_name']) && $_FILES[$key]['tmp_name'] != "" && $oldvalues != null && isset($values[$key]) ) // se è un aggiornamento
                {
                    $oldfileimage = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $key . "/" . $oldvalues[$key] : "";
                    $oldfilethumb = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $key . "/thumbs/" . $oldvalues[$key] . ".jpg" : "";
                    if ( $oldvalues[$key] != "" && file_exists($oldfilethumb) )
                    {
                        unlink($oldfilethumb);
                    }
                    if ( $oldvalues[$key] != "" && file_exists($oldfileimage) )
                    {
                        unlink($oldfileimage);

                        if (  !file_exists("$path/$databasename/$tablename/$unirecid/$key") && $unirecid != "" && $key != "" && $databasename != "" )
                        {
                            //remove_dir_rec("$path/$databasename/$tablename/$unirecid/$key/");
                        }
                    }
                }
                // cancellazione di un record
                if ( isset($_POST["__isnull__$key"]) && $_POST["__isnull__$key"] == "null" )
                {
                    $oldfileimage = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $key . "/" . $oldvalues[$key] : "";
                    $oldfilethumb = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $key . "/thumbs/" . $oldvalues[$key] . ".jpg" : "";
                    if ( $oldvalues[$key] != "" && file_exists($oldfilethumb) )
                    {
                        unlink($oldfilethumb);
                        rmdir(dirname($oldfilethumb));
                    }
                    if ( $oldvalues[$key] != "" && file_exists($oldfileimage) )
                    {
                        unlink($oldfileimage);
                        rmdir(dirname($oldfileimage));
                    }
                }

                if ( isset($_FILES[$key]['tmp_name']) && $_FILES[$key]['tmp_name'] != "" )
                {
                    if ( preg_match('/.php/is',$_FILES["$key"]['name']) || preg_match('/.php3/is',$_FILES["$key"]['name']) || preg_match('/.php4/is',$_FILES["$key"]['name']) || preg_match('/.php5/is',$_FILES["$key"]['name']) || preg_match('/.phtml/is',$_FILES["$key"]['name']) )
                    {
                        touch("$path/$databasename/$tablename/$unirecid/$key/" . $_FILES["$key"]['name']);
                    }
                    else
                    {
                        if (  !file_exists("$path/$databasename/$tablename/$unirecid") )
                            mkdir("$path/$databasename/$tablename/$unirecid");
                        if (  !file_exists("$path/$databasename/$tablename/$unirecid/$key") )
                            mkdir("$path/$databasename/$tablename/$unirecid/$key");
                        if (  !move_uploaded_file(realpath($_FILES[$key]['tmp_name']),"$path/$databasename/$tablename/$unirecid/$key/" . $_FILES["$key"]['name']) )
                            copy(realpath($_FILES[$key]['tmp_name']),"$path/$databasename/$tablename/$unirecid/$key/" . $_FILES["$key"]['name']);
                        //dprint_r($path);
                        //dprint_r(realpath($path));
                        //dprint_r(realpath("$path/$databasename/$tablename/$unirecid/$key/"));


                        $create_thumb[$key] = true;
                    }
                }
            }
        }

        //---------------- creazione anteprime per le immagini ----------------------
        foreach ( $this->fields as $field )
        {
            switch ($field->type)
            {
                case "image" :
                    if ( isset($values[$field->name]) && $values[$field->name] != "" ) // se il cmpo è stato aggiornato
                    {
                        $fileimage = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $field->name . "/" . $values[$field->name] : "";
                        $filethumb = isset($values[$this->primarykey]) ? "$path/$databasename/$tablename/" . $values[$this->primarykey] . "/" . $field->name . "/thumbs/" . $values[$field->name] . ".jpg" : false;

                        if ( file_exists($fileimage) && (isset($create_thumb[$key]) || ($filethumb &&  !file_exists($filethumb))) )
                        {
                            $size = isset($field->thumbsize) ? $field->thumbsize : 22;
                            $size_w = isset($field->thumbsize_w) ? $field->thumbsize_w : "";
                            $size_h = isset($field->thumbsize_h) ? $field->thumbsize_h : "";
                            if ( $size < 16 )
                                $size = 16;
                            xmldb_create_thumb($fileimage,$size,$size_h,$size_w);

                        }
                    }
                    break;
            }
        }

    }
}

/**
 * driver xmlphp per Xmltable
 *
 */
class XMLTable_xmlphp
{
    var $databasename;
    var $tablename;
    var $primarykey;
    var $filename;
    var $indexfield;
    var $fields;
    function XMLTable_xmlphp(&$xmltable)
    {
        $this->xmltable = &$xmltable;
        $this->tablename = &$xmltable->tablename;
        $this->databasename = &$xmltable->databasename;
        $this->fields = &$xmltable->fields;
        $this->path = &$xmltable->path;
        $this->numrecords = &$xmltable->numrecords;
        $this->usecachefile = &$xmltable->usecachefile;
        $this->filename = &$xmltable->filename;
        $this->indexfield = &$xmltable->indexfield;
        $this->primarykey = &$xmltable->primarykey;
        $this->driver = &$xmltable->driver;
        $this->xmldescriptor = &$xmltable->xmldescriptor;
        $this->xmlfieldname = &$xmltable->xmlfieldname;
        $this->datafile = &$xmltable->datafile;
        $this->xmltagroot = &$xmltable->xmltagroot;
        //propriera' relative a i file xml
        $path = $this->path;
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        // dati su singolo file
        $this->filename = get_xml_single_element("filename",$this->xmldescriptor);
        return true;
    }
    /**
     * GetNumRecords
     * Torna il numero di records
     */
    function GetNumRecords($restr = null)
    {
        $cacheid = $restr;
        if ( is_array($restr) )
            $cacheid = implode("|",$restr);
        if ( $restr == null )
            $cacheid = " ";
        $cacheid = md5($cacheid);
        if ( isset($this->numrecordscache[$cacheid]) )
        {
            return $this->numrecordscache[$cacheid];
        }
        $c = count($this->GetRecords($restr,false,false,false,false,$this->primarykey));
        $this->numrecordscache[$cacheid] = $c;
        //dprint_r($this->numrecordscache);
        if ( $restr == null )
            $this->numrecords = $c;
        return $c;
    }

    function ClearCachefile()
    {
        if ( $this->usecachefile != 1 )
            return;

        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $files = glob($cachefile = "$path/" . $databasename . "/cache/$tablename*");
        if ( is_array($files) )
            foreach ( $files as $file )
            {
                @unlink($file);
            }
    }
    /**
     * GetRecords
     * recupera tutti i records
     */
    function GetRecords($restr = false, $min = false, $length = false, $order = false, $reverse = false, $fields = false)
    {

        $ret = null;
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $fieldname = $this->xmlfieldname;
        if ( is_array($fields) )
        {
            $fields = implode("|",$fields);
        }

        $tmf = "";
        if ( $fields != false && is_array($restr) )
        {
            foreach ( $restr as $key=>$value )
                $fields .= "|$key";
        }
        $rc = $restr;
        if ( is_array($restr) )
            $rc = implode("|",$restr);

        //cache su file---->
        if ( $this->usecachefile == 1 )
        {
            $cacheindex = $rc . $min . $length . $order . $reverse . $fields;
            if (  !file_exists("$path/" . $databasename . "/cache") )
                mkdir("$path/" . $databasename . "/cache");
            $cachefile = "$path/" . $databasename . "/cache/" . $tablename . "." . md5($cacheindex) . ".cache";
            if ( file_exists($cachefile) )
            {
                $ret = file_get_contents($cachefile);
                $ret = @unserialize($ret);
                //dprint_r("[$cachefile]");
                //dprint_r ($ret);
                if ( $ret !== false )
                    return $ret;
            }
        }
        //cache su file----<


        // filtro i field che non sono associati alla tabella
        if ( $fields === false )
        {
            $fields = array();
            foreach ( $this->fields as $v )
            {
                $fields[] = $v->name;
            }
            $fields = implode("|",$fields);
        }

        $all = readDatabase($this->datafile,$fieldname,$fields,false);
        //echo "readDatabase(\"$path/\" . $databasename . \"/\" . $tablename,$tablename,$fields,false);";


        if (  !is_array($all) )
            return null;
        //se il campo manca lo forzo a default oppure null


        foreach ( $all as $k=>$r )
        {
            foreach ( $this->fields as $field )
            {

                if (  !isset($r[$field->name]) )
                    $r[$field->name] = isset($this->fields[$field->name]->defaultvalue) ? $this->fields[$field->name]->defaultvalue : null;
            }
            $all[$k] = $r;
        }
        if ( is_array($restr) )
        {

            $ret = array();
            foreach ( $all as $r )
            {
                $ok = true;
                foreach ( $restr as $key=>$value )
                {
                    if (  !isset($r[$key]) || $r[$key] != $restr[$key] )
                    {
                        $ok = false;
                        break;
                    }
                }

                if ( $ok == true )
                {
                    $ret[] = $r;
                }
            }
        }
        else
            $ret = $all;

        //ordinamento dei records
        if ( $order !== false && $order !== "" && isset($this->fields[$order]) && is_array($ret) )
        {
            $newret = array();
            foreach ( $ret as $key=>$value )
            {
                if ( isset($value[$order]) )
                {
                    $i = 0;
                    $r = $value[$order] . "0";
                    while (isset($newret[$r . $i]))
                    {
                        $i++ ;
                    }
                    $newret["$r" . "$i"] = $ret[$key];
                }
                else
                {
                    $i = 0;
                    $r = "";
                    while (isset($newret[$r . $i]))
                    {
                        $i++ ;
                    }
                    $newret["$r" . "$i"] = $ret[$key];
                }
            }
            ksort($newret);
            $ret = $newret;
        }

        if ( $reverse )
        {
            $ret = array_reverse($ret);
        }
        // minimo e massimo
        if ( $min != false && $length != false )
            $ret = array_slice($ret,$min - 1,$length);
        $ret = array_values($ret);

        //cache su file---->
        if ( $this->usecachefile == 1 )
        {
            $cachestring = serialize($ret);
            //dprint_r($cachefile);
            //dprint_r($cacheindex);
            //dprint_r($cachestring);
            $fp = fopen($cachefile,"wb");
            fwrite($fp,$cachestring);
            fclose($fp);
        }
        //cache su file----<


        return $ret;
    }

    /**
     * GetRecord
     * recupera un singolo record
     *
     * @param array restrizione
     */
    function GetRecord($restr = false)
    {
        $rec = $this->GetRecords($restr,0,1);
        if ( is_array($rec) && isset($rec[0]) )
        {
            return $rec[0];
        }
        return null;
    }

    /**
     * GetRecordByUnirecid
     *
     * Torna un record in formato array partendo dall' unirecid (nomefile)
     **/
    function GetRecordByPrimaryKey($unirecid)
    {
        return $this->GetRecordByPk($unirecid);
    }

    /**
     * GetAutoincrement
     *
     * gestisce l' autoincrement di un campo della tabella
     *
     * @param string nome del campo
     * @return indice disponibile
     */
    function GetAutoincrement($field)
    {
        if ( isset($this->maxautoincrement[$field]) )
            return $this->maxautoincrement[$field] + 1;
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $records = $this->GetRecords();
        $max = 0;
        $contamax = 0;
        foreach ( $records as $rec )
        {
            $contamax++ ;
            if ( isset($rec[$field]) && $rec[$field] > $max )
                $max = $rec[$field];

        }
        $this->numrecords = $contamax;
        return $max + 1;
    }
    /**
     * InsertRecord
     * Aggiunge un record
     *
     * @param array $values
     **/
    function InsertRecord($values)
    {
        //dprint_r($values);
        //die();
        $this->numrecords =  -1;
        $this->numrecordscache = array();
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;

        foreach ( $this->fields as $f )
        {
            //dprint_r($this->fields[$f->name]);
            if (  !isset($values[$f->name]) || (isset($values[$f->name]) && $values[$f->name] == "") )
                if ( isset($this->fields[$f->name]->extra) && $this->fields[$f->name]->extra == "autoincrement" )
                {
                    $newid = $this->GetAutoincrement($f->name);
                    $values[$f->name] = $newid;
                    $this->maxautoincrement[$f->name] = $newid;
                }
            if ( ( !isset($values[$f->name]) || $values[$f->name] === null) && (isset($this->fields[$f->name]->defaultvalue) && $this->fields[$f->name]->defaultvalue != "") )
            {

                $dv = $this->fields[$f->name]->defaultvalue;
                $fname = $f->name;
                $rv = "";
                eval("\$rv=\"$dv\";");
                $rv = str_replace("\\","\\\\",$rv);
                $rv = str_replace("'","\\'",$rv);
                eval("\$values" . "['$fname'] = '$rv' ;");
                //$values[$f->name] = $this->fields[$f->name]->defaultvalue;
            }

        }

        if (  !isset($values[$this->primarykey]) || $values[$this->primarykey] == "" )
        {
            return "manca la chiave primaria nella tabella $tablename";
        }

        // cerco il file da modificare o creare----->
        if (  !preg_match("/\\/$/si",$this->datafile) ) //datafile
            $xmltowritefullpath = $this->datafile;
        else
        {
            $unirecid = urlencode($values[$this->primarykey]);
            $xmltowritefullpath = "{$this->datafile}" . $unirecid . ".php"; //default
            if ( $this->filename != "" )
            {
                $xmltowritefullpath = "{$this->datafile}" . $this->filename . ".php"; //filename
            }
            if ( $this->indexfield != "" && isset($values[$this->indexfield]) )
            {
                $xmltowritefullpath = "{$this->datafile}" . $values[$this->indexfield] . ".php"; //indexfield
            }
        }
        // cerco il file da modificare o creare-----<


        // se esiste gia'
        if ( file_exists($xmltowritefullpath) )
        {
            $readok = false;
            for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
            {
                $oldfilestring = file_get_contents($xmltowritefullpath);
                if ( strpos($oldfilestring,"</{$this->xmltagroot}") !== false )
                {
                    $readok = true;
                    break;
                }
            }
            if (  !$readok )
            {
                return "error insert";
            }
            $str = "\t<{$this->xmlfieldname}>";
            foreach ( $this->fields as $field )
            {
                $valtowrite = isset($values[$field->name]) ? $values[$field->name] : "";
                $valtowrite = xmlenc("$valtowrite");
                $str .= "\n\t\t<" . $field->name . ">" . $valtowrite . "</" . $field->name . ">";
            }
            $str .= "\n\t</{$this->xmlfieldname}>\n</{$this->xmltagroot}>";
            $newfilestring = preg_replace('/<\/' . $this->xmltagroot . '>$/s',encode_preg_replace2nd($str),trim(($oldfilestring)));

            //	dprint_xml($oldfilestring);
            //	dprint_xml($newfilestring);
            //	die();
            if ( file_exists("$xmltowritefullpath") &&  !is_writable("$xmltowritefullpath") )
                return false;
            //dprint_xml($newfilestring);
            //die();


            $handle = fopen($xmltowritefullpath,"w");
            fwrite($handle,$newfilestring);
            fclose($handle);
        }
        else
        {
            //$values[$this->primarykey]=$unirecid;
            $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?php exit(0);?>
			<{$this->xmltagroot}>\n\t<{$this->xmlfieldname}>";
            foreach ( $this->fields as $field )
            {
                $valtowrite = isset($values[$field->name]) ? $values[$field->name] : "";
                $valtowrite = xmlenc("$valtowrite");
                $str .= "\n\t\t<" . $field->name . ">" . $valtowrite . "</" . $field->name . ">";
            }
            $str .= "\n\t</{$this->xmlfieldname}>\n</{$this->xmltagroot}>";
            if ( file_exists("$xmltowritefullpath") &&  !is_writable("$xmltowritefullpath") )
                return false;
            if (  !file_exists(dirname("$xmltowritefullpath")) )
                mkdir(dirname("$xmltowritefullpath"));
            $handle = fopen($xmltowritefullpath,"w");
            //dprint_r($values);
            //die();
            fwrite($handle,$str);
            fclose($handle);
        }
        $this->xmltable->gestfiles($values);
        //$this->gestfiles($values);
        $this->ClearCachefile();

        readDatabase($xmltowritefullpath,$this->xmlfieldname,false,false);
        return $values;
    }
    /**
     * elimina tutti i dati da una tabella
     *
     */
    function Truncate()
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $this->numrecords =  -1;
        $this->numrecordscache = array();
        remove_dir_rec("$path/$databasename/$tablename");
        $this->ClearCachefile();
        return true;
    }

    /**
     * DelRecord
     * Elimina un record.
     * @param string $unirecid
     *    <b>$values[$this->primarykey] deve essere presente</b>
     * @return array record appena inserito o null
     **/
    function DelRecord($pkvalue)
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $this->numrecords =  -1;
        $this->numrecordscache = array();
        $oldfile = $this->GetFileRecord($this->primarykey,$pkvalue);
        $dirold = dirname($oldfile) . "/" . basename($oldfile,".php");
        if (  !file_exists($oldfile) )
            return false;

        if ( preg_match("/\\/$/si",$this->datafile) )
            if (  !strpos($pkvalue,"..") !== false && file_exists("{$this->datafile}$pkvalue/") && is_dir("{$this->datafile}$pkvalue/") )
                remove_dir_rec("{$this->datafile}$pkvalue");

        $this->ClearCachefile();
        $n = readDatabase($oldfile,$this->xmlfieldname,false,false);

        if ( count($n) == 1 ) // se è l' ultimo record
        {
            if ( preg_match("/\\/$/si",$this->datafile) )
            {
                @ unlink($oldfile);
                if ( file_exists($oldfile) && is_dir($oldfile) )
                {
                    remove_dir_rec($oldfile);
                }
            }
            readDatabase($oldfile,$this->xmlfieldname,false,false);
            return true;
        }

        $pkey = $this->primarykey;
        $pvalue = $pkvalue;
        $readok = false;
        for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
        {
            if (  !file_exists($oldfile) ) //errore
                break;
            $oldfilestring = file_get_contents("$oldfile");
            if ( strpos($oldfilestring,"</{$this->xmltagroot}>") !== false )
            {
                $readok = true;
                break;
            }
        }
        if (  !$readok )
        {
            return false;
        }

        $oldfilestring = removexmlcomments($oldfilestring);
        $strnew = "";
        $newfilestring = preg_replace('/<' . $this->xmlfieldname . '>([^(' . $this->xmlfieldname . ')]*)<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>(.*?)<\/' . $this->xmlfieldname . '>/s',$strnew,$oldfilestring);
        $newfilestring = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?php exit(0);?>\n" . trim($newfilestring,"\n ");
        //dprint_xml($oldfilestring);
        //dprint_xml($newfilestring);
        //die();
        $file = fopen($oldfile,"w");
        fwrite($file,$newfilestring);
        fclose($file);
        $this->ClearCachefile();
        readDatabase($oldfile,$this->xmlfieldname,false,false);
        return true;
    }

    /**
     * GetFileRecord
     * torna il nome del file che contiene il record
     * @param string $pkey
     * @param string $pvalue
     */
    function GetFileRecord($pkey, $pvalue)
    {
        //if (isset($this->cache_filerecord[$pvalue]))
        //	return $this->cache_filerecord[$pvalue];
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        if (  !preg_match("/\\/$/si",$this->datafile) )
        {
            //echo "f=".$this->datafile;
            return $this->datafile;
        }
        //guardo prima quelo con la chiave primaria
        if ( file_exists($this->datafile . "/" . urlencode($pvalue) . ".php") )
        {
            $data = file_get_contents($this->datafile . "/" . urlencode($pvalue) . ".php");
            $data = removexmlcomments($data);
            //dprint_xml($data);
            if ( preg_match('/<' . $tablename . '>(.*)<' . $pkey . '>' . xmlenc(encode_preg($pvalue)) . '<\/' . $pkey . '>/s',$data) )
            {
                $this->cache_filerecord[$pvalue] = $this->datafile . "/" . urlencode($pvalue) . ".php";
                return $this->datafile . "/" . urlencode($pvalue) . ".php";
            }
        }
        //cerco in tutti i files
        $pvalue = xmlenc($pvalue);
        $pvalue = encode_preg($pvalue);
        //dprint_r($pvalue);
        if (  !file_exists($this->datafile) )
            return false;
        $handle = opendir($this->datafile);
        while (false !== ($file = readdir($handle)))
        {
            $tmp2 = null;
            if ( preg_match('/.php$/s',$file) and  !is_dir($this->datafile . "/$file") )
            {
                $data = file_get_contents($this->datafile . "/$file");
                $data = removexmlcomments($data);
                //dprint_r(strlen($data));
                //if (preg_match('/<' . $tablename . '>(.*)<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>/s', $data))
                if ( preg_match('/<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>/s',$data) )
                {
                    $this->cache_filerecord[$pvalue] = $this->datafile . "/$file";
                    return $this->datafile . "/$file";
                }
            }
        }
        return false;
    }
    /**
     * GetRecordByPk
     * torna il record passandogli la chiave primaria
     * @param string $pvalue valore chiave
     */
    function GetRecordByPk($pvalue)
    {
        $pkey = $this->primarykey;
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        //cache su file---->
        if ( $this->usecachefile == 1 )
        {
            $cacheindex = $pvalue;
            if (  !file_exists("$path/" . $databasename . "/cache") )
                mkdir("$path/" . $databasename . "/cache");
            $cachefile = "$path/" . $databasename . "/cache/" . $tablename . "." . urlencode($pvalue) . ".cache";
            if ( file_exists($cachefile) )
            {
                $ret = file_get_contents($cachefile);
                $ret = @unserialize($ret);
                //dprint_r("[$cachefile]");
                //dprint_r ($ret);
                if ( $ret !== false )
                    return $ret;
            }
        }
        //cache su file----<


        $old = $this->GetFileRecord($pkey,$pvalue);
        $values = readDatabase($old,$this->xmlfieldname);
        //dprint_r($old);
        $ret = null;
        $found = false;
        if (  !is_array($values) )
            return null;
        foreach ( $values as $value )
        {
            if ( $value[$pkey] == ($pvalue) )
            {
                $found = true;
                $ret = $value;
                break;
            }
        }
        //riempo i campi che mancano
        if ( $found )
            foreach ( $this->fields as $field )
            {
                if (  !isset($ret[$field->name]) )
                    $ret[$field->name] = isset($field->defaultvalue) ? $field->defaultvalue : null;
            }
        //dprint_r($ret);


        //cache su file---->
        if ( $this->usecachefile == 1 )
        {
            $cachestring = serialize($ret);
            $fp = fopen($cachefile,"wb");
            fwrite($fp,$cachestring);
            fclose($fp);
        }
        //cache su file----<


        return $ret;
    }

    /**
     * UpdateRecordBypk
     * aggiorna il record passandogli la chiave primaria
     * @param array $values
     * @param string $pkey
     * @param string $pvalue
     */
    function UpdateRecordBypk($values, $pkey, $pvalue)
    {

        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        $strnew = "";
        {
            $old = $this->GetFileRecord($pkey,$pvalue);

            if (  !file_exists($old) )
                return false;

            //$oldfilestring = file_get_contents($old);
            $readok = false;
            for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
            {
                $oldfilestring = file_get_contents($old);
                //die ($databasename);
                //if ( strpos($oldfilestring,"</$databasename>") !== false )
                if ( strpos($oldfilestring,"</") !== false )
                {
                    $readok = true;
                    break;
                }
            }
            if (  !$readok )
            {
                return "error update";
            }
            $oldfilestring = removexmlcomments($oldfilestring);
            $oldvalues = $newvalues = $this->GetRecordByPk($pvalue);
            foreach ( $values as $key=>$value )
            {
                $newvalues[$key] = $value;
            }

            $this->xmltable->gestfiles($values,$oldvalues);

            //compongo il nuovo xml per il record da aggiornare
            $strnew = "<{$this->xmlfieldname}>";
            foreach ( $newvalues as $key=>$value )
            {
                $strnew .= "\n\t\t<$key>" . xmlenc("$value") . "</$key>";
            }
            $strnew .= "\n\t</{$this->xmlfieldname}>";
            $strnew = encode_preg_replace2nd($strnew);
            //$strnew = str_replace ( '$', '\\$', $strnew );
            //$oldfilestring = encode_preg_replace2nd ( $oldfilestring );
            $pvalue = xmlenc($pvalue);
            $pvalue = encode_preg($pvalue);

            $newfilestring = preg_replace('/<' . $this->xmlfieldname . '>([^(' . $this->xmlfieldname . ')]*)<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>(.*?)<\/' . $this->xmlfieldname . '>/s',$strnew,$oldfilestring);
            $newfilestring = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?php exit(0);?>\n" . $newfilestring;
            if (  !is_writable($old) )
            {
                echo ("$old is readonly,I can't update");
                return ("$old is readonly,I can't update");
            }

            //dprint_xml('/<' . $tablename . '>([^(' . $tablename . ')]*)<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>(.*?)<\/' . $tablename . '>/s');
            //dprint_xml("strnew=".$strnew);
            //dprint_xml("oldfilestring=".$oldfilestring);
            //dprint_xml("newfilestring=".$newfilestring);
            //die();
            //dprint_xml("newfilestring=".$newfilestring);


            $handle = fopen($old,"w");
            fwrite($handle,$newfilestring);
            $this->ClearCachefile();
            $newvalues = readDatabase($old,$this->xmlfieldname,false,false); //aggiorna la cache
            $newvalues = $this->GetRecordByPk($pvalue);
            //dprint_r($values);
            //			echo "values=";
            //			dprint_r($newvalues);
            //die();


            if (  !isset($newvalues[$pkey]) )
                return false;
            //echo "<br>";
            //dprint_xml($oldfilestring);
            //dprint_xml($newfilestring);
            //die();
            return $newvalues;
        }
    }

    /**
     * UpdateRecord
     * Aggiorna un record.
     * @param array $values
     * $values[$this->primarykey] deve essere presente
     * @return array record appena inserito o null
     **/
    function UpdateRecord($values)
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = $this->path;
        //dprint_r($_FILES);
        if (  !isset($values[$this->primarykey]) )
            return false;
        $unirecid = $values[$this->primarykey];
        return $this->UpdateRecordBypk($values,$this->primarykey,$values[$this->primarykey]);
    }
    /**
     * get_file
     * ricava l' url del file
     *
     */
    function get_file($recordvalues, $recordkey)
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = realpath($this->path);
        $ret = "";
        $unirecid = $recordvalues[$this->primarykey];
        //dprint_r($recordvalues);


        if (  !isset($recordvalues[$recordkey]) )
            $recordvalues = $this->GetRecord($recordvalues);

        $value = $recordvalues[$recordkey];

        //echo "$path/$databasename/$tablename/$unirecid/$recordkey/$value";


        if ( file_exists("$path/$databasename/$tablename/$unirecid/$recordkey/$value") )
        {
            $php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : "";
            $dirname = dirname($php_self);
            if ( $dirname == "/" || $dirname == "\\" )
                $dirname = "";
            $protocol = "http://";
            if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" )
                $protocol = "https://";

            $siteurl = "$protocol" . $_SERVER['HTTP_HOST'] . $dirname;
            if ( substr($siteurl,strlen($siteurl) - 1,1) != "/" )
            {
                $siteurl = $siteurl . "/";
            }

            return "$siteurl" . $this->path . "/$databasename/$tablename/$unirecid/$recordkey/$value";
        }
        return false;
    }
    /**
     * get_thumb
     * ricava l' anteprima del file
     *
     */
    function get_thumb($recordvalues, $recordkey)
    {
        $databasename = $this->databasename;
        $tablename = $this->tablename;
        $path = realpath($this->path);
        $ret = "";
        $unirecid = $recordvalues[$this->primarykey];
        if (  !isset($recordvalues[$recordkey]) )
            $recordvalues = $this->GetRecord($recordvalues);
        $value = $recordvalues[$recordkey];
        if ( file_exists("$path/$databasename/$tablename/$unirecid/$recordkey/thumbs/$value.jpg") )
        {
            $php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : "";
            $dirname = dirname($php_self);
            if ( $dirname == "/" || $dirname == "\\" )
                $dirname = "";
            $protocol = "http://";
            if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" )
                $protocol = "https://";

            $siteurl = "$protocol" . $_SERVER['HTTP_HOST'] . $dirname;
            if ( substr($siteurl,strlen($siteurl) - 1,1) != "/" )
            {
                $siteurl = $siteurl . "/";
            }

            return "$siteurl" . $this->path . "/$databasename/$tablename/$unirecid/$recordkey/thumbs/$value.jpg";
        }
        return false;
    }

} //class XMLTable


//-----------------------------XMLField---------------------------------------------------------
/**
 * classe  XMLField
 * classe che descrive un singolo field della tabella
 **/
class XMLField
{
    var $title = null;
    //var $visible = null; //"onlyform","onlygrid","all" default = all
    //var $inputtype = null; //tipo di input default null
    var $readonly = null;
    //var $filter = false; //visualizza un filtro sul campo
    var $foreignkey = null; //foreignkey
    //var $fk_link_field = null; //campo linkato
    //var $fk_show_field = null; //campo da visualizzare
    var $_defaultvalue;
    var $type = null;
    function XMLField($descriptionfile, $fieldname)
    {
        //---proprieta' relative al database
        $this->type = "varchar";
        $this->name = "";
        $this->extra = "";
        $this->primarykey = "";
        $this->size = "";
        if (  !is_array($descriptionfile) )
            $obj = readDatabase($descriptionfile,"field");
        else
            $obj = $descriptionfile;
        $fields = null;

        foreach ( $obj as $ob )
        {
            if ( isset($ob['name']) && $ob['name'] == $fieldname )
            {
                $fields = $ob;
                break;
            }
        }
        if ( $fields != null )
            foreach ( $fields as $key=>$value )
            {
                $this->$key = $value;
            }
        if ( $this->title == null )
            $this->title = $this->name; // se è null prende il nome del campo
        if ( $this->type == "string" )
            $this->type = "varchar";
        if ( $this->type == "varchar" && $this->size == "" )
            $this->size = 255;

    }
} //class XMLField


//-- uso questa funzione per crearmi le anteprime per i campi di tipo immagine
// occorrono le librerie GD


/**
 *	xmldb_create_thumb
 *	Crea l' anteprima di un file
 *	@param string $filename nome del file
 *	@param int $max dimensione massima anteprima
 */
function xmldb_create_thumb($filename, $max, $max_h = "", $max_w = "")
{
    if ( $max_h == "" )
        $max_h = $max;
    if ( $max_w == "" )
        $max_w = $max;

    if (  !function_exists("getimagesize") )
    {
        echo "<br />" . _FNNOGDINSTALL;
        return;
    }
    $new_height = $new_width = 0;
    if (  !file_exists($filename) )
    {
        echo "non esiste";
        return;
    }
    if (  !getimagesize($filename) )
    {
        echo "$filename non è un immagine ";
        return;
    }

    list($width,$height,$type,$attr) = getimagesize($filename);
    $path = dirname($filename) . "/thumbs";

    $file_thumb = $path . "/" . basename($filename);

    if (  !file_exists($path) )
    {
        mkdir($path);
    }

    if (  !file_exists($path) )
    {
        echo "errore creazione dir";
        return false;
    }

    if (  !is_dir($path) )
    {
        echo "<br />$path non esiste";
    }
    $new_height = $height;
    $new_width = $width;
    if ( $width >= $max_w )
    {
        $new_width = $max_w;
        $new_height = intval($height * ($new_width / $width));
    }

    //se troppo alta
    if ( $new_height >= $max_h )
    {
        $new_height = $max_h;
        $new_width = intval($width * ($new_height / $height));
    }
    // se l' immagine e gia piccola
    if ( $width <= $max_w && $height <= $max_h )
    {
        $new_width = $width;
        $new_height = $height;
        //return;
    }

    //die("h=$new_height w=$new_width");
    // Load
    $thumb = imagecreatetruecolor($new_width,$new_height);
    $white = imagecolorallocate($thumb,255,255,255);
    $size = getimagesize($filename);
    switch ($size[2])
    {
        case 1 :
            $source = ImageCreateFromGif($filename);
            break;
        case 2 :
            $source = ImageCreateFromJpeg($filename);
            break;
        case 3 :
            $source = ImageCreateFromPng($filename);
            break;
        default :
            $tmb = null;
            $size[0] = $size[1] = 150;
            $source = ImageCreateTrueColor(150,150);
            $rosso = ImageColorAllocate($tmb,255,0,0);
            ImageString($tmb,5,10,10,"Not a valid",$rosso);
            ImageString($tmb,5,10,30,"GIF, JPEG or PNG",$rosso);
            ImageString($tmb,5,10,50,"image.",$rosso);
    }
    // Resize
    imagefilledrectangle($thumb,0,0,$width,$width,$white);
    imagecopyresampled($thumb,$source,0,0,0,0,$new_width,$new_height,$width,$height);

    // Output
    $file_to_open = $file_thumb;
    //forzo estensione jpg
    imagejpeg($thumb,$file_to_open . ".jpg");

}
/**
 * removexmlcomments
 * rimuove i commenti da un file xml
 *
 * @param string $data
 * @return string xml privo di commenti
 *
 */
function removexmlcomments($data)
{
    $data = preg_replace("/<!--(.*?)-->/ms","",$data);
    $data = preg_replace("/<\\?(.*?)\\?>/","",$data);
    return $data;
}
//-------------------------FUNZIONI DI CREAZIONE/MODIFICA DATABASE----------------
/**
 * createxmltable
 *
 * crea una nuova tabella xml
 * @param string nome database
 * @param string nome tabella
 * @param array campi
 * @param string path dei databases
 * @param misc $singlefilename se su un solo file specificarne il nome, se su database
 *             mettere la connessione di tipo array(host=>'' user=>'' password=>'')
 *
 *
 * -- ESEMPIO : --
 * $fields[0]['name']="unirecid";
 * $fields[0]['primarykey']=1;
 * $fields[0]['defaultvalue']=null;
 * $fields[0]['type']="varchar";
 * $fields[1]['name']="test";
 * $fields[1]['primarykey']=0;
 * $fields[1]['defaultvalue']="pippo";
 * $fields[1]['type']="varchar";
 * createxmltable("plugins","test",$fields,"misc");
 **/

function createxmltable($databasename, $tablename, $fields, $path = ".", $singlefilename = false)
{

    //----MYSQL---------------->
    if ( is_array($singlefilename) && isset($singlefilename['host']) && isset($singlefilename['user']) && isset($singlefilename['password']) )
    {
        if ( false !== ($conn = mysql_connect($singlefilename['host'],$singlefilename['user'],$singlefilename['password'])) )
        {

            $query = "CREATE TABLE `$tablename` (";
            $n = count($fields);
            foreach ( $fields as $field )
            {

                if (  !isset($field['type']) || $field['type'] == "string" )
                    $field['type'] = "varchar";
                $query .= "`" . $field['name'] . "`";
                $field['size'] = isset($field['size']) ? $field['size'] : "";
                switch ($field['type'])
                {
                    case "text" :
                    case "html" :
                        $query .= " TEXT";
                        break;
                    case "int" :
                        $query .= " INT";
                        break;
                    default : //forzo tutto a varchar
                        $query .= " VARCHAR";
                        $field['size'] = "255";
                        break;
                }
                if ( $field['size'] != "" )
                    $query .= "(" . $field['size'] . ")";
                $query .= " ";
                if ( isset($field['extra']) && $field['extra'] == "autoincrement" )
                    $query .= " AUTO_INCREMENT ";

                if ( isset($field['primarykey']) && $field['primarykey'] == "1" )
                {

                    $query .= "  PRIMARY KEY ";
                }
                if ( $n--  > 1 )
                    $query .= ",";
            }
            $query .= ")";
            //dprint_r($query);
            mysql_select_db($databasename);
            $res = mysql_query($query);
            print(mysql_error());
            mysql_close();
        }
        else
        {
            return (mysql_error());
        }
    }
    //<----MYSQL----------------


    if (  !file_exists("$path/$databasename") ||  !is_dir("$path/$databasename") )
        return "xml databse not exists";
    if ( file_exists("$path/$databasename/$tablename") && file_exists("$path/$databasename/$tablename.php") )
        return "xml table exists";
    if (  !is_writable("$path/$databasename/") )
        return "xml database not writable";
    //if (file_exists("$path/$databasename/$tablename") || file_exists("$path/$databasename/$tablename.php"))
    //	return "table alredy exists";


    $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?php exit(0);?>\n<tables>";
    foreach ( $fields as $field )
    {
        $str .= "\n\t<field>";
        foreach ( $field as $key=>$value )
        {
            $str .= "\n\t\t<$key>$value</$key>";
        }
        $str .= "\n\t</field>";
    }
    if ( $singlefilename != false )
    {
        if ( is_array($singlefilename) )
        {
            foreach ( $singlefilename as $key=>$values )
            {
                $str .= "\n\t<$key>" . xmlenc($values) . "</$key>";

            }
        }
        else
            $str .= "\n<filename>$singlefilename</filename>";
        //dprint_r($singlefilename);
    }
    $str .= "\n</tables>";
    if (  !file_exists("$path/$databasename/$tablename") )
        mkdir("$path/$databasename/$tablename");
    $file = fopen("$path/$databasename/$tablename.php","w");
    fwrite($file,$str);
    fclose($file);
    return false;
}

/**
 * createxmldatabase
 * crea un database
 *
 * @param string $databasename
 * @param string $path
 * @return false se il databare e'stato creato oppure una stringa che contiene l' errore
 */
function createxmldatabase($databasename, $path = ".", $mysql = false)
{
    //----MYSQL---------------->
    if ( is_array($mysql) )
    {
        if ( false !== ($conn = mysql_connect($mysql['host'],$mysql['user'],$mysql['password'])) )
        {
            $ret = mysql_query("CREATE DATABASE $databasename");
            mysql_close();
            return false;
        }
        else
        {
            return (mysql_error());
        }
    }
    //<----MYSQL----------------


    if ( file_exists("$path/$databasename") )
        return "database $databasename alredy exists";

    if (  !is_writable("$path/") )
        return "database not writable";

    mkdir("$path/$databasename");
    return false;
}

/**
 * xmldatabaseexists
 * verifica se un database esiste
 *
 * @param string $databasename
 * @param string $path
 */
function xmldatabaseexists($databasename, $path = ".", $conn = false)
{
    return (file_exists("$path/$databasename"));
}

function xmltableexists($databasename, $tablename, $path = ".")
{
    return (file_exists("$path/$databasename/$tablename") && file_exists("$path/$databasename/$tablename.php"));
}

/**
 * addfield
 * add field in table
 *
 * @param string $databasename
 * @param string $tablename
 * @param array $field
 * @param string $path
 * @param bool $force
 *
 */
function addxmltablefield($databasename, $tablename, $field, $path = ".", $force = true)
{

    if (  !isset($field['name']) )
        return null;

    $values = $field;
    $pvalue = $field['name'];
    $pkey = "name";
    $old = "$path/$databasename/$tablename.php";
    if (  !file_exists($old) )
        return null;

    $readok = false;
    for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
    {
        $oldfilestring = file_get_contents($old);
        if ( strpos($oldfilestring,"</tables>") !== false )
        {
            $readok = true;
            break;
        }
    }
    if (  !$readok )
    {
        die("error update");
    }
    $oldfilestring = removexmlcomments($oldfilestring);
    $oldvalues = $newvalues = getxmltablefield($databasename,$tablename,$field['name'],$path);
    foreach ( $values as $key=>$value )
    {
        $newvalues[$key] = $value;
    }
    //compongo il nuovo xml per il record da aggiornare
    $strnew = "<field>";
    foreach ( $newvalues as $key=>$value )
    {
        $strnew .= "\n\t\t<$key>" . xmlenc($value) . "</$key>";
    }
    $strnew .= "\n\t</field>";

    if ( $oldvalues )
    {
        $pvalue = xmlenc($pvalue);
        $pvalue = encode_preg($pvalue);
        $strnew = str_replace('$','\\$',$strnew);
        $newfilestring = preg_replace('/<field>([^(field)]*)<' . $pkey . '>' . $pvalue . '<\/' . $pkey . '>(.*?)<\/field>/s',$strnew,$oldfilestring);
        if (  !is_writable($old) )
        {
            echo ("$old is readonly,I can't update");
            return ("$old is readonly,I can't update");
        }
        ///		dprint_xml($oldfilestring);
        ///		dprint_xml($newfilestring);
        ///		die();
        if ( $oldfilestring != $newfilestring && $force )
        {
            $handle = fopen($old,"w");
            fwrite($handle,$newfilestring);
            readDatabase($old,'field',false,false); //aggiorna la cache
        }
        return $newvalues;
    }
    else
        // new field
    {
        for ( $i = 0;$i < _MAXTENTATIVIDIACCESSO;$i++  )
        {
            $oldfilestring = file_get_contents("$path/$databasename/$tablename.php");
            if ( strpos($oldfilestring,"</tables>") !== false )
            {
                $readok = true;
                break;
            }
        }
        if (  !$readok )
        {
            return "error insert field";
        }

        $strnew = encode_preg_replace2nd($strnew);
        $newfilestring = preg_replace('/<\/tables>$/s',encode_preg_replace2nd($strnew) . "\n</tables>",trim($oldfilestring)) . "\n";

        $handle = fopen("$path/$databasename/$tablename.php","w");
        fwrite($handle,$newfilestring);
        fclose($handle);
        readDatabase($old,'field',false,false); //aggiorna la cache
        return $newvalues;
    }
}

/**
 * getxmltablefield
 * ritorna tutte le proprieta' di un campo di una tabella xml
 *
 * @param string databasename
 * @param string tablename
 * @param string fieldname
 * @param string path
 */
function getxmltablefield($databasename, $tablename, $fieldname, $path = ".")
{
    if (  !file_exists("$path/$databasename/$tablename.php") )
        return false;
    $rows = readDatabase("$path/$databasename/$tablename.php","field");
    foreach ( $rows as $row )
    {
        if ( $row['name'] == $fieldname )
        {
            return $row;
        }
    }
    return false;
}

/**
 * Elimina ricorsivamente una cartella
 *
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @param $dirtodelete cartella da eliminare
 *
 **/
function remove_dir_rec($dirtodelete)
{
    if ( strpos($dirtodelete,"../") !== false )
        die(_NONPUOI);
    if ( false !== ($objs = glob($dirtodelete . "/*")) )
    {
        foreach ( $objs as $obj )
        {
            is_dir($obj) ? remove_dir_rec($obj) : unlink($obj);
        }
    }
    rmdir($dirtodelete);
}

function tooltip($text, $width = 200)
{

    if ( $width < 0 )
    {
        $width = strlen($text) * 10;
        if ( $width > 400 )
        {
            $width = 400;
        }
    }
    if ( $text != "" && $text != null )
    {
        $text = html_entity_decode($text,ENT_QUOTES);
        $text = str_replace("'","\\'",$text);
        $text = str_replace('"',"\\'",$text);
        $text = str_replace("\n",'<br />',$text);
        $text = str_replace("\r",'',$text);

        $ret = " onmouseover=\"this.T_SHADOWWIDTH=3;this.T_WIDTH=$width;this.T_PADDING=2;return escape('" . $text . "')\" ";
    }
    return $ret;
}

/**
 * encode_preg_replace2nd
 * prepara la stringa per il secondo parametro
 * dell' preg_replace aggiungendo la \ savanti a \ e $

 *
 */
function encode_preg_replace2nd($str)
{
    $str = str_replace("\\","\\\\",$str);
    $str = str_replace('$','\\$',$str);
    return $str;
}
/**
 * encode_preg_replace2nd
 * prepara la stringa per il primo parametro
 * dell' preg_replace aggiungendo
 * la barra davanti ai cratteri speciali
 *
 *
 */

function encode_preg($str)
{
    $str = str_replace('\\','\\\\',$str);
    $str = str_replace('/','\\/',$str);
    $str = str_replace('(','\\(',$str);
    $str = str_replace(')','\\)',$str);
    $str = str_replace('^','\\^',$str);
    $str = str_replace('$','\\$',$str);
    $str = str_replace('*','\\*',$str);
    $str = str_replace('+','\\+',$str);
    $str = str_replace('?','\\?',$str);
    $str = str_replace('[','\\[',$str);
    $str = str_replace(']','\\]',$str);
    return $str;

}
/**
 * Restituisce un elemento XML
 *
 * Restituisce un elemento XML da un file passato come parametro.
 *
 *
 * @param string $elem Nome dell'elemento XML da cercare
 * @param string $xml Nome del file XML da processare
 * @return string Stringa contenente il valore dell'elemento XML
 *
 */

function get_xml_single_element($elem, $xml)
{

    $xml = removexmlcomments($xml);
    $buff = preg_replace("/.*<" . $elem . ">/s","",$xml);
    if ( $buff == $xml )
        return "";
    $buff = preg_replace("/<\/" . $elem . ">.*/s","",$buff);
    return $buff;
}

function natksort($array)
{
    // Like ksort but uses natural sort instead
    $keys = array_keys($array);
    natsort($keys);

    foreach ( $keys as $k )
        $new_array[$k] = $array[$k];

    return $new_array;
}

function addsl($str)
{
    //return htmlspecialchars($str);
    return (str_replace('"','&quot;',$str));
}

/**
 * xml_to_sql
 * Trasforma una tabella xml in una tabella sql
 *
 */
function xml_to_sql($databasename, $tablename, $xmlpath, $connection, $dropold = false)
{
    // leggo i dati dalla tabella xml
    $TableXml = new XMLTable($databasename,$tablename,$xmlpath);
    $records = $TableXml->GetRecords();
    //dprint_r($connection);
    //die();
    if ( isset($TableXml->xmltable->connection) )
    {
        echo "this is alredy sql database";
        return;
    }
    //modifico le proprietà della tabella xml
    $oldfilestring = file_get_contents($xmlpath . "/$databasename/$tablename.php");
    $strnew = "\n\t<driver>mysql</driver>";
    $strnew .= "\n\t<host>" . $connection['host'] . "</host>";
    $strnew .= "\n\t<user>" . $connection['user'] . "</user>";
    $strnew .= "\n\t<password>" . $connection['password'] . "</password>";

    $newfilestring = preg_replace('/<\/tables>$/s',encode_preg_replace2nd($strnew) . "\n</tables>",trim(($oldfilestring))) . "\n";
    //die("<pre>".htmlspecialchars($newfilestring)."</pre>");
    $file = fopen($xmlpath . "/$databasename/$tablename.php","w");
    fwrite($file,$newfilestring);
    $TableSql = new XMLTable($databasename,$tablename,$xmlpath);

    $fields = array();
    foreach ( $TableXml->fields as $field )
    {
        $tmp = array();
        $tmp['name'] = $field->name;
        $tmp['type'] = $field->type;
        $tmp['size'] = $field->size;
        if ($tmp['type'] != "varchar" || $tmp['type'] != "text" || $tmp['type'] != "datetime" )
        {
            $tmp['type'] = "text";
            $tmp['size'] = "";
        }
        if ( isset($field->extra) )
            $tmp['extra'] = $field->extra;
        if ( isset($field->primarykey) && $field->primarykey == 1 )
            $tmp['primarykey'] = $field->primarykey;
        $fields[] = $tmp;
    }
    if ( $dropold )
    {
        if (  !$connessione = mysql_connect($connection['host'],$connection['user'],$connection['password']) )
            return (mysql_error());
        mysql_select_db($databasename);
        $err = mysql_query("DROP TABLE `$tablename`");
        mysql_close();
        echo (mysql_error());
    }
    // creo la tabella sql
    $err = createxmldatabase($databasename,$xmlpath,$connection);
    echo ("<br />create database:" . $err);
    $err = createxmltable($databasename,$tablename,$fields,$xmlpath,$connection);
    echo ("<br />create table:" . $err);
    foreach ( $records as $record )
    {
        //print_r($record);
        //inserisco i dati nella tabella sql
        $err = $TableSql->InsertRecord($record);
        if (  !is_array($err) )
            echo ("<br />$err");
        else
            dprint_r($err);
    }

}
function array_in_array($needle, $haystack)
{
    //Make sure $needle is an array for foreach
    if (  !is_array($haystack) )
        return false;
    if (  !is_array($needle) )
        $needle = array($needle);
    //For each value in $needle, return TRUE if in $haystack
    foreach ( $haystack as $line )
    {
        /*echo serialize($line);
        echo "<br>";
        echo serialize($needle);
        echo "<br>";		echo "<br>";		*/
        if (  !strcmp(serialize($line),serialize($needle)) )
            return true;
    }
    return false;
}

function array_sort_by_key($array, $order, $desc = false)
{
    $ret = $array;
    $newret = array();
    foreach ( $ret as $key=>$value )
    {
        if ( isset($value[$order]) )
        {
            $i = $desc ? 99999999 : 0;
            $r = $value[$order];
            while (isset($newret[$r . $i]))
            {
                if ( $desc )
                    $i-- ;
                else
                    $i++ ;
            }
            $newret[$r . $i] = $ret[$key];
        }
        else
        {
            $i = $desc ? 99999999 : 0;
            $r = "";
            while (isset($newret[$r . $i]))
            {
                if ( $desc )
                    $i-- ;
                else
                    $i++ ;
            }
            $newret[$r . $i] = $ret[$key];
        }
    }
    ksort($newret);
    if ( $desc == true )
    {
        $newret = array_reverse($newret);
    }
    return $newret;

}

class XMLDatabase
{
    var $path;
    var $databasename;
    function XMLDatabase($databasename, $path = "misc")
    {
        $this->databasename = $databasename;
        $this->path = $path;
    }
    /**
     * Parser SQL
     * es.
     * SELECT * FROM table1 WHERE field1 AS alias1, field2 WHERE field1 = "condition" OR field2 = "condition2" ORDER BY field1 LIMIT 1,10
     *
     * Limitazioni attuali:
     * ancora da implementare INSERT,UPDATE,DELETE
     */
    function Query($query)
    {
        $databasename = $this->databasename;
        static $tblcache = array();

        $qitems = array();
        $qitems['fields'] = false;
        $qitems['tablename'] = false;
        $qitems['option'] = false;
        $qitems['orderby'] = false;
        $qitems['min'] = false;
        $qitems['length'] = false;
        $qitems['where'] = false;
        $fieldstoget = false;
        //SELECT
        if ( preg_match("/^SELECT/is",$query) )
        {
            if ( preg_match("/^SELECT( DISTINCT | )([a-zA-Z0-9, \(\)\*]+|\*|COUNT\(\*\)) FROM (\w+)(.+)/i","$query ",$t1) )
            {
                //campi
                $qitems['fields'] = trim(ltrim($t1[2]));
                $qitems['tablename'] = trim(ltrim($t1[3]));
                $qitems['option'] = trim(ltrim($t1[1]));
                //dprint_r($t1);
                //dprint_r($qitems);
                $tmpwhere = $t1[4];
                $cid = $this->path . $databasename . $qitems['tablename'];
                if (  !isset($tblcache[$cid]) )
                    $tblcache[$cid] = new XMLTable($databasename,$qitems['tablename'],$this->path);
                $tbl = &$tblcache[$cid];
                //$tbl = new XMLTable($databasename, $qitems['tablename'], $this->path);


                if ( $qitems['tablename'] == "" )
                    return "xmldb: syntax error";

                if (  !file_exists($this->path . "/$databasename/{$qitems['tablename']}.php") )
                    return "xmldb: unknow table {$qitems['tablename']}";

                //dprint_r($tbl);
                //native mysql table ----------------------->
                /*
                if (isset($tbl->driverclass->connection))
                {
                    $ret = $tbl->driverclass->dbQuery($query, $databasename);
                    //dprint_r($ret);
                    return $ret;
                }
                */
                //native mysql table -----------------------<


                //dprint_r($t1);
                if ( preg_match("/WHERE (.+)/i",$tmpwhere,$t1) )
                {
                    $tmpwhere = $t1[1];
                    $qitems['where'] = $tmpwhere;
                }
                else
                    $qitems['where'] = "";

                if ( preg_match("/(.+)LIMIT ([0-9]+),([0-9]+)/i",$tmpwhere,$dt3) )
                {
                    //dprint_r($dt3);
                    $qitems['min'] = $dt3[2];
                    $qitems['length'] = $dt3[3];
                    $tmpwhere = $dt3[1];
                    $qitems['where'] = $tmpwhere;
                }
                if ( preg_match("/(.+)ORDER BY (.*)(i:limit|)/i",$tmpwhere,$dt2) )
                {
                    //dprint_r($dt2);
                    $tmpwhere = $dt2[1];
                    $qitems['orderby'] = trim(ltrim($dt2[2]));
                    $qitems['where'] = $tmpwhere;
                }

                //dprint_r($qitems);


            }

            //dprint_r($qitems);
            //----CONDIZIONE---------------------->
            $where2 = preg_replace("/(\\w+)( = )/",'\$item[\'${1}\'] == ',trim(ltrim($qitems['where'])));
            $where2 = preg_replace("/(\\w+)( > )/",'\$item[\'${1}\'] > ',$where2);
            $where2 = preg_replace("/(\\w+)( < )/",'\$item[\'${1}\'] < ',$where2);
            $where2 = preg_replace("/(\\w+)( >= )/",'\$item[\'${1}\'] >= ',$where2);
            $where2 = preg_replace("/(\\w+)( <= )/",'\$item[\'${1}\'] <= ',$where2);
            $where2 = preg_replace("/(\\w+)( <> )/",'\$item[\'${1}\'] != ',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+"%(.*?)%"/i','preg_match("/${3}/i",\$item[\'${1}\'])',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+"%(.*?)"/i','preg_match("/${3}$/i",\$item[\'${1}\'])',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+"(.*?)%"/i','preg_match("/^${3}/i",\$item[\'${1}\'])',$where2);

            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+"(.*?)"/i','"${3}" == \$item[\'${1}\']',$where2);
            //$where2 = preg_replace ( '/(\w+)[\040]+(LIKE)[\040]+"(.*?)"/i', 'preg_match("/${3}/i",\$item[\'${1}\'])', $where2 );


            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+\'%(.*?)%\'/i','preg_match("/${3}/i",\$item[\'${1}\'])',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+\'%(.*?)\'/i','preg_match("/${3}$/i",\$item[\'${1}\'])',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+\'(.*?)%\'/i','preg_match("/^${3}/i",\$item[\'${1}\'])',$where2);
            $where2 = preg_replace('/(\w+)[\040]+(LIKE)[\040]+\'(.*?)\'/i','"${3}" == \$item[\'${1}\']',$where2);
            //$where2 = preg_replace ( '/(\w+)[\040]+(LIKE)[\040]+\'(.*?)\'/i', 'preg_match("/${3}/i",\$item[\'${1}\'])', $where2 );
            //dprint_r($where2);
            //----CONDIZIONE----------------------<
            //per ottimizzare prendo solo i fields che mi interessano--->
            if ( $qitems['fields'] == "*" || preg_match('/COUNT\(\*\)/is',$qitems['fields']) )
            {

                $fieldstoget = false;
            }
            else
            {
                //per performance coinvolgo solamente i fields interessati dalla query
                if ( $qitems['orderby'] != "" )
                {
                    $t = explode(",",$qitems['orderby']);
                    foreach ( $t as $tf )
                    {
                        if ( preg_match("/([a-zA-Z0-9_]+)/",$tf,$pm) )
                            $fieldstoget[] = trim(ltrim($pm[0]));
                    }
                }
                $t = explode(",",$qitems['fields']);
                foreach ( $t as $tf )
                {
                    if ( preg_match("/([a-zA-Z0-9_]+)/",$tf,$pm) )
                        $fieldstoget[] = trim(ltrim($pm[0]));
                }
                $t = xmldb_iExplode(" OR ",$qitems['where']);
                //dprint_r($t);
                foreach ( $t as $tf )
                {
                    $tf = trim(ltrim($tf));
                    if ( preg_match("/([a-zA-Z0-9_]+)/",$tf,$pm) )
                        $fieldstoget[] = trim(ltrim($pm[0]));
                }
                $t = xmldb_iExplode(" AND ",$qitems['where']);
                foreach ( $t as $tf )
                {
                    $tf = trim(ltrim($tf));
                    if ( preg_match("/([a-zA-Z0-9_]+)/",$tf,$pm) )
                        $fieldstoget[] = trim(ltrim($pm[0]));
                }
                if (  !is_array($fieldstoget) )
                    return "xmldb: syntax error";

                $fieldstoget = array_unique($fieldstoget);
            }
            //dprint_r($fieldstoget);
            //per ottimizzare prendo solo i fields che mi interessano--->


            $allrecords = $tbl->GetRecords(false,false,false,false,false,$fieldstoget);

            //ordinamento -------->
            if ( $qitems['orderby'] != "" )
            {
                $orders = explode(",",$qitems['orderby']);
                foreach ( $orders as $order )
                {

                    if ( preg_match("/([a-zA-Z0-9]+)(.*)/s",$order,$orderfields) );
                    {
                        //dprint_r($orderfields);
                        if ( preg_match("/DESC/is",trim(ltrim($orderfields[2]))) )
                        {
                            $isdesc = true;
                        }
                        else
                            $isdesc = false;

                        $allrecords = array_sort_by_key($allrecords,trim(ltrim($orderfields[1])),$isdesc);
                    }
                }
            }
            //ordinamento --------<


            $i = 0;
            $ret = null;

            //filtro search condition -------------------->
            if (  !is_array($allrecords) )
                return null;
            foreach ( $allrecords as $item )
            {
                $ok = false;

                //eval($where2);
                //dprint_r ("if ($where2){". '$ok=true;'."}");
                if ( $where2 == "" )
                    $ok = true;
                else
                    eval("if ($where2){" . '$ok=true;' . "}");
                if ( $ok == false )
                    continue;
                //dprint_r($qitems);
                if ( $qitems['fields'] == "*" )
                {
                    $tmp = $item;
                }
                else
                {

                    $fields = explode(",",$qitems['fields']);
                    $tmp = null;
                    //dprint_r($fields);
                    //alias ------->
                    foreach ( $fields as $field )
                    {
                        if ( preg_match("/ AS /is",$field) )
                        {

                            $as = xmldb_iExplode(" AS ",$field);
                            $k2 = trim(ltrim($as[1]));
                            $k1 = trim(ltrim($as[0]));
                        }
                        else
                            $k1 = $k2 = trim(ltrim($field));

                        if (  !isset($item[$k1]) && strtoupper($k1) != "COUNT(*)" )
                            return "xmldb: unknow row '$k1' in table {$qitems['tablename']}";

                        if ( strtoupper($k1) != "COUNT(*)" )
                        {
                            //echo "field = $k1 ";
                            $tmp[$k2] = $item[$k1];
                        }
                        else
                            $tmp[$k2] = $item;

                    }
                    //alias -------<
                }
                //----distinct------------->
                if ( strtoupper($qitems['option']) == "DISTINCT" )
                    if ( array_in_array($tmp,$ret) )
                        continue;
                //----distinct-------------<
                $i++ ;
                //----min length----------->
                if ( ($qitems['min']) && $i < $qitems['min'] )
                    continue;
                if ( ($qitems['min'] && $qitems['length']) && ($i) >= ($qitems['min'] + $qitems['length']) )
                    break;
                //----min length----------->
                $ret[] = $tmp;
            }
            //filtro search condition --------------------<
            //dprint_r($qitems);
            $count = false;
            if ( stristr($qitems['fields'],"COUNT(*)") )
            {
                // ADDED BY DANIELE FRANZA 2/02/2009: start
                if ( preg_match("/ AS /is",$qitems['fields']) )
                {
                    $as = xmldb_iExplode(" AS ",$qitems['fields']);
                    $k2 = trim(ltrim($as[1]));
                    $k1 = trim(ltrim($as[0]));
                }
                else
                {
                    //if (!isset($field))
                    //	$field="COUNT(*)";
                    $k1 = $k2 = trim(ltrim($field));
                }
                // ADDED BY DANIELE FRANZA 2/02/2009: end


                $count = true;
                //$fields = false;
            }

            if ( $count )
                return array(0=>array("$k2"=>count($ret)));
            else
                return $ret;
        }

        //DESCRIBE  TODO
        if ( preg_match("/^DESCRIBE/is",$query) )
        {
            if ( preg_match("/^DESCRIBE ([a-zA-Z0-9_]+)/is","$query ",$t1) )
            {
                $qitems['tablename'] = trim(ltrim($t1[1]));
                $cid = $this->path . $databasename . $qitems['tablename'];
                if (  !isset($tblcache[$cid]) )
                    $tblcache[$cid] = new XMLTable($databasename,$qitems['tablename'],$this->path);
                $t = &$tblcache[$cid];
                //				$t = new XMLTable($databasename, $qitems['tablename'], $this->path);
                //native mysql table ----------------------->
                /*
                if ($t->driverclass->connection)
                {
                    return $t->driverclass->dbQuery($query);
                }
                //native mysql table -----------------------<
                else*/
                {
                    $ret = array();
                    foreach ( $t->fields as $field )
                    {
                        $ret[] = array("Field"=>$field->name,"Type"=>$field->type,"Null"=>"NO","Key"=>$field->primarykey,"Extra"=>$field->extra);
                    }
                    return $ret;
                }
            }
        }
        //SHOW TABLES
        if ( preg_match("/^SHOW TABLES/is",trim(ltrim($query))) )
        {
            $path = ($this->path . "/" . $this->databasename . "/*");
            $files = glob($path);
            $ret = array();
            foreach ( $files as $file )
            {
                if (  !is_dir($file) )
                    $ret[] = array("Tables_in_" . $this->databasename=>preg_replace('/.php$/s','',basename($file)));
            }
            return $ret;
        }
        //INSERT
        if ( preg_match("/^INSERT/is",$query) )
        {
            if ( preg_match("/^INSERT[ ]+INTO([a-zA-Z0-9\\`\\._ ]+)\\(([a-zA-Z_ ,]+)\\)[ ]+VALUES[ ]+\\((.*)\\)/i","$query ",$t1) )
            {
                $qitems['tablename'] = trim(ltrim($t1[1]));
                $qitems['fields'] = trim(ltrim($t1[2]));
                $qitems['values'] = trim(ltrim($t1[3]));
                $cid = $this->path . $databasename . $qitems['tablename'];
                if (  !isset($tblcache[$cid]) )
                    $tblcache[$cid] = new XMLTable($databasename,$qitems['tablename'],$this->path);
                $tbl = &$tblcache[$cid];
                //native mysql table ----------------------->
                /*if ($tbl->driverclass->connection)
                {
                    $ret = $t->driverclass->dbQuery($query);
                    //dprint_r($ret);
                    return $ret;
                }*/
                //native mysql table -----------------------<


                $fields = explode(",",$qitems['fields']);
                $values = explode(",",$qitems['values']);
                $recordstoinsert = array();
                if ( count($fields) == count($values) )
                {
                    for ( $i = 0;$i < count($fields);$i++  )
                    {
                        $recordstoinsert[$fields[$i]] = preg_replace("/^'/","",preg_replace("/'$/s","",preg_replace('/^"/s',"",preg_replace('/"$/s',"",$values[$i]))));
                    }
                }
                else
                    return "xmldb: syntax error";

                return $tbl->InsertRecord($recordstoinsert);
            }
        }
        //UPDATE TODO
        if ( preg_match("/^UPDATE/is",$query) )
        {
            //UPDATE `fndatabase`.`users` SET `name` = 'quattro' WHERE CONVERT( `users`.`email` USING utf8 ) = 'uno' LIMIT 1 ;


        }
        //DELETE TODO
        if ( preg_match("/^DELETE/is",$query) )
        {

        }
    }
}

function xmldb_iExplode($Delimiter, $String, $Limit = '')
{
    $tmpString = strtoupper($String);
    $tmpDelimiter = strtoupper($Delimiter);
    $tmpret = explode($tmpDelimiter,$tmpString);
    $start = 0;
    foreach ( $tmpret as $r )
    {
        $length = strlen($r);
        $ret[] = substr($String,$start,$length);
        $start += strlen($r . $Delimiter);
    }
    return ($ret);
}

function print_xmldb_records($array, $extraparam = "border='1'")
{
    if ( is_array($array) && count($array) > 0 )
    {
        echo "<table $extraparam>";
        $f = true;
        foreach ( $array as $val )
        {
            if ( is_array($val) && count($val) > 0 )
            {
                if ( $f )
                {
                    $f = false;
                    echo "<tr>";
                    foreach ( $val as $title=>$v )
                    {
                        echo "<td><b>" . htmlspecialchars($title) . "</b></td>";
                    }
                    echo "</tr>";
                }
                echo "<tr>";
                foreach ( $val as $title=>$v )
                {
                    echo "<td>" . htmlspecialchars($v) . "</td>";
                }
                echo "</tr>";

            }

        }
        echo "</table>";

    }

}

function xmldblockfile($file_lock)
{
    /*	$max_retries = 20000;
    $lockfile=$file_lock.".lock";
    $retries = 0;
    clearstatcache();
    // se non esiste fa subito la open
    while (file_exists($lockfile) || (!$fp = @fopen($lockfile, "a+")))
    {
        if ($retries >= $max_retries)
        {
            @unlink($lockfile);
            return false;
        }
        usleep(rand(1, 100));
        $retries ++;
        clearstatcache();
    }
    fclose($fp);
    return $lockfile;

    $max_retries = 20000;
    $retries = 0;
    if (!$fp = fopen($file_lock, "a+"))
    {
        return false;
    }
    // keep trying to get a lock as long as possible
    while (!flock($fp, LOCK_EX+LOCK_NB) and $retries <= $max_retries)
    {
        usleep(rand(1, 100));
        $retries ++;
    }

    if ($retries >= $max_retries)
    {
        //die("error lock 2");
    }
    return $fp;
        */
}

function xmldbunlockfile($sem)
{
    if (  !flock($sem,LOCK_UN) )
        return false;
    return true;
}

?>