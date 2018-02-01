<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2018 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/

/**
 * Class intended for auto-import of {@link CActiveMock}
 * @package application.tests
 */
abstract class CActiveRecordBehaviorTestCase extends X2TestCase {

	public static function setUpBeforeClass() {
		Yii::app()->db->createCommand('DROP TABLE IF EXISTS`'.CActiveMock::MOCK_TABLE)->execute();
		Yii::app()->db->createCommand('CREATE TABLE `'.CActiveMock::MOCK_TABLE.'` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, foo BLOB,bar TEXT, flag TINYINT NOT NULL DEFAULT 0)')->execute();
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass(){
		Yii::app()->db->createCommand('DROP TABLE IF EXISTS`'.CActiveMock::MOCK_TABLE)->execute();
		parent::tearDownAfterClass();
	}
}

/**
 * Child class of CActiveRecord for doing mocks in tests of CActiveRecordBehavior
 * @package application.tests
 */
class CActiveMock extends CActiveRecord {

	const MOCK_TABLE = 'x2_mock_model_table';

	public function rules() {
		return array(
			array('foo,bar,flag','safe')
		);
	}

	public function tableName() {
		return self::MOCK_TABLE;
	}
}

?>
