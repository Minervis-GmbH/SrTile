<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\SrTile\DICTrait;
use srag\Plugins\SrTile\TileListGUI\TileListContainerGUI\TileListContainerGUI;
use srag\Plugins\SrTile\TileListGUI\TileListDesktopGUI\TileListDesktopGUI;
use srag\Plugins\SrTile\Utils\SrTileTrait;

/**
 * Class ilSrTileUIHookGUI
 *
 * Generated by srag\PluginGenerator v0.9.2
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilSrTileUIHookGUI extends ilUIHookPluginGUI {

	use DICTrait;
	use SrTileTrait;
	const PLUGIN_CLASS_NAME = ilSrTilePlugin::class;
	const PAR_TABS = "tabs";
	const MAIN_TEMPLATE_ID = "tpl.main.html";
	const MAIN_MENU_TEMPLATE_ID = "Services/MainMenu/tpl.main_menu.html";
	const STARTUP_SCREEN_TEMPLATE_ID = "Services/Init/tpl.startup_screen.html";
	const TEMPLATE_ADD = "template_add";
	const TEMPLATE_GET = "template_get";
	const TEMPLATE_SHOW = "template_show";
	const TILE_CONFIG_TAB_LOADER = "tile_config_tab";
	const TILE_CONTAINER_LOADER = "tile_container";
	const TILE_FAVORITES_LOADER = "tile_desktop_loader";
	const TILE_RECOMMEND_MODAL = "tile_recommend_modal";
	const TEMPLATE_ID_CONTAINER_PAGE = "Services/Container/tpl.container_page.html";
	const TEMPLATE_ID_FAVORITES = "Services/PersonalDesktop/tpl.pd_list_block.html";
	const GET = 'template_get';
	const TAB_ID = "tile";
	const ADMIN_FOOTER_TPL_ID = "tpl.adm_content.html";
	/**
	 * @var bool[]
	 */
	protected static $load = [
		self::TILE_CONFIG_TAB_LOADER => false,
		self::TILE_CONTAINER_LOADER => false,
		self::TILE_FAVORITES_LOADER => false,
		self::TILE_RECOMMEND_MODAL => false
	];


	/**
	 * ilSrTileUIHookGUI constructor
	 */
	public function __construct() {

	}


	/**
	 * @param string $a_comp
	 * @param string $a_part
	 * @param array  $a_par
	 *
	 * @return array
	 */
	public function getHTML(/*string*/
		$a_comp, /*string*/
		$a_part, $a_par = []): array {

		$baseClass = strtolower(filter_input(INPUT_GET, 'baseClass'));

		//Repository
		if ($this->loadTileContainerPossible($a_part, $a_par)) {
			self::$load[self::TILE_CONTAINER_LOADER] = true;
			$obj_ref_id = self::tiles()->filterRefId();

			if (self::tiles()->isObject($obj_ref_id)) {

				$this->initJS();

				$tile_list_gui = new TileListContainerGUI($obj_ref_id);

				return [
					"mode" => ilUIHookPluginGUI::PREPEND,
					"html" => self::output()->getHTML($tile_list_gui)
				];
			}
		}

		//Favorites
		if (!self::$load[self::TILE_FAVORITES_LOADER]) {
			if ($baseClass === strtolower(ilPersonalDesktopGUI::class)
				&& $a_par['tpl_id'] === self::TEMPLATE_ID_FAVORITES) {

				self::$load[self::TILE_FAVORITES_LOADER] = true;

				$this->initJS();

				$tile_list_gui = new TileListDesktopGUI(self::dic()->user()->getId());

				return [
					"mode" => ilUIHookPluginGUI::PREPEND,
					"html" => $tile_list_gui->render()
				];
			}
		}

		// Recommend modal
		if (!self::$load[self::TILE_RECOMMEND_MODAL]) {
			if ($a_par['tpl_id'] === self::ADMIN_FOOTER_TPL_ID) {
				self::$load[self::TILE_RECOMMEND_MODAL] = true;

				$recommend_gui = new SrTileRecommendGUI();

				return [ "mode" => ilUIHookPluginGUI::APPEND, "html" => $recommend_gui->getModal() ];
			}
		}

		return parent::getHTML($a_comp, $a_part, $a_par);
	}


	/**
	 * @param string $a_comp
	 * @param string $a_part
	 * @param array  $a_par
	 *
	 * @return array
	 */
	public function modifyGUI(/*string*/
		$a_comp, /*string*/
		$a_part, /*array*/
		$a_par = []): array {
		if (!self::$load[self::TILE_CONFIG_TAB_LOADER]) {

			$obj_ref_id = self::tiles()->filterRefId();

			if ($this->matchObjectBaseClass()
				&& $a_part === self::PAR_TABS
				&& self::tiles()->isObject($obj_ref_id)) {

				self::$load[self::TILE_CONFIG_TAB_LOADER] = true;

				if (!self::access()->hasWriteAccess($obj_ref_id)) {
					return [ "mode" => self::KEEP, "html" => "" ];
				}

				self::dic()->ctrl()->saveParameterByClass(SrTileGUI::class, SrTileGUI::GET_PARAM_OBJ_REF_ID);

				self::dic()->tabs()->addTab(self::TAB_ID, self::plugin()->translate(self::TAB_ID), self::dic()->ctrl()->getLinkTargetByClass([
					ilUIPluginRouterGUI::class,
					SrTileGUI::class
				], SrTileGUI::CMD_EDIT_TILE));

				self::dic()->tabs()->target[count(self::dic()->tabs()->target) - 1]['cmd'] = [];
			}
		}

		return [ "mode" => self::KEEP, "html" => "" ];
	}


	/**
	 *
	 */
	protected function initJS()/*: void*/ {
		self::dic()->mainTemplate()->addJavaScript(self::plugin()->directory() . "/node_modules/@iconfu/svg-inject/dist/svg-inject.min.js");
	}


	/**
	 * @return bool
	 */
	protected function matchObjectBaseClass(): bool {
		$baseClass = strtolower(filter_input(INPUT_GET, 'baseClass'));

		return ($baseClass === strtolower(ilRepositoryGUI::class) || $baseClass === strtolower(ilObjPluginDispatchGUI::class)
			|| $baseClass === strtolower(ilSAHSEditGUI::class)
			|| empty($baseClass));
	}


	/**
	 * @param string $a_part
	 * @param array  $a_par
	 *
	 * @return bool
	 */
	protected function loadTileContainerPossible(string $a_part, array $a_par): bool {
		return (!self::$load[self::TILE_CONTAINER_LOADER]
			&& $this->matchObjectBaseClass()
			&& $a_part === self::GET
			&& ($a_par['tpl_id'] === self::TEMPLATE_ID_CONTAINER_PAGE)
			&& !in_array(self::dic()->ctrl()->getCmd(), [ "editOrder" ])
			&& !in_array(self::dic()->ctrl()->getCallHistory()[0]['cmd'], [ 'editOrder' ])
			&& !$_SESSION["il_cont_admin_panel"]);
	}
}
