<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 15/09/14
 * Time: 23:40
 */


class Feed_Library
{







	# ideally, we'd grab these via ajax, in case there is a delay
	public function getFeed($feed_url="",$elements=0,$chars=0)
	{
		$x = @simplexml_load_file($feed_url);
		if ($x)
		{

			$blog_entries	=	"";

			for($i = 0; $i < $elements; $i++)
			{
				if(!empty($x->channel->item[$i]))
				{
					$title       = $x->channel->item[$i]->title;
					$link        = $this->safe_url($x->channel->item[$i]->link);
					$description = $this->clean_text($this->getTextBetweenTwoTags('<p>', '</p>', $x->channel->item[$i]->description, $chars));
					# tags are not always present
					if	(empty($description))
					{
						$description = $this->clean_text(substr($x->channel->item[$i]->description, 0, $chars));
					}
					$pubDate     = $x->channel->item[$i]->pubDate;

					$blog_entries .= $this->formatBlogEntry($title, $link, $description, $pubDate);
				}
			}

			return	$blog_entries;
		}
	}




	# formats the article
	private function formatBlogEntry($title,$link,$description,$date)
	{
		$string = "<div class='col-sm-4'>
<div >
<h2 class='blog-post-title'><a href='$link' target='_blank'>$title</a></h2>
<p>$description</p>
</div>
</div>";

		return	$string;

	}






	private function getTextBetweenTwoTags($startTag,$endTag,$text,$length=80)
	{
		$pos1 = strpos($text,$startTag,0);

		if(!is_integer($pos1)){
			return false;
		}
		$pos1 += strlen($startTag);
		$pos2 = strpos($text,$endTag,$pos1);

		if(!is_integer($pos2)){
			$pos = false;return false;
		}


		$max_length = min($length,($pos2-$pos1));
		$res = substr($text,$pos1,$max_length);

		$pos3	 =	strrpos($res, " ", -(strlen($res) - $max_length));
		$res2 	= substr($text,$pos1,$pos3) . "...";


		$pos = $pos2 + strlen($endTag);
		return $res2;
	}



	private function clean_text($text, $length = 0)
	{
		$html = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		$text = strip_tags($html);
		if ($length > 0 && strlen($text) > $length) {
			$cut_point = strrpos(substr($text, 0, $length), ' ');
			$text = substr($text, 0, $cut_point) . '…';
		}
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');
		return $text;
	}


	private function safe_url($raw_url)
	{
		$url_scheme = parse_url($raw_url, PHP_URL_SCHEME);
		if ($url_scheme == 'http' || $url_scheme == 'https')
		{
			return htmlspecialchars($raw_url, ENT_QUOTES, 'UTF-8',
				false);
		}
		// parse_url failed, or the scheme was not hypertext-based.
		return false;
	}







}