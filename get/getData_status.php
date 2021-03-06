<?php
/**
 * Ajax backend file to fetch profile data
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

/** @var new is the system new */
if (isset($_GET["newsys"]))
{
	$new = $_GET["newsys"] == "true" ? "true" : "false";
}
else
{
	$new = "false";
}
/** @var override override the default minimum time between refreshes */
$override = $_GET["override"] == "true" ? "true" : "false";
/** @var force_update */
$force_update = $_GET["force_update"] == "true" ? "true" : "false";

/** @require api update */
require_once($_SERVER["DOCUMENT_ROOT"] . "/action/updateAPIdata.php");
/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

/**
 * show user status
 */

$data['cmdr_status'] = "false";
$data['cmdr_balance_status'] = "false";
if (isset($api["commander"]) && $settings["show_cmdr_status"] == "true")
{
	if ($api["commander"] != "no_data")
	{
		$cmdr_credits = number_format($api["commander"]["credits"]);

		/**
		 * get icons for cmdr ranks
		 */

		$cmdr_rank_combat = $api["commander"]["rank"]["combat"];
		$cmdr_rank_combat_icon = '<a href="#" title="Combat rank: ' . get_rank("combat", $cmdr_rank_combat, false) . '"><img src="' . get_rank("combat", $cmdr_rank_combat+1) . '" alt="combat" class="status_img" style="margin-right:6px" /></a>';

		$cmdr_rank_trade = $api["commander"]["rank"]["trade"];
		$cmdr_rank_trade_icon = '<a href="#" title="Trade rank: ' . get_rank("trade", $cmdr_rank_trade, false) . '"><img src="' . get_rank("trade", $cmdr_rank_trade+1) . '" alt="trade" class="status_img" style="margin-right:6px" /></a>';

		$cmdr_rank_explore = $api["commander"]["rank"]["explore"];
		$cmdr_rank_explore_icon = '<a href="#" title="Explorer rank: ' . get_rank("explore", $cmdr_rank_explore, false) . '"><img src="' . get_rank("explore", $cmdr_rank_explore+1) . '" alt="explorer" class="status_img" /></a>';

		$cmdr_rank_cqc = "";
		$cmdr_rank_cqc_icon = "";

		if ($settings["show_cqc_rank"] == "true")
		{
			$cmdr_rank_cqc = $api["commander"]["rank"]["cqc"];
			$cmdr_rank_cqc_icon = '<a href="#" title="CQC rank: ' . get_rank("cqc", $cmdr_rank_cqc, false) . '"><img src="' . get_rank("cqc", $cmdr_rank_cqc+1) . '" class="status_img" alt="cqc" style="margin-right:6px" /></a>';
		}

		/**
		 * additional info
		 */

		$cmdr_rank_fed = $api["commander"]["rank"]["federation"];
		$fed_rank = get_rank("federation", $cmdr_rank_fed, false);

		$cmdr_rank_empire = $api["commander"]["rank"]["empire"];
		$empire_rank = get_rank("empire", $cmdr_rank_empire, false);

		$additional = '<div id="cmdr_status_mi" style="display:none"><strong>Federation rank:</strong> ' . $fed_rank . '<br /><strong>Empire rank:</strong> ' . $empire_rank . '</div>';

		$data['cmdr_status'] = $cmdr_rank_combat_icon . $cmdr_rank_trade_icon . $cmdr_rank_explore_icon . $cmdr_rank_cqc_icon . $additional;

		$data['cmdr_balance_status'] = '<img src="/style/img/rare.png" style="margin-right:3px;margin-bottom:2px;height:14px;width:14px" alt="Cr" />' . $cmdr_credits . ' CR';
	}
	else
	{
		$data['cmdr_status'] = "";
		$data['cmdr_balance_status'] = "";
	}
}

/**
 * show ship status
 */

