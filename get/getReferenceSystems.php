<?php
/**
 * Ajax backend file to fetch reference systems for trilateration
 *
 * No description
 *
 * @package EDTB\Backend
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

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

$system_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, reference_distances
															FROM user_systems_own
															WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

$system_exists = mysqli_num_rows($system_res);

$ref = array();
if ($system_exists > 0)
{
	$system_arr = mysqli_fetch_assoc($system_res);
	$values = explode("---", $system_arr["reference_distances"]);

	$i = 1;
	foreach ($values as $value)
	{
		$values2 = explode(":::", $value);

		$ref[$i]["name"] = $values2[0];
		$ref[$i]["distance"] = $values2[1];
		$i++;
	}
}
else
{
	$ref[1]["name"] = "";
	$ref[1]["distance"] = "";
	$ref[2]["name"] = "";
	$ref[2]["distance"] = "";
	$ref[3]["name"] = "";
	$ref[3]["distance"] = "";
	$ref[4]["name"] = "";
	$ref[4]["distance"] = "";
}
?>
<script>
	$("a.send").click(function()
	{
		$.get("/action/shipControls.php?send=" + $(this).data("send"));
		$('#ref_' + $(this).data("id") + '_dist').focus();
		//console.log($(this).data("send"));
	});

	$("#clear").click(function()
	{
		 $('#ref_1_dist').val('');
		 $('#ref_2_dist').val('');
		 $('#ref_3_dist').val('');
		 $('#ref_4_dist').val('');
	});
</script>
<form method="post" id="calc_form" action="coorddata.php">
	<div class="input-inner">
		<table>
			<tr>
				<td class="heading" colspan="2">Calculate Coordinates
					<span class="right">
						<a href="javascript:void(0)" onclick="tofront('calculate')" title="Close form">
							<img src="/style/img/close.png" class="icon" alt="X" />
						</a>
					</span>
				</td>
			</tr>
			<tr>
				<td class="light" colspan="2" style="text-align:left;font-size:13px">Use this form to calculate coordinates for systems that have no known coordinates<br />in the <a href="http://edsm.net" target="_BLANK">EDSM</a><img src="/style/img/external_link.png" class="ext_link" alt="ext" style="margin-left:4px" /> database by inserting distances from the system map into this form.<br /><br />
				Clicking the <em>clipboard</em> icon will copy the system name to the client side clipboard.<br /><br />
				Clicking the <em>magic</em> icon will send the system name to the ED client.<br />
				<strong>Note:</strong> have the system map open and the search box targeted before clicking the icon.</td>
			</tr>
			<tr>
				<td class="dark" colspan="2" style="font-size:14px">
					<strong>Target System:</strong> <?php echo $curSys["name"]?><input class="textbox" type="hidden" name="target_system" value="<?php echo $curSys["name"]?>" id="target_system" />
				</td>
			</tr>
			<tr>
				<td class="light" style="text-align:right"><strong><a href="javascript:void(0)" onclick="set_reference_systems(true)" title="Change reference systems">Reference system</a></strong></td>
				<td class="light">
					<strong>Distance (ly)</strong>
					<div class="button" id="clear" style="width:80px;white-space:nowrap;margin-top:3px">Clear All</div>
				</td>
			</tr>
			<?php
			$referencesystems = isset($_GET["standard"]) ? reference_systems(true) : reference_systems();

			$i = 1;
			foreach ($referencesystems as $ref_name => $ref_coordinates)
			{
				$ref_rname = $ref[$i]["name"] != "" ? $ref[$i]["name"] : $ref_name;
				?>
				<tr>
					<td class="dark" style="text-align:right">
						<input class="textbox" type="hidden" id="<?php echo $i?>" name="reference_<?php echo $i?>" value="<?php echo $ref_rname?>" />
						<input class="textbox" type="hidden" name="reference_<?php echo $i?>_coordinates" value="<?php echo $ref_coordinates?>" />
						<span class="left">
							<a class="send" href="javascript:void(0)" title="Send to ED client" data-send="<?php echo $ref_rname?>" data-id="<?php echo $i?>">
								<img class="icon24" src="/style/img/magic.png" alt="Send" />
							</a>
							<a href="javascript:void(0)" title="Copy to clipboard">
								<img class="btn" src="/style/img/clipboard.png" alt="Copy" data-clipboard-text="<?php echo $ref_rname?>" />
						</span>
							</a>
						<strong><?php echo $ref_rname?></strong>
					</td>
					<td class="dark">
						<input class="textbox" type="number" step="any" min="0" id="ref_<?php echo $i?>_dist" name="reference_<?php echo $i?>_distance" value="<?php echo $ref[$i]["distance"]?>" placeholder="1234.56" style="width:100px" autocomplete="off" /><br />
					</td>
				</tr>
				<?php
				$i++;
			}
			?>
			<tr>
				<td class="light" colspan="2">
					<button id="submitc" onclick="update_data('calc_form', '/add/coord.php?do', true);tofront('null', true);return false">Submit Query</button>
				</td>
			</tr>
		</table>
	</div>
</form>
