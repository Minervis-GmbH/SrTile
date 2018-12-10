<?php

namespace srag\Plugins\SrTile\TileListGUI\TileListContainerGUI;

use srag\Plugins\SrTile\TileGUI\TileContainerGUI\TileContainerGUI;
use srag\Plugins\SrTile\TileList\TileListContainer\TileListContainer;
use srag\Plugins\SrTile\TileListGUI\TileListGUIAbstract;

/**
 * Class TileListContainerGUI
 *
 * @package srag\Plugins\SrTile\TileListGUI\TileListContainerGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  studer + raimann ag - Martin Studer <ms@studer-raimann.ch>
 *
 */
class TileListContainerGUI extends TileListGUIAbstract {

	/**
	 * TileListContainerGUI constructor
	 *
	 * @param int $container_obj_ref_id
	 */
	public function __construct(int $container_obj_ref_id) {
		$this->tile_list = TileListContainer::getInstance($container_obj_ref_id);
	}


	/**
	 * @inheritdoc
	 */
	public function getHtml(): string {
		$tile_html = '';
		foreach ($this->tile_list->getTiles() as $tile) {
			$tile_gui = new TileContainerGUI($tile);
			$tile_html .= $tile_gui->render();
		}

		return $tile_html;
	}


	/**
	 * @inheritdoc
	 */
	public function hideOriginalRowsOfTiles() /*:void*/ {

		$css = '';
		$is_parent_css_rendered = false;
		foreach ($this->tile_list->getTiles() as $tile) {
			$css .= ' #lg_div_';
			$css .= $tile->getObjRefId();
			$css .= '_pref_';
			$css .= $this->tile_list->getContainerObjRefId();
			$css .= '{ display: none !important;} ';

			$css .= '#sr-tile-' . $tile->getTileId();
			$css .= '{ background-color: #' . $tile->getLevelColor() . '!important;}';

			if ($is_parent_css_rendered == false) {

				$parent_tile = Tile::getInstanceForObjRefId(self::dic()->tree()->getParentId($tile->getObjRefId()));
				if (is_object($parent_tile)) {
					$css .= 'a#il_mhead_t_focus';
					$css .= '{ color: #' . $parent_tile->getLevelColor() . '!important;}';

					$css .= '.card';
					$css .= '{ border: 4px solid #' . $parent_tile->getLevelColor() . '!important;}';

					$css .= '.btn-default';
					$css .= '{ background-color: #' . $parent_tile->getLevelColor() . '!important; ';
					$css .= 'border-color:' . $parent_tile->getLevelColor() . '!important; }';
				}
			}
			$is_parent_css_rendered = true;
		}

		self::dic()->mainTemplate()->addInlineCss($css);
	}
}
