<?php

namespace srag\Plugins\SrTile\Rating;

use ActiveRecord;
use arConnector;
use ilSrTilePlugin;
use srag\DIC\SrTile\DICTrait;
use srag\Plugins\SrTile\Utils\SrTileTrait;

/**
 * Class Rating
 *
 * @package srag\Plugins\SrTile\Rating
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Rating extends ActiveRecord {

	use DICTrait;
	use SrTileTrait;
	const TABLE_NAME = "ui_uihk_srtile_ratoing";
	const PLUGIN_CLASS_NAME = ilSrTilePlugin::class;
	/**
	 * @var int
	 *
	 * @con_has_field    true
	 * @con_fieldtype    integer
	 * @con_length       8
	 * @con_is_notnull   true
	 * @con_is_primary   true
	 * @con_sequence     true
	 */
	protected $rating_id;
	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      8
	 * @con_is_notnull  true
	 */
	protected $obj_ref_id;
	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      8
	 * @con_is_notnull  true
	 */
	protected $user_id;


	/**
	 * Rating constructor
	 *
	 * @param int              $primary_key_value
	 * @param arConnector|null $connector
	 */
	public function __construct(/*int*/
		$primary_key_value = 0, arConnector $connector = NULL) {
		parent::__construct($primary_key_value, $connector);
	}


	/**
	 * @return string
	 */
	public function getConnectorContainerName(): string {
		return self::TABLE_NAME;
	}


	/**
	 * @param string $field_name
	 *
	 * @return mixed|null
	 */
	public function sleep(/*string*/
		$field_name) {
		$field_value = $this->{$field_name};

		switch ($field_name) {
			default:
				return NULL;
		}
	}


	/**
	 * @param string $field_name
	 * @param mixed  $field_value
	 *
	 * @return mixed|null
	 */
	public function wakeUp(/*string*/
		$field_name, $field_value) {
		switch ($field_name) {
			case "obj_ref_id":
			case "rating_id":
			case "user_id":
				return intval($field_value);

			default:
				return NULL;
		}
	}


	/**
	 * @return int
	 */
	public function getRatingId(): int {
		return $this->rating_id;
	}


	/**
	 * @param int $rating_id
	 */
	public function setRatingId(int $rating_id)/*: void*/ {
		$this->rating_id = $rating_id;
	}


	/**
	 * @return int
	 */
	public function getObjRefId(): int {
		return $this->obj_ref_id;
	}


	/**
	 * @param int $obj_ref_id
	 */
	public function setObjRefId(int $obj_ref_id)/*: void*/ {
		$this->obj_ref_id = $obj_ref_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id)/*: void*/ {
		$this->user_id = $user_id;
	}
}
