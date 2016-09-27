<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

class MobileViewEventAction extends MobileAction {

    public $pageDepth = 1;

    public function run ($id) {
        $formModel = new EventCommentPublisherFormModel;
        $profile = Yii::app()->params->profile;
        $model = $this->controller->lookUpModel ($id, 'Events');
        $this->controller->dataUrl = Yii::app()->request->url;
        $settings = Yii::app()->settings;
        if (isset ($_POST['geoCoords']) && isset ($_POST['geoLocationCoords'])) {
            $creds = Credentials::model()->findByPk($settings->googleCredentialsId);
            if ($creds && $creds->auth && $creds->auth->apiKey){
                $key = $creds->auth->apiKey; 
                $result = "";
                $decodedResponse = json_decode($_POST['geoCoords'],true);
                //https://davidwalsh.name/curl-post
                //extract data from the post
                //set POST variables
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' .
                    $decodedResponse['lat'] . ',' .$decodedResponse['lon'] . 
                    '&key=' . $key;
                //open connection
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_URL, $url);

                //execute post
                $result = curl_exec($ch);
                //close connection
                echo $result;
                curl_close($ch);
                Yii::app()->end ();
            }        
        }
        
        if ($model->checkPermissions ('view')) {
            if (isset ($_POST['EventCommentPublisherFormModel'])) {
                $formModel->setAttributes ($_POST['EventCommentPublisherFormModel']);
                if (isset ($_FILES['EventCommentPublisherFormModel'])) {
                    $model->photo = CUploadedFile::getInstance ($model, 'photo');
                }
                if (isset($_POST['geoCoords']) && Yii::app()->settings->locationTrackingSwitch){
                    $location = Yii::app()->params->profile->user->logLocation('mobileActivityPost', 'POST');
                }
                if ($formModel->validate ()) {
                    $event = new Events;
                    $event->setAttributes (array (
                        'visibility' => X2PermissionsBehavior::VISIBILITY_PUBLIC,
                        'user' => $profile->username,
                        'type' => 'structured-feed',
                        'associationType' => 'Events',
                        'associationId' => $id,
                        'text' => $formModel->text,
                        'photo' => $formModel->photo
                    ), false);
                    if ($event->save ()) {
                        $formModel->text = '';
                        if (!isset ($_FILES['EventCommentPublisherFormModel'])) {
                        } else {
                            Yii::app()->end (); 
                        }
                    } else {
                        throw new CHttpException (
                            500, implode (';', $event->getAllErrorMessages ()));
                    }
                }
            }

            $dataProvider = new CActiveDataProvider ('Events', array(
                'criteria' => array(
                    'order' => 'timestamp ASC',
                    'condition' => 
                        "type in ('comment', 'structured-feed') AND 
                         associationType='Events' AND associationId=$id",
                ),
                'pagination' => array (
                    'pageSize' => 30,
                )));
            $this->controller->render (
                $this->pathAliasBase.'views.mobile.viewEvent', array (
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                    'formModel' => $formModel,
                )
            );
        } else {
            $this->controller->denied ();
        }
    }

}

?>
