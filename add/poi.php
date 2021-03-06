<?php
/**
 * Add or edit points of interest
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

if (isset($_GET["do"]))
{
	/** @require config */
	require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
	/** @require functions */
	require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
	/** @require MySQL */
	require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

	$data = json_decode($_REQUEST["input"], true);

	$p_system = $data["poi_system_name"];
	$p_name = $data["poi_name"];
	$p_x = $data["poi_coordx"];
	$p_y = $data["poi_coordy"];
	$p_z = $data["poi_coordz"];

	if (valid_coordinates($p_x, $p_y, $p_z))
	{
		$addc = ", x = '" . $p_x . "', y = '" . $p_y . "', z = '" . $p_z . "'";
		$addb = ", '" . $p_x . "', '" . $p_y . "', '" . $p_z . "'";
	}
	else
	{
		$addc = ", x = null, y = null, z = null";
		$addb = ", null, null, null";
	}

	$p_entry = $data["poi_text"];
	$p_id = $data["poi_edit_id"];
	$category_id = $data["category_id"];

	if ($p_id != "")
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_poi SET
													poi_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_name) . "',
													system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_system) . "',
													text = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_entry) . "',
													category_id = '" . $category_id . "'" . $addc . "
													WHERE id = '" . $p_id . "'") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	elseif (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_poi
													WHERE id = '" . $_GET["deleteid"] . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_poi (poi_name, system_name, text, category_id, x, y, z)
													VALUES
														('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_name) . "',
														'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_system) . "',
														'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_entry) . "',
														'" . $category_id . "'" . $addb . ")") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}

	exit;
}

if ($_SERVER['PHP_SELF'] == "/Poi.php")
{
?>
    <div class="input" id="addPoi" style="text-align:center">
		<form method="post" id="poi_form" action="Poi.php">
			<div class="input-inner">
				<div class="suggestions" id="suggestions_33" style="margin-top:79px;margin-left:12px"></div>
				<table>
					<tr>
						<td class="heading" colspan="2">Add/edit Point of Interest
							<span class="right">
								<a href="javascript:void(0)" onclick="tofront('addPoi')" title="Close form">
									<img class="icon" src="/style/img/close.png" alt="X" />
								</a>
							</span>
						</td>
					</tr>
					<tr>
						<td class="dark" style="width:50%">
							<input type="hidden" name="poi_edit_id" id="poi_edit_id">
							<input class="textbox" type="text" name="poi_system_name" placeholder="System name" id="system_33" style="width:95%" oninput="showResult(this.value, '33')" />
						</td>
						<td class="dark" style="white-space:nowrap;width:30%">
							<input class="textbox" type="text" name="poi_coordx" placeholder="x.x" id="coordsx_33" style="width:40px" />
							<input class="textbox" type="text" name="poi_coordy" placeholder="y.y" id="coordsy_33" style="width:40px" />
							<input class="textbox" type="text" name="poi_coordz" placeholder="z.z" id="coordsz_33" style="width:40px" />
						</td>
					</tr>
					<tr>
						<td class="dark" style="width:70%">
							<input class="textbox" type="text" name="poi_name" id="poi_name" placeholder="POI name (optional)" style="width:95%" />
						</td>
						<td class="dark" style="white-space:nowrap;width:auto">
							<select class="selectbox" name="category_id" id="category_id" style="width:auto">
								<option value="0">Category (optional)</option>
								<?php
								$pcat_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM user_poi_categories");

								while ($pcat_arr = mysqli_fetch_assoc($pcat_res))
								{
									echo '<option value="' . $pcat_arr["id"] . '">' . $pcat_arr["name"] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="dark" colspan="2">
							<textarea id="poi_text" name="poi_text" placeholder="Text (optional)" rows="10" cols="40"></textarea>
						</td>
					</tr>
					<tr>
						<td class="dark" colspan="2">
							<a href="javascript:void(0)" data-replace="true" data-target=".entries">
								<div class="button" onclick="update_data('poi_form', '/add/Poi.php?do', true);tofront('null', true)">
									Submit Point of Interest
								</div>
							</a>
							<span id="delete_poi"></span>
						</td>
					</tr>
				</table>
			</div>
		</form>
    </div>
<?php
}
