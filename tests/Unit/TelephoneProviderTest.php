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
namespace OCA\TelephoneProvider\Tests;

use OCA\TelephoneProvider\TelephoneProvider;

use OCP\Contacts\ContactsMenu\IAction;
use OCP\Contacts\ContactsMenu\IActionFactory;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\IL10N;
use OCP\IURLGenerator;

use Test\TestCase;

class TelephoneProviderTest extends TestCase {

	private $provider;
	private $action1;
	private $action2;
	private $actionFactory;
	private $urlGenerator;
	private $entry;
	private $l10n;

	public function setUp() {
		parent::setUp();

		$this->action1 = $this->getMockBuilder(IAction::class)
			->disableOriginalConstructor()->getMock();
		$this->action2 = $this->getMockBuilder(IAction::class)
			->disableOriginalConstructor()->getMock();
		$this->actionFactory = $this->getMockBuilder(IActionFactory::class)
			->disableOriginalConstructor()->getMock();
		$this->urlGenerator = $this->getMockBuilder(IURLGenerator::class)
			->disableOriginalConstructor()->getMock();
		$this->l10n = $this->getMockBuilder(IL10N::class)
			->disableOriginalConstructor()->getMock();
		$this->entry = $this->getMockBuilder(IEntry::class)
			->disableOriginalConstructor()->getMock();

		$this->provider = new TelephoneProvider($this->actionFactory,
			$this->urlGenerator, $this->l10n);
	}

	public function testNoPhoneNumber() {
		$this->entry->expects($this->once())
			->method('getProperty')
			->with('TEL')
			->will($this->returnValue(null));
		$this->entry->expects($this->never())
			->method('addAction');

		$this->provider->process($this->entry);
	}

	public function testOnePhoneNumber() {
		$this->entry->expects($this->at(0))
			->method('getProperty')
			->with('TEL')
			->will($this->returnValue(['+49000111222333']));

		$this->l10n->expects($this->at(0))
			->method('t')
			->with('Call %s', ['+49000111222333'])
			->will($this->returnValue('localized_1'));

		$this->urlGenerator->expects($this->at(0))
			->method('imagePath')
			->with('telephoneprovider', 'call.svg')
			->will($this->returnValue('image_url_1'));

		$this->urlGenerator->expects($this->at(1))
			->method('getAbsoluteURL')
			->with('image_url_1')
			->will($this->returnValue('absolute_url_1'));

		$this->actionFactory->expects($this->at(0))
			->method('newLinkAction')
			->with('absolute_url_1', 'localized_1', 'tel:+49000111222333')
			->will($this->returnValue($this->action1));

		$this->entry->expects($this->at(1))
			->method('addAction')
			->with($this->action1);

		$this->provider->process($this->entry);
	}

	public function testTwoPhoneNumbers() {
		$this->entry->expects($this->at(0))
			->method('getProperty')
			->with('TEL')
			->will($this->returnValue([
				'+49000111222333',
				'+49111222333444'
			]));

		$this->l10n->expects($this->at(0))
			->method('t')
			->with('Call %s', ['+49000111222333'])
			->will($this->returnValue('localized_1'));

		$this->l10n->expects($this->at(1))
			->method('t')
			->with('Call %s', ['+49111222333444'])
			->will($this->returnValue('localized_2'));

		$this->urlGenerator->expects($this->at(0))
			->method('imagePath')
			->with('telephoneprovider', 'call.svg')
			->will($this->returnValue('image_url_1'));

		$this->urlGenerator->expects($this->at(1))
			->method('getAbsoluteURL')
			->with('image_url_1')
			->will($this->returnValue('absolute_url_1'));

		$this->urlGenerator->expects($this->at(2))
			->method('imagePath')
			->with('telephoneprovider', 'call.svg')
			->will($this->returnValue('image_url_2'));

		$this->urlGenerator->expects($this->at(3))
			->method('getAbsoluteURL')
			->with('image_url_2')
			->will($this->returnValue('absolute_url_2'));

		$this->actionFactory->expects($this->at(0))
			->method('newLinkAction')
			->with('absolute_url_1', 'localized_1', 'tel:+49000111222333')
			->will($this->returnValue($this->action1));

		$this->actionFactory->expects($this->at(1))
			->method('newLinkAction')
			->with('absolute_url_2', 'localized_2', 'tel:+49111222333444')
			->will($this->returnValue($this->action2));

		$this->entry->expects($this->at(1))
			->method('addAction')
			->with($this->action1);

		$this->entry->expects($this->at(2))
			->method('addAction')
			->with($this->action2);

		$this->provider->process($this->entry);
	}

	/**
	 * @dataProvider dataStripsPhoneNumber
	 */
	public function testStripsPhoneNumber($given, $expected) {
		$this->entry->expects($this->at(0))
			->method('getProperty')
			->with('TEL')
			->will($this->returnValue([$given]));

		$this->l10n->expects($this->at(0))
			->method('t')
			->with('Call %s', [$given])
			->will($this->returnValue('localized_1'));

		$this->urlGenerator->expects($this->at(0))
			->method('imagePath')
			->with('telephoneprovider', 'call.svg')
			->will($this->returnValue('image_url_1'));

		$this->urlGenerator->expects($this->at(1))
			->method('getAbsoluteURL')
			->with('image_url_1')
			->will($this->returnValue('absolute_url_1'));

		$this->actionFactory->expects($this->at(0))
			->method('newLinkAction')
			->with('absolute_url_1', 'localized_1', $expected)
			->will($this->returnValue($this->action1));

		$this->entry->expects($this->at(1))
			->method('addAction')
			->with($this->action1);

		$this->provider->process($this->entry);
	}

	public function dataStripsPhoneNumber() {
		return [
			['+49 000 111 222 333', 'tel:+49000111222333'],
			['+49 000 (111) 222 333', 'tel:+49000111222333'],
			['000 111 222 333', 'tel:000111222333'],
		];
	}
}
