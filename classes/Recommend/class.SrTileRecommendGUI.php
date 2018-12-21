<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\SrTile\DICTrait;
use srag\Plugins\SrTile\Recommend\RecommendFormGUI;
use srag\Plugins\SrTile\Recommend\SuccessFormGUI;
use srag\Plugins\SrTile\Tile\Tile;
use srag\Plugins\SrTile\Utils\SrTileTrait;

/**
 * Class SrTileRecommendGUI
 *
 * @ilCtrl_isCalledBy SrTileRecommendGUI: ilUIPluginRouterGUI
 */
class SrTileRecommendGUI {

	use DICTrait;
	use SrTileTrait;
	const PLUGIN_CLASS_NAME = ilSrTilePlugin::class;
	const CMD_ADD_RECOMMEND = "addRecommend";
	const CMD_NEW_RECOMMEND = "newRecommend";
	const LANG_MODULE_RECOMMENDATION = "recommendation";
	/**
	 * @var Tile
	 */
	protected $tile;


	/**
	 * SrTileRecommendGUI constructor
	 */
	public function __construct() {
		$this->tile = self::tiles()->getInstanceForObjRefId(self::tiles()->filterRefId());
	}


	/**
	 *
	 */
	public function executeCommand()/*: void*/ {
		if (!($this->tile->getProperties()->getShowRecommendIcon() === Tile::SHOW_TRUE
			&& !empty($this->tile->getProperties()->getRecommendMailTemplate())
			&& self::access()->hasReadAccess($this->tile->getObjRefId()))) {
			return;
		}

		$next_class = self::dic()->ctrl()->getNextClass($this);

		switch ($next_class) {
			default:
				$cmd = self::dic()->ctrl()->getCmd();

				switch ($cmd) {
					case self::CMD_ADD_RECOMMEND:
					case self::CMD_NEW_RECOMMEND:
						$this->{$cmd}();
						break;

					default:
						break;
				}
				break;
		}
	}


	/**
	 * @return string
	 */
	public function getModal(): string {
		self::dic()->mainTemplate()->addJavaScript(self::plugin()->directory() . "/js/recommend.min.js");

		ilModalGUI::initJS();

		$modal = ilModalGUI::getInstance();
		$modal->setType(ilModalGUI::TYPE_LARGE);

		$modal->setId("tile_recommend_modal");

		return self::output()->getHTML($modal);
	}


	/**
	 * @return RecommendFormGUI
	 */
	protected function getRecommendForm() {
		$tile = self::tiles()->getInstanceForObjRefId(self::tiles()->filterRefId());

		$form = new RecommendFormGUI($this, $tile);

		return $form;
	}


	/**
	 * @return SuccessFormGUI
	 */
	protected function getSuccessForm() {
		$tile = self::tiles()->getInstanceForObjRefId(self::tiles()->filterRefId());

		$form = new SuccessFormGUI($this, $tile);

		return $form;
	}


	/**
	 * @param string|null       $message
	 * @param ilPropertyFormGUI $form
	 */
	protected function show(/*?string*/
		$message, ilPropertyFormGUI $form)/*: void*/ {
		$tpl = self::plugin()->template("Recommend/tpl.recommend_modal.html");

		if ($message !== NULL) {
			$tpl->setCurrentBlock("recommend_message");
			$tpl->setVariable("MESSAGE", $message);
		}

		$tpl->setCurrentBlock("recommend_form");
		$tpl->setVariable("FORM", self::output()->getHTML($form));

		self::output()->output($tpl, true);
	}


	/**
	 *
	 */
	protected function addRecommend()/*: void*/ {
		$message = NULL;

		$form = $this->getRecommendForm();

		$this->show($message, $form);
	}


	/**
	 *
	 */
	protected function newRecommend()/*: void*/ {
		$message = NULL;

		$form = $this->getRecommendForm();

		if (!$form->storeForm()) {
			$this->show($message, $form);

			return;
		}

		$recommend = $form->getRecommend();

		if ($recommend->send()) {
			$message = self::dic()->mainTemplate()->getMessageHTML(self::plugin()
				->translate("sent_success", self::LANG_MODULE_RECOMMENDATION), "success");
		} else {
			$message = self::dic()->mainTemplate()->getMessageHTML(self::plugin()
				->translate("sent_failure", self::LANG_MODULE_RECOMMENDATION), "failure");
		}

		$this->show($message, $form);
	}
}
