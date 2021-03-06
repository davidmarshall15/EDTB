<?php
/**
 * Galnet news
 *
 * No description
 *
 * @package EDTB\Main
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

 /*
 * ED ToolBox, a companion web app for the video game Elite Dangerous
 * (C) 1984 - 2016 Frontier Developments Plc.
 * ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require phpfastcache */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/Vendor/phpfastcache/phpfastcache.php");

/** @var pagetitle */
$pagetitle = "Galnet News";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

// CACHE
$html = __c("files")->get("galnet");

if ($html == null)
{
	ob_start();
	?>
	<div class="entries">
		<div class="entries_inner">
		<h2><img class="icon24" src="/style/img/galnet.png" alt="GalNet" style="margin-right:6px" />Latest Galnet News</h2>
		<hr>
		<?php
		$xml = xml2array($galnet_feed) or die("Error: Cannot create object");

		$i = 0;
		foreach ($xml["rss"]["channel"]["item"] as $data)
		{
			$title = $data["title"];
			$link = $data["link"];
			$text = $data["description"];

			// exclude stuff
			$continue = true;
			foreach ($settings["galnet_excludes"] as $exclude)
			{
				$find = $exclude;
				$pos = strpos($title, $find);

				if ($pos !== false)
				{
					$continue = false;
					break 1;
				}
			}

			if ($continue !== false)
			{
				echo '<h3>';
				echo '	<a href="javascript:void(0)" onclick="$(\'#'.$i.'\').fadeToggle()">';
				echo '		<img class="icon" src="/style/img/plus.png" alt="expand" style="padding-bottom:3px" />' . $title;
				echo '	</a>';
				echo '</h3>';
				echo '<p id="' . $i . '" style="display:none;padding-left:22px;max-width:800px">';
				echo str_replace('<p><sub><i>-- Delivered by <a href="http://feed43.com/">Feed43</a> service</i></sub></p>', "", $text);
				echo '<br /><br /><br />';
				echo '<span style="margin-bottom:15px">';
				echo '	<a href="' . $link . '" target="_BLANK">';
				echo '		Read on elitedangerous.com';
				echo '	</a><img class="ext_icon" src="style/img/external_link.png" style="margin-bottom:3px" alt="ext" />';
				echo '</span>';
				echo '</p>';

				$i++;
			}
		}
		?>
		</div>
	</div>
	<?php
	$html = ob_get_contents();
	// Save to Cache for 30 minutes
	__c("files")->set("galnet", $html, 1800);

	require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
	exit;
}
echo $html;

require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