$data['ship_status'] = "false";
if (isset($api["ship"]) && $settings["show_ship_status"] == "true")
{
	if ($api["ship"] == "no_data")
	{
		$data['ship_status'] = '<a href="/admin/api_login.php">No data, reconnect API</a>';
	}
	else
	{
		/**
		 * basic ship info
		 */

		$ship_name = $api["ship"]["name"];
		$ship_health = number_format($api["ship"]["health"]["hull"]/10000, 1);

		$ship_fuel = number_format($api["ship"]["fuel"]["main"]["level"]/$api["ship"]["fuel"]["main"]["capacity"]*100, 1);
		$ship_cargo_cap = $api["ship"]["cargo"]["capacity"];
		$ship_cargo_used = $api["ship"]["cargo"]["qty"];

		/**
		 * additional ship info
		 */

		$ship_value = number_format($api["ship"]["value"]["total"]);
		$ship_hull_value = number_format($api["ship"]["value"]["hull"]);
		$ship_modules_value = number_format($api["ship"]["value"]["modules"]);

		if (isset($api["stored_ships"]))
		{
			$stored_ships = "<br /><br /><strong>Stored ships</strong><br />";
			foreach ($api["stored_ships"] as $shipId => $stored_ship)
			{
				if ($shipId != $api["commander"]["currentShipId"])
				{
					$ship_name = ship_name($stored_ship["name"]);
					$docked_at_station = $stored_ship["station"]["name"];
					$docked_at_system = $stored_ship["starsystem"]["name"];

					$distance = get_distance($docked_at_system);

					$stored_ships .= $ship_name . ' (' . $distance . ')<br />' . $docked_at_station . ' at <a href="/System.php?system_name=' . urlencode($docked_at_system ) . '">' . $docked_at_system . '</a><br /><br />';
				}
			}
		}

		$additional = '<div id="ship_status_mi" style="display:none"><strong>Ship value:</strong> ' . $ship_value .' CR<br />Hull: ' . $ship_hull_value . ' CR<br />Modules: ' . $ship_modules_value . ' CR' . $stored_ships . '</div>';

		$data['ship_status'] = '<img src="/style/img/ship.png" class="icon" alt="Ship hull" />' . $ship_health . ' %<img src="/style/img/fuel.png"  class="icon24" style="margin-left:6px;margin-bottom:4px" alt="Ship fuel" />' . $ship_fuel . ' %<img src="/style/img/cargo.png" class="icon24" style="margin-left:6px" alt="Ship cargo" />' . $ship_cargo_used . '/' . $ship_cargo_cap . $additional;
	}
}

/**
 * write to cache if changed
 */

$cmdr_ranks_file = $_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_ranks_status.html";
$data['cmdr_ranks_update'] = "false";
$cmdr_rank_cache = file_get_contents($cmdr_ranks_file);

$cmdr_balance_file = $_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_balance_status.html";
$data['cmdr_balance_update'] = "false";
$cmdr_balance_cache = file_get_contents($cmdr_balance_file);

$ship_status_file = $_SERVER["DOCUMENT_ROOT"] . "/cache/ship_status.html";
$data['ship_status_update'] = "false";
$ship_status_cache = file_get_contents($ship_status_file);

if ($force_update == "true")
{
	$data['cmdr_ranks_update'] = "true";
	$data['cmdr_balance_update'] = "true";
	$data['ship_status_update'] = "true";
}
else
{
	if ($cmdr_rank_cache != $data["cmdr_status"])
	{
		if (!file_put_contents($cmdr_ranks_file, $data["cmdr_status"]))
		{
			$error = error_get_last();
			write_log("Error: " . $error["message"], __FILE__, __LINE__);
		}
		$data['cmdr_ranks_update'] = "true";
	}

	if ($cmdr_balance_cache != $data["cmdr_balance_status"])
	{
		if (!file_put_contents($cmdr_balance_file, $data["cmdr_balance_status"]))
		{
			$error = error_get_last();
			write_log("Error: " . $error["message"], __FILE__, __LINE__);
		}
		$data['cmdr_balance_update'] = "true";
	}

	if ($ship_status_cache != $data["ship_status"])
	{
		if (!file_put_contents($ship_status_file, $data["ship_status"]))
		{
			$error = error_get_last();
			write_log("Error: " . $error["message"], __FILE__, __LINE__);
		}
		$data['ship_status_update'] = "true";
	}
}
echo json_encode($data);
