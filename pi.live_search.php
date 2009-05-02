<?php

/*
=====================================================
 Copyright (c) 2008 Pascal Kriete
-----------------------------------------------------
 http://pascalkriete.com/
=====================================================
 File: pi.live_search.php
-----------------------------------------------------
 Purpose: Generate live-search json results
=====================================================
Changelog:
- Version 1.1.0
	Removed trailing comma from js array
	Updated javascript to accept optional parameters
- Version 1.0.2
	Fixed PHP Notice bug
- Version 1.0.1
	Added 'No Results' text
- Version 1.0.0
	Plugin Created
*/


$plugin_info = array(
						'pi_name'			=> 'Live Search',
						'pi_version'		=> '1.1.0',
						'pi_author'			=> 'Pascal Kriete',
						'pi_author_url'		=> 'http://pascalkriete.com/',
						'pi_description'	=> 'Generate search results for the accompanying jQuery plugin.',
						'pi_usage'			=> Live_search::usage()
					);


class Live_search {

	/**
	 * Display Search Results
	 *
	 * @access	public
	 * @return	string
	 */
	function results()
	{
		global $IN, $DB, $LOC, $TMPL; 

		if ( ! $IN->QSTR) 
		{
		    return '';
		}

		$search_phrase =& $IN->QSTR;
		$search_pharse = $DB->escape_str( urldecode($search_phrase) );
		
		/** ---------------------------------------
		/**  Build and run the query
		/** ---------------------------------------*/
		
		$sql = "SELECT distinct(a.entry_id), a.url_title, a.title, b.blog_url, b.comment_url 
				FROM exp_weblog_titles a, exp_weblogs b
				WHERE a.weblog_id = b.weblog_id
				AND a.status != 'closed'
				AND (a.expiration_date > '".$LOC->now."' OR a.expiration_date = '0')
				AND a.title LIKE '%{$search_phrase}%'
				ORDER BY a.title ASC LIMIT 0,10";

		$query = $DB->query($sql);

		/** ---------------------------------------
		/**  Empty, no point in continuing
		/** ---------------------------------------*/
		
		if ($query->num_rows == 0) 
		{ 
		    echo '[{ "title" : "No results", "path" : "#" }]';
			exit;
		}
		
		/** ---------------------------------------
		/**  Process parameters
		/** ---------------------------------------*/
				
		if( $TMPL->fetch_param('link') == 'comment')
		{
			$link_to = 'comment_url';
		}
		else
		{
			$link_to = 'blog_url';
		}
		
		/** ---------------------------------------
		/**  Create javascript json array
		/** ---------------------------------------*/

		$data = '[';
		foreach($query->result as $row) 
		{
		    $data .= '{ "title" : "'.$row['title'].'", "path" : "'.$row[$link_to].$row['url_title'].'" }, ';
		}
		
		// Take off the last comma
		$data = substr($data, 0, -2);
		$data .= ']';
		
		echo $data;
		exit;
	}
	
	function _safe_json($val)
	{
		
	}
    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
// Make sure and use output buffering

function usage()
{
ob_start(); 
?>
EE Template:

{exp:live_search:results}

------------------------

EE Parameters:

link="comment"
- The weblog path to link to.  Options are "blog" and "comment".  Default is "blog".

------------------------

Javascript:

Make sure you have an up-to-date version of jQuery included before the plugin javascript.

Parameters:
width			: width of the displayed results
center_results	: boolean to center the result box

Examples:
$('#keywords').livesearch('/search/livesearch/');
$('#keywords').livesearch('/search/livesearch/', { width: '200' });
$('#keywords').livesearch('/search/livesearch/', { center_results: true });
$('#keywords').livesearch('/search/livesearch/', { width: '200', center_results: true });

<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
// END


}
// END CLASS
?>