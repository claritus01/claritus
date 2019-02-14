<?php

namespace backend\modules\user\models;

use Yii;

/**
 * This is the model class for table "{{%user_personal_details}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property integer $gender
 * @property integer $marital_status
 * @property string $address
 * @property integer $country
 * @property integer $state
 * @property integer $city
 * @property integer $is_social
 * @property integer $social_id
 * @property string $image_path
 * @property string $created_on
 * @property integer $created_by
 * @property string $modified_on
 * @property integer $modified_by
 *
 * @property Users $user
 * @property States $state0
 * @property Cities $city0
 * @property Lookups $gender0
 * @property Lookups $maritalStatus
 * @property Lookups $social
 * @property Users $createdBy
 * @property Users $modifiedBy
 * @property Countries $country0
 */
class UserPersonal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_personal_details}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'email', 'created_on', 'modified_on'], 'required'],
            [['user_id', 'gender', 'marital_status', 'country', 'state', 'city', 'is_social', 'social_id', 'created_by', 'modified_by'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            [['first_name', 'last_name'], 'string', 'max' => 30],
            [['email', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 15],
            [['email'], 'unique'],
            [['phone'], 'unique'],
            [['email'], 'email'],
            [['phone'], 'match', 'pattern'=>'/^([0-9  ]+)$/','message' => 'Phone number is invalid.'],
            [['phone'], 'string', 'max' =>15],
            [['phone'], 'string', 'min' =>10],
            [['image_path'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'gender' => 'Gender',
            'marital_status' => 'Marital Status',
            'address' => 'Address',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City',
            'is_social' => 'Is Social',
            'social_id' => 'Social ID',
            'image_path' => 'Image Path',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'modified_on' => 'Modified On',
            'modified_by' => 'Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\common\models\Users::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState0()
    {
        return $this->hasOne(\common\models\States::className(), ['id' => 'state']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity0()
    {
        return $this->hasOne(\common\models\Cities::className(), ['id' => 'city']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGender0()
    {
        return $this->hasOne(\common\models\Lookups::className(), ['id' => 'gender']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaritalStatus()
    {
        return $this->hasOne(\common\models\Lookups::className(), ['id' => 'marital_status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSocial()
    {
        return $this->hasOne(\common\models\Lookups::className(), ['id' => 'social_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(\common\models\Users::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy()
    {
        return $this->hasOne(\common\models\Users::className(), ['id' => 'modified_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry0()
    {
        return $this->hasOne(\common\models\Countries::className(), ['id' => 'country']);
    }
}
