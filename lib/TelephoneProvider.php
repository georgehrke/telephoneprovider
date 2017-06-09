<?php
/**
 * @copyright 2017 Georg Ehrke <georg-dev@ehrke.email>
 * @author Georg Ehrke <georg-dev@ehrke.email>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\TelephoneProvider;

use OCP\Contacts\ContactsMenu\IActionFactory;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\Contacts\ContactsMenu\IProvider;
use OCP\IL10N;
use OCP\IURLGenerator;

/**
 * Class TelephoneProvider
 *
 * @package OCA\TelephoneProvider
 */
class TelephoneProvider implements IProvider {

	/** @var IActionFactory */
	private $actionFactory;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IL10N */
	private $l10n;

	/**
	 * @param IActionFactory $actionFactory
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct(IActionFactory $actionFactory, IURLGenerator $urlGenerator, IL10N $l10n) {
		$this->actionFactory = $actionFactory;
		$this->urlGenerator = $urlGenerator;
		$this->l10n = $l10n;
	}

	/**
	 * @param IEntry $entry
	 */
	public function process(IEntry $entry) {
		$telephoneNumbers = $entry->getProperty('TEL');

		if (!$telephoneNumbers) {
			return;
		}

		foreach ($telephoneNumbers as $telephoneNumber) {
			$label = $this->l10n->t('Call %s', [$telephoneNumber]);

			$iconUrl = $this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('telephoneprovider', 'call.svg'));
			$callUrl = 'tel://' . preg_replace('/[^\+0-9]/s', '', $telephoneNumber);

			$action = $this->actionFactory->newLinkAction($iconUrl, $label, $callUrl);
			$entry->addAction($action);
		}
	}
}