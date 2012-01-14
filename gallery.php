<html>
	<head>
		<?php
			/**
			* Set up the Kaltura Client
			**/
			require_once('kaltura-php5/KalturaClient.php');
			require_once('Vidyo2KalturaConfig.php');
			$kConfig = new KalturaConfiguration(Vidyo2KalturaConfig::PARTNER_ID);
			$client = new KalturaClient($kConfig);
			$ks = $client->generateSession(Vidyo2KalturaConfig::ADMIN_SECRET, 'vidyoSyncClient', KalturaSessionType::ADMIN, Vidyo2KalturaConfig::PARTNER_ID);
			$client->setKs($ks);

			// create a filter to define what exactly we want to be in the list
			$filter = new KalturaMediaEntryFilter();
			$filter->statusEqual = KalturaEntryStatus::READY;
			$filter->mediaTypeIn = KalturaMediaType::VIDEO.','.KalturaMediaType::IMAGE.','.KalturaMediaType::AUDIO;
			// order the results by the creation data descending
			$filter->orderBy = KalturaBaseEntryOrderBy::CREATED_AT_DESC;
			// or ascending :
			// $filter->orderBy = KalturaBaseEntryOrderBy::CREATED_AT_ASC;
			$pager = new KalturaFilterPager();
			// choose the page_size to be some number that will fit the area you would like to display the thumbnails gallery
			$page_size = 20; 
			// get page number from request parameters:
			$page = $_REQUEST['pagenum']; // read the current page from the request.
			// page=1 is the first page
			if ( $page < 1 ) $page = 1;
			$pager->pageSize = $page_size;
			$pager->pageIndex = $page;
			// get the list of entries:
			$result = $client->media->listAction($filter, $pager);

			$count = $result->totalCount;
			$entries = $result->objects;
			if ( ! $entries ) $entries = array(); // be fail safe

			//to debug and dump the result, uncomment the line below -
			//echo '<pre>'.print_r($result, true).'</pre>'; die();

			function create_gallery_pager  ($page_number, $current_page , $page_size , $count ,$js_callback_paging_clicked  )
			{
				$page_number = (int)$page_number;
				$a = $page_number * $page_size + 1;
				$b = (($page_number+1) * $page_size) ;
				$b = min ( $b , $count ); // don't let the page-end be bigger than the total count
				$page_to_goto = $page_number + 1;
				if ( $page_to_goto == $current_page )
					$str = "[<a title='{$page_to_goto}' href='javascript:{$js_callback_paging_clicked} ($page_to_goto)'>{$a}-{$b}</a>] ";
				else
					$str =  "<a title='{$page_to_goto}' href='javascript:{$js_callback_paging_clicked} ($page_to_goto)'>{$a}-{$b}</a> "; 
				return $str;	
			}

			// set the number of images in a single row of the table
			$images_in_row = 5; 
			$pager_string = "";
			$start_page = max ( 1 , $page - 5 );
			$very_last_page = (int)($count / $page_size);
			$very_last_page += ($count % $page_size == 0) ? 0 : 1;
			$end_page = min ( $very_last_page , $start_page + 10 );
			for ($page_number = $start_page; $page_number < $end_page; ++$page_number)
			{
				$pager_string .= create_gallery_pager ($page_number , $page  , $page_size , $count , "pagerClicked");
			}	
			$before_page_string = "";
			$after_page_string = "";
			// add page 0 if not in list  
			if ( $start_page > 0 ) $before_page_string .= create_gallery_pager ( 0, $page ,$page_size , $count , "pagerClicked" )  ; 
			// have some dots if there is a real gap between 0 and the rest
			if ( $start_page > 1 ) $before_page_string .= "..."; if ( $end_page < $very_last_page -1 ) $after_page_string .= "..."; 
			//add last page if lot in list
			if ( $end_page < $very_last_page ) $after_page_string .= create_gallery_pager ( $very_last_page , $page  , $page_size, $count, "pagerClicked"); 
			// combine all pager strings into one
			$pager_string = "<span style=\"color:#ccc;\">Total (" . $count . ") </span>" . $before_page_string . $pager_string . $after_page_string;

			$gallery_html = "<table><tr>";
				
			$i=0;
			foreach ( $entries as $entry ) 
			{ 
				$name = $entry->name;
				$type = $entry->mediaType;
				$id = $entry->id;
				$display =  $entry->thumbnailUrl ? "<img width='120' height='90' src='" . $entry->thumbnailUrl . "' title='" . $id . " ". $name .  "' >" : "<div>" .  $id . " ". $name . "</div>";
				//create a link to the player for this specific entry. entryClicked is the name of the javascript function to call.
				$gallery_html .= "<td style='overflow:hidden; vertical-align:top ;  style='width:130px; height:100px;'><a href='javascript:entryClicked (\"$id\")'>{$display}</a>" . "<br>$name" . "</td>";
				++$i; 
				if ( $i % $images_in_row == 0 ) $gallery_html .=  "</tr><tr>";
			}
			$gallery_html .= "</tr></table>";
		?>
		<link href="lib/facebox.css" media="screen" rel="stylesheet" type="text/css" />
		<script src="lib/jquery.js" type="text/javascript"></script>
		<script src="lib/facebox.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function($) {
				$.facebox.settings.closeImage = './lib/closelabel.png';
				$.facebox.settings.loadingImage = './lib/loading.gif';
			});
		</script>
		<script type="text/javascript" >
			function entryClicked (entry_id) {
				var playerurl = 'player.php?entryid=' + entry_id + '&partnerid=<?php echo Vidyo2KalturaConfig::PARTNER_ID; ?>';
				$.facebox({ajax:playerurl});
			}
			
			function pagerClicked (page_number)	{
				window.location = "./gallery.php?pagenum=" + page_number; 
			}
		</script>
	</head>
	<body>
		<?php echo $pager_string . "<br>" . $gallery_html; ?>
	</body>
</html>