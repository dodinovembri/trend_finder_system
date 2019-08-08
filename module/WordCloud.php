<style type="text/css">
    /*#tagcloud {
    width: 300px;
    background:#CFE3FF;
    color:#0066FF;
    padding: 10px;
    border: 1px solid #559DFF;
    text-align:center;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
}*/
 
#tagcloud a:link, #tagcloud a:visited {
    text-decoration:none;
    color: #333;
}
 
#tagcloud a:hover {
    text-decoration: underline;
}
 
#tagcloud span {
    padding: 4px;
}
 
#tagcloud .smallest {
    font-size: x-small;
}
 
#tagcloud .small {
    font-size: small;
}
 
#tagcloud .medium {
    font-size:medium;
}
 
#tagcloud .large {
    font-size:large;
}
 
#tagcloud .largest {
    font-size:larger;
}
</style>
<div id="tagcloud">
<?php  

class WordCloud
{
    var $words = array();
 
    function __construct($text)
    {
 
        $words = explode(' ', $text);        
        foreach ($words as $key => $value)
        {
                $this->addWord($value);
        }
 
    }
 
    function addWord($word, $value = 1)
    {
        $word = strtolower($word);
 
        if (array_key_exists($word, $this->words))
            $this->words[$word] += $value;
        else
            $this->words[$word] = $value;
    }
 
 
    function getSize($percent)
    {
        $size = "font-size: ";
 
        if ($percent >= 99)
            $size .= "5em;";
        else if ($percent >= 95)
            $size .= "4.5em;";
        else if ($percent >= 80)
            $size .= "4em;";
        else if ($percent >= 70)
            $size .= "3.5em;";
        else if ($percent >= 60)
            $size .= "3em;";
        else if ($percent >= 50)
            $size .= "2.5em;";
        else if ($percent >= 40)
            $size .= "2em;";
        else if ($percent >= 30)
            $size .= "1.5em;";
        else if ($percent >= 25)
            $size .= "1.4em;";
        else if ($percent >= 20)
            $size .= "1.3em;";
        else if ($percent >= 15)
            $size .= "1.2em;";
        else if ($percent >= 10)
            $size .= "1.1.em;";
        else if ($percent >= 5)
            $size .= "1em;";
        else
            $size .= "0.9em;";
 
        return $size;
    }
 
    function showCloud($show_freq = false)
    {
        $this->max = max($this->words);
 
        foreach ($this->words as $word => $freq)
        {
            if(!empty($word))
            {
                $size = $this->getSize(($freq / $this->max) * 100);
                if($show_freq) $disp_freq = "($freq)"; else $disp_freq = "";
 
                $out .= "<span style='font-family: Tahoma; color:#1e282c; padding: 4px 4px 4px 4px; letter-spacing: 3px; $size'>
                            &nbsp; {$word}<sup>$disp_freq</sup> &nbsp; </span>";
            }
        }
 
        return $out;
    }
 
	}

?>
</div>



