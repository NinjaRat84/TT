<?php 
// contains little procedural functions to output various HTML strings

// Adapted code from the MIT licensed QuickDD class
// created also by me
function DaskalQuickDDDate($name, $date=NULL, $format=NULL, $markup=NULL, $start_year=1900, $end_year=2100)
{
   // normalize params
   if(empty($date) or !preg_match("/\d\d\d\d\-\d\d-\d\d/",$date)) $date=date("Y-m-d");
    if(empty($format)) $format="YYYY-MM-DD";
    if(empty($markup)) $markup=array();

    $parts=explode("-",$date);
    $html="";

    // read the format
    $format_parts=explode("-",$format);

    $errors=array();
    
    // let's output
    foreach($format_parts as $cnt=>$f)
    {
        if(preg_match("/[^YMD]/",$f)) { 
            $errors[]="Unrecognized format part: '$f'. Skipped.";
            continue;
        }

        // year
        if(strstr($f,"Y"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."year\"".$extra_html.">\n";

            for($i=$start_year;$i<=$end_year;$i++)
            {
                $selected="";
                if(!empty($parts[0]) and $parts[0]==$i) $selected=" selected";
                
                $val=$i;
                // in case only two digits are passed we have to strip $val for displaying
                // it's either 4 or 2, everything else is ignored
                if(strlen($f)<=2) $val=substr($val,2);        
                
                $html.="<option value='$i'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // month
        if(strstr($f,"M"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."month\"".$extra_html.">\n";

            for($i=1;$i<=12;$i++)
            {
                $selected="";
                if(!empty($parts[1]) and intval($parts[1])==$i) $selected=" selected";
                
                $val=sprintf("%02d",$i);
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // day - we simply display 1-31 here, no extra intelligence depending on month
        if(strstr($f,"D"))
        {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."day\"".$extra_html.">\n";

            for($i=1;$i<=31;$i++)
            {
                $selected="";
                if(!empty($parts[2]) and intval($parts[2])==$i) $selected=" selected";
                
                if(strlen($f)>1) $val=sprintf("%02d",$i);
                else $val=$i;
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }
    }

    // that's it, return dropdowns:
    return $html;
}

// safe redirect
function daskal_redirect($url) {
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}