<?php

namespace srag\Plugins\SrTile\Certificate;

use ilCertificate;
use ilCourseParticipants;
use ilLPStatus;
use ilObjCourseGUI;
use ilObjUser;
use ilRepositoryGUI;
use ilSAHSPresentationGUI;
use ilSrTilePlugin;
use ilUIPluginRouterGUI;
use srag\DIC\SrTile\DICTrait;
use srag\Plugins\SrTile\Tile\Tiles;
use srag\Plugins\SrTile\Utils\SrTileTrait;
use srCertificate;
use srCertificateDefinition;
use srCertificateUserGUI;

/**
 * Class Certificates
 *
 * @package srag\Plugins\SrTile\Certificate
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Certificates {

	use DICTrait;
	use SrTileTrait;
	const PLUGIN_CLASS_NAME = ilSrTilePlugin::class;
	/**
	 * @var self[]
	 */
	protected static $instances = [];


	/**
	 * @param ilObjUser $user
	 * @param int       $obj_ref_id
	 *
	 * @return self
	 */
	public static function getInstance(ilObjUser $user, int $obj_ref_id): self {
		if (!isset(self::$instances[$user->getId() . "_" . $obj_ref_id])) {
			self::$instances[$user->getId() . "_" . $obj_ref_id] = new self($user, $obj_ref_id);
		}

		return self::$instances[$user->getId() . "_" . $obj_ref_id];
	}


	/**
	 * @var ilObjUser
	 */
	protected $user;
	/**
	 * @var int
	 */
	protected $obj_ref_id;
	/**
	 * @var int
	 */
	protected $obj_id;


	/**
	 * Certificates constructor
	 *
	 * @param ilObjUser $user
	 * @param int       $obj_ref_id
	 */
	private function __construct(ilObjUser $user, int $obj_ref_id) {
		$this->user = $user;
		$this->obj_ref_id = $obj_ref_id;
		$this->obj_id = self::dic()->objDataCache()->lookupObjId($obj_ref_id);
	}


	/**
	 * @return bool
	 */
	public function enabled(): bool {
		if (ilCertificate::isActive() && ilCertificate::isObjectActive($this->obj_id)) {
			return true;
		}

		if (file_exists(__DIR__ . "/../../../Certificate/vendor/autoload.php")) {
			// Certificate is enabled because Certificate plugin is available
			return true;
		}

		return false;
	}


	/**
	 * @return string|null
	 */
	public function getCertificateDownloadLink()/*: ?string*/ {
		switch (self::dic()->objDataCache()->lookupType($this->obj_id)) {
			case "crs":
				// First check Certificate plugin
				if (file_exists(__DIR__ . "/../../../Certificate/vendor/autoload.php")) {
					/**
					 * @var srCertificateDefinition|null $cert_def
					 * @var srCertificate|null           $cert
					 */

					// An object links to a certificate definition
					$cert_def = srCertificateDefinition::where([ "ref_id" => $this->obj_ref_id ])->first();

					if ($cert_def !== NULL) {

						// Check allow to download certificate
						if (boolval($cert_def->getDownloadable())) {

							// A certificate definition links to the certificate of the user
							$cert = srCertificate::where([
								"user_id" => $this->user->getId(),
								"definition_id" => $cert_def->getId(),
								"active" => 1
							])->first();

							if ($cert !== NULL) {

								// The certificate must be active and be generated
								if ($cert->getActive() && intval($cert->getStatus()) === srCertificate::STATUS_PROCESSED) {

									self::dic()->ctrl()->setParameterByClass(srCertificateUserGUI::class, "cert_id", $cert->getId());

									return self::dic()->ctrl()->getLinkTargetByClass([
										ilUIPluginRouterGUI::class,
										srCertificateUserGUI::class
									], srCertificateUserGUI::CMD_DOWNLOAD_CERTIFICATE);
								} else {
									// The current user has no activated and generated certificate
									return "";
								}
							} else {
								// The current user has no certificate
								return "";
							}
						} else {
							// Download of certificate disabled for this object
							return "";
						}
					} else {
						// The Certificate Plugin is not enabled for this object - Use ILIAS core certificate as possible fallback
						//return "";
					}
				}

				//@see Modules/Course/classes/class.ilObjCourseGUI.php:3214
				if (self::ilias()->certificates($this->user, $this->obj_ref_id)->enabled()
					&& ilCourseParticipants::getDateTimeOfPassed($this->obj_id, $this->user->getId())) {
					self::dic()->ctrl()->setParameterByClass(ilObjCourseGUI::class, Tiles::GET_PARAM_REF_ID, $this->obj_ref_id);

					return self::dic()->ctrl()->getLinkTargetByClass([ ilRepositoryGUI::class, ilObjCourseGUI::class ], 'deliverCertificate');
				}
				break;

			case "sahs":
				if (self::ilias()->learningProgress($this->user)->getStatus($this->obj_ref_id) === ilLPStatus::LP_STATUS_COMPLETED_NUM) {
					//the following way of link generation does not work! the above way is the standard(!:-( ILIAS way of link generation for certificate
					//$this->ctrl->setParameterByClass(ilSAHSPresentationGUI::class, Tiles::GET_PARAM_REF_ID, $obj_ref_id);
					//return $this->ctrl->getLinkTargetByClass(ilSAHSPresentationGUI::class,'downloadCertificate');
					return 'ilias.php?baseClass=' . ilSAHSPresentationGUI::class . '&ref_id=' . $this->obj_ref_id . '&cmd=downloadCertificate';
				}
				break;

			case "tst":
				// TODO Certificates for ILIAS Test
				break;

			default:
				break;
		}

		return NULL;
	}
}
