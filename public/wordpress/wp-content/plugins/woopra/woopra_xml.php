<?php

/**
 * @authors Shane Froebel
 * @Credits: Thank you shane for this class! Shane is also the developer of the Woopra Plugin for vBulletin
 * @copyright 2008
 */

class WoopraAPI {

    //	XML parser variables
    var $parser = null;
    var $url = null;
    var $data = null;
	var $counter = null;     
    var $current_tag = null;
    var $error_msg = null;
    var $founddata = false;
    
    //	Woopra Vars
    var $siteid = null;
    var $server = null;
    var $hostname = null;
    
	/**
	 * WoopraAPI::Init()
	 * 
	 * @return void
	 */
	function Init()
	{
		if (!$this->siteid)
		{
			return false;	//	they both need to be set for this to work
		}
		$this->server = floor(((int)($this->siteid))/100000000);
		return true;
	}

	/**
	 * WoopraAPI::setXML()
	 * @uses Set the varabiles for the URL.
	 * @param mixed $area
	 * @param mixed $start_day
	 * @param mixed $end_day
	 * @param mixed $limit
	 * @param mixed $offset
	 * @return void
	 */
	function setXML($area, $start_day, $end_day, $limit, $offset)
	{
		$this->url = "http://engine".$this->server.".woopra.com/api/output_format=xml&website=".$this->hostname."&api_key=".woopra_api_key."&query=".$area."&start_day=".$start_day."&end_day=".$end_day."&limit=".$limit."&offset=".$offset;
		return true;	//	URL is set
	}
	
    /**
     * WoopraAPI::processData()
     * @uses To process the XML file once the setting we want is set.
     * @return
     */
    function processData()
	{
    	$this->clearData();
        return $this->parse();
    }

  	/**
  	 * WoopraAPI::clearData()
  	 * 
  	 * @return void
  	 */
  	function clearData()
  	{
  		$this->data = Array();
    	$this->counter = 0;
  	}
  
    /**
     * WoopraAPI::parse()
     * 
     * @return void
     */
    function parse()
    {
        $this->parser = xml_parser_create ("UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startXML', 'endXML');
        xml_set_character_data_handler($this->parser, 'charXML');

        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);

        if (!($fp = @fopen($this->url, 'rb')))
		{
            $this->error_msg = "Cannot open {$this->url}";
            return $this->error();
        }

        while (($data = fread($fp, 8192)))
		{
            if (!xml_parse($this->parser, $data, feof($fp)))
			{
                $this->error_msg = sprintf('XML error at line %d column %d', xml_get_current_line_number($this->parser), xml_get_current_column_number($this->parser));
                return $this->error();
            }
        }
        
        if ($this->founddata)
        {
        	return true;	//	after we did everything, was there some "entry" found?	
        } else {
        	$this->error_msg = "No data entries.";
        	return false;	//	not an error, but return false for if checks...
        }     
    }

	/**
	 * WoopraAPI::startXML()
	 * 
	 * @param mixed $parser
	 * @param mixed $name
	 * @param mixed $attribs
	 * @return void
	 */
	function startXML($parser, $name, $attribs)
	{
		if (($name == "entry") && (!$this->founddata))
		{
			$this->founddata = true;
		}
		$this->current_tag = $name;
    }

    /**
     * WoopraAPI::endXML()
     * 
     * @param mixed $parser
     * @param mixed $name
     * @return void
     */
    function endXML($parser, $name)
	{
		if ($name == "entry")
		{
			$this->counter++;
		}
    }

    /**
     * WoopraAPI::charXML()
     * 
     * @param mixed $parser
     * @param mixed $data
     * @return void
     */
    function charXML($parser, $data)
	{
		if ($this->founddata)
		{
	    	$this->data[$this->counter][$this->current_tag] = $data;		
		}
	}
	
	/**
	 * WoopraAPI::error()
	 * 
	 * @return
	 */
	function error()
	{
		return false;
	}
	
}


?>